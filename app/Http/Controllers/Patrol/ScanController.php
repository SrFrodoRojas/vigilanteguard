<?php
// app/Http/Controllers/Patrol/ScanController.php

namespace App\Http\Controllers\Patrol;

use App\Http\Controllers\Controller;
use App\Models\Checkpoint;
use App\Models\CheckpointScan;
use App\Models\PatrolAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    /**
     * (Opcional) UI alternativa. Tu ruta usa showScanner().
     */
    public function index(Request $request)
    {
        $checkpoint = null;
        if ($request->filled('c')) {
            $checkpoint = Checkpoint::with(['route.branch'])
                ->where('qr_token', $request->input('c'))
                ->first();
        }

        $myAssignments = PatrolAssignment::with(['route.branch'])
            ->where('guard_id', Auth::id())
            ->orderByDesc('scheduled_start')
            ->limit(20)
            ->get();

        $assignment = null;
        if ($checkpoint) {
            $assignment = $myAssignments->firstWhere('patrol_route_id', $checkpoint->patrol_route_id);
        }

        return view('patrol.scan', compact('checkpoint', 'assignment', 'myAssignments'));
    }

    /**
     * GET /patrol/scan (ruta: patrol.scan)
     */
    public function showScanner(Request $request)
    {
        $checkpoint = null;

        if ($request->filled('c')) {
            $checkpoint = Checkpoint::with(['route.branch'])
                ->where('qr_token', $request->input('c'))
                ->first();
        }

        $myAssignments = PatrolAssignment::with(['route.branch'])
            ->where('guard_id', Auth::id())
            ->orderByDesc('scheduled_start')
            ->limit(20)
            ->get();

        $assignment = null;
        if ($request->filled('a')) {
            $assignment = $myAssignments->firstWhere('id', (int) $request->input('a'));
        }
        if (! $assignment && $checkpoint) {
            $assignment = $myAssignments->firstWhere('patrol_route_id', $checkpoint->patrol_route_id);
        }

        $alreadyScanned = false;
        if ($checkpoint && $assignment) {
            $alreadyScanned = CheckpointScan::where('patrol_assignment_id', $assignment->id)
                ->where('checkpoint_id', $checkpoint->id)
                ->exists();
        }

        return view('patrol.scan', compact('checkpoint', 'assignment', 'myAssignments', 'alreadyScanned'));
    }

    /**
     * POST /patrol/scan
     */
    public function store(Request $request)
    {
        // Normalizar accuracy si viene como accuracy_m
        if ($request->has('accuracy_m') && ! $request->has('accuracy')) {
            $request->merge(['accuracy' => $request->input('accuracy_m')]);
        }

        $data = $request->validate([
            'assignment_id' => 'required|exists:patrol_assignments,id',
            'qr_token'      => 'required|string',
            'lat'           => 'required|numeric|between:-90,90',
            'lng'           => 'required|numeric|between:-180,180',
            'accuracy'      => 'nullable|numeric|min:0',
        ]);

        $assignment = PatrolAssignment::with('route')->findOrFail($data['assignment_id']);
        $checkpoint = Checkpoint::with('route')->where('qr_token', $data['qr_token'])->first();

        if (! $checkpoint) {
            return redirect()
                ->route('patrol.scan', ['a' => $assignment->id])
                ->with('warning', 'QR inválido o no corresponde a un checkpoint activo.');
        }
        if ((int) $checkpoint->patrol_route_id !== (int) $assignment->patrol_route_id) {
            return redirect()
                ->route('patrol.scan', ['c' => $checkpoint->qr_token, 'a' => $assignment->id])
                ->with('warning', 'El checkpoint no pertenece a la ruta de tu asignación.');
        }

        // ⚠️ Bloquear escaneo por estado no permitido
        if (! in_array($assignment->status, ['scheduled', 'in_progress'])) {
            return redirect()
                ->route('patrol.scan', ['c' => $checkpoint->qr_token, 'a' => $assignment->id])
                ->with('warning', 'Esta asignación no admite escaneo en estado: ' . $assignment->status . '.');
        }

        // Política
        [
            'accuracyMax'          => $accuracyMax,
            'modoEstrictoAccuracy' => $modoEstrictoAccuracy,
            'modoEstrictoradio'    => $modoEstrictoradio,
            'speedMaxMps'          => $speedMaxMps,
            'jumpMaxM'             => $jumpMaxM,
            'jumpWindowS'          => $jumpWindowS,
        ] = $this->patrolPolicy();

        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];
        $acc = is_null($data['accuracy']) ? null : (float) $data['accuracy'];

        $distanceM = $this->haversineMeters(
            (float) $checkpoint->latitude,
            (float) $checkpoint->longitude,
            $lat,
            $lng
        );

        $effectiveRadiusM = (float) max(
            (float) $checkpoint->radius_m,
            (float) ($checkpoint->route->min_radius_m ?? 0)
        );

        $verified   = 1;
        $accToCheck = is_null($acc) ? 9999 : $acc;

        if ($accToCheck > $accuracyMax) {
            if ($modoEstrictoAccuracy) {
                return redirect()
                    ->route('patrol.scan', ['c' => $checkpoint->qr_token, 'a' => $assignment->id])
                    ->with('warning', "Precisión insuficiente (±" . round($accToCheck) . " m > {$accuracyMax} m). Mejorá la señal y reintenta.");
            }
            $verified = 0;
        }

        if ($distanceM > $effectiveRadiusM) {
            if ($modoEstrictoradio) {
                return redirect()
                    ->route('patrol.scan', ['c' => $checkpoint->qr_token, 'a' => $assignment->id])
                    ->with('warning', "Fuera de radio (dist: " . round($distanceM) . " m, permitido: {$effectiveRadiusM} m). Acercate al punto.");
            }
            $verified = 0;
        }

        // Anti-fraude
        $suspect          = 0;
        $suspectReason    = null;
        $speedMpsForStore = null;
        $jumpMForStore    = null;

        $lastScan = CheckpointScan::where('patrol_assignment_id', $assignment->id)
            ->orderByDesc('scanned_at')
            ->first();

        if ($lastScan) {
            $dt = max(1, now()->diffInSeconds($lastScan->scanned_at));
            $dd = $this->haversineMeters(
                (float) $lastScan->lat,
                (float) $lastScan->lng,
                $lat,
                $lng
            );
            $speed            = $dd / $dt;
            $speedMpsForStore = (int) round($speed);
            $jumpMForStore    = (int) round($dd);

            $reasons = [];
            if ($speed > $speedMaxMps) {
                $reasons[] = "speed>{$speedMaxMps}m/s";
            }

            if ($dt < $jumpWindowS && $dd > $jumpMaxM) {
                $reasons[] = "jump>{$jumpMaxM}m<{$jumpWindowS}s";
            }

            if ($reasons) {
                $suspect       = 1;
                $suspectReason = implode('|', $reasons);
            }
        }

        $now = now();
        $ua  = Str::limit((string) $request->header('User-Agent'), 255, '');
        $ip  = $request->ip();

        $distanceForStore = (int) min(65535, max(0, round($distanceM)));
        $accuracyForStore = is_null($acc) ? null : (int) min(65535, max(0, round($acc)));

        try {
            DB::transaction(function () use (
                $assignment, $checkpoint, $lat, $lng,
                $distanceForStore, $accuracyForStore,
                $verified, $now, $ua, $ip,
                $speedMpsForStore, $jumpMForStore,
                $suspect, $suspectReason
            ) {
                CheckpointScan::create([
                    'patrol_assignment_id' => $assignment->id,
                    'checkpoint_id'        => $checkpoint->id,
                    'scanned_at'           => $now,
                    'lat'                  => $lat,
                    'lng'                  => $lng,
                    'distance_m'           => $distanceForStore,
                    'accuracy_m'           => $accuracyForStore,
                    'device_info'          => $ua,
                    'verified'             => $verified ? 1 : 0,
                    'source_ip'            => $ip,
                    'speed_mps'            => $speedMpsForStore,
                    'jump_m'               => $jumpMForStore,
                    'suspect'              => $suspect ? 1 : 0,
                    'suspect_reason'       => $suspectReason,
                ]);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                return redirect()
                    ->route('patrol.scan', ['c' => $checkpoint->qr_token, 'a' => $assignment->id])
                    ->with('warning', 'Este checkpoint ya fue registrado para esta asignación.');
            }
            throw $e;
        }

        // 🔄 Actualizar estado tras el scan:
        // - Si estaba scheduled, pasar a in_progress.
        // - Si alcanzó el total del snapshot, completar.
        $totalSnapshot = (int) $assignment->checkpoints()->count();
        $doneDistinct  = (int) $assignment->scans()
            ->distinct('checkpoint_id')
            ->count('checkpoint_id');

        if ($assignment->status === 'scheduled') {
            $assignment->update(['status' => 'in_progress']);
        }
        if ($totalSnapshot > 0 && $doneDistinct >= $totalSnapshot && $assignment->status !== 'completed') {
            $assignment->update(['status' => 'completed']);
        }

        // Redirigir SIEMPRE al GET con c & a
        return $verified
        ? redirect()->route('patrol.scan', ['c' => $checkpoint->qr_token, 'a' => $assignment->id])
            ->with('success', 'Punto verificado correctamente ✅')
        : redirect()->route('patrol.scan', ['c' => $checkpoint->qr_token, 'a' => $assignment->id])
            ->with('warning', 'Punto registrado, pero NO VERIFICADO (radio o precisión).');
    }

    private function patrolPolicy(): array
    {
        return [
            'accuracyMax'          => (int) config('patrol.accuracy_max', 50),
            'modoEstrictoAccuracy' => (bool) config('patrol.strict_accuracy', false),
            'modoEstrictoradio'    => (bool) config('patrol.strict_radius', false),
            'speedMaxMps'          => (float) config('patrol.speed_max_mps', 15),
            'jumpMaxM'             => (float) config('patrol.jump_max_m', 150),
            'jumpWindowS'          => (int) config('patrol.jump_window_s', 10),
        ];
    }

    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R     = 6371000;
        $toRad = fn($deg) => $deg * M_PI / 180;
        $dLat  = $toRad($lat2 - $lat1);
        $dLon  = $toRad($lon2 - $lon1);
        $a     = sin($dLat / 2) ** 2
         + cos($toRad($lat1)) * cos($toRad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
