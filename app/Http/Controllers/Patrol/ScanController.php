<?php
// app/Http/Controllers/Patrol/ScanController.php

namespace App\Http\Controllers\Patrol;

use App\Http\Controllers\Controller;
use App\Models\Checkpoint;
use App\Models\CheckpointScan;
use App\Models\PatrolAssignment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    /**
     * Pantalla del escáner (UI).
     * Mantén la tuya si ya existe; este método es compatible con ?c=<qr_token>.
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
     * Registrar paso por checkpoint (POST /patrol/scan).
     */
    public function store(Request $request)
    {
        // Normalizar accuracy si vino como accuracy_m (desde la vista)
        if ($request->has('accuracy_m') && ! $request->has('accuracy')) {
            $request->merge(['accuracy' => $request->input('accuracy_m')]);
        }

        // 1) Validación
        $data = $request->validate([
            'assignment_id' => 'required|exists:patrol_assignments,id',
            'qr_token'      => 'required|string',
            'lat'           => 'required|numeric|between:-90,90',
            'lng'           => 'required|numeric|between:-180,180',
            'accuracy'      => 'nullable|numeric|min:0',
        ]);

        // 2) Resolver assignment y checkpoint (misma ruta)
        $assignment = PatrolAssignment::with('route')->findOrFail($data['assignment_id']);

        $checkpoint = Checkpoint::with('route')
            ->where('qr_token', $data['qr_token'])
            ->first();

        if (! $checkpoint) {
            return back()->with('warning', 'QR inválido o no corresponde a un checkpoint activo.');
        }
        if ((int) $checkpoint->patrol_route_id !== (int) $assignment->patrol_route_id) {
            return back()->with('warning', 'El checkpoint no pertenece a la ruta de tu asignación.');
        }

        // 3) Cargar política (defaults idénticos)
        [
            'accuracyMax'          => $accuracyMax,
            'modoEstrictoAccuracy' => $modoEstrictoAccuracy,
            'modoEstrictoradio'    => $modoEstrictoradio,
            'speedMaxMps'          => $speedMaxMps,
            'jumpMaxM'             => $jumpMaxM,
            'jumpWindowS'          => $jumpWindowS,
        ] = $this->patrolPolicy();

        // 4) Calcular métrica espacial
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

        // 5) Política verificado / no verificado
        $verified   = 1;
        $accToCheck = is_null($acc) ? 9999 : $acc;

        if ($accToCheck > $accuracyMax) {
            if ($modoEstrictoAccuracy) {
                return back()->with('warning', "Precisión insuficiente (±" . round($accToCheck) . " m > {$accuracyMax} m). Mejorá la señal y reintenta.");
            }
            $verified = 0;
        }

        if ($distanceM > $effectiveRadiusM) {
            if ($modoEstrictoradio) {
                return back()->with('warning', "Fuera de radio (dist: " . round($distanceM) . " m, permitido: {$effectiveRadiusM} m). Acercate al punto.");
            }
            $verified = 0;
        }

        // 6) Anti-fraude (velocidad/salto) basados en último scan por asignación
        $suspect          = 0;
        $suspectReason    = null;
        $speedMpsForStore = null;
        $jumpMForStore    = null;

        $lastScan = CheckpointScan::where('patrol_assignment_id', $assignment->id)
            ->orderByDesc('scanned_at')
            ->first();

        if ($lastScan) {
            $dt = max(1, now()->diffInSeconds($lastScan->scanned_at)); // s
            $dd = $this->haversineMeters(
                (float) $lastScan->lat,
                (float) $lastScan->lng,
                $lat,
                $lng
            ); // m

            $speed            = $dd / $dt; // m/s
            $speedMpsForStore = (int) round($speed);
            $jumpMForStore    = (int) round($dd);

            $reasons = [];
            if ($speed > $speedMaxMps) {
                $reasons[] = "speed>{$speedMaxMps}m/s";
            }
            if ($dt < $jumpWindowS && $dd > $jumpMaxM) {
                $reasons[] = "jump>{$jumpMaxM}m<{$jumpWindowS}s";
            }
            if (! empty($reasons)) {
                $suspect       = 1;
                $suspectReason = implode('|', $reasons);
                // Si querés que todo sospechoso quede no verificado, descomentá:
                // $verified = 0;
            }
        }

        // 7) Persistencia (campos ajustados a tu schema)
        $now = now();
        $ua  = Str::limit((string) $request->header('User-Agent'), 255, '');
        $ip  = $request->ip();

        // Ajustar tipos a columnas SMALLINT UNSIGNED (0..65535)
        $distanceForStore = (int) min(65535, max(0, round($distanceM)));
        $accuracyForStore = is_null($accToCheck) ? null : (int) min(65535, max(0, round($accToCheck)));

        try {
            DB::transaction(function () use (
                $assignment,
                $checkpoint,
                $lat,
                $lng,
                $distanceForStore,
                $accuracyForStore,
                $verified,
                $now,
                $ua,
                $ip,
                $speedMpsForStore,
                $jumpMForStore,
                $suspect,
                $suspectReason
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
        } catch (QueryException $e) {
            // Por si tenés UNIQUE (patrol_assignment_id, checkpoint_id)
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {
                return back()->with('warning', 'Este checkpoint ya fue registrado para esta asignación.');
            }
            throw $e;
        }

        return $verified
        ? back()->with('success', 'Punto verificado correctamente ✅')
        : back()->with('warning', 'Punto registrado, pero NO VERIFICADO (radio o precisión).');
    }

    /**
     * Política (lee config/patrol.php).
     */
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

    /**
     * Distancia Haversine en metros.
     */
    private function haversineMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R     = 6371000; // m
        $toRad = fn($deg) => $deg * M_PI / 180;
        $dLat  = $toRad($lat2 - $lat1);
        $dLon  = $toRad($lon2 - $lon1);
        $a     = sin($dLat / 2) * sin($dLat / 2)
         + cos($toRad($lat1)) * cos($toRad($lat2))
         * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    public function showScanner(Request $request)
    {
        $checkpoint = null;

        // Si abriste desde un QR con ?c=<qr_token>
        if ($request->filled('c')) {
            $checkpoint = Checkpoint::with(['route.branch'])
                ->where('qr_token', $request->input('c'))
                ->first();
        }

        // Asignaciones del guardia autenticado
        $myAssignments = PatrolAssignment::with(['route.branch'])
            ->where('guard_id', Auth::id())
            ->orderByDesc('scheduled_start')
            ->limit(20)
            ->get();

        // Preselección por ?a=<assignment_id> o por ruta del checkpoint
        $assignment = null;
        if ($request->filled('a')) {
            $assignment = $myAssignments->firstWhere('id', (int) $request->input('a'));
        }
        if (! $assignment && $checkpoint) {
            $assignment = $myAssignments->firstWhere('patrol_route_id', $checkpoint->patrol_route_id);
        }

        // ¿Ya fue registrado este checkpoint en esta asignación?
        $alreadyScanned = false;
        if ($checkpoint && $assignment) {
            $alreadyScanned = CheckpointScan::where('patrol_assignment_id', $assignment->id)
                ->where('checkpoint_id', $checkpoint->id)
                ->exists();
        }

        return view('patrol.scan', compact('checkpoint', 'assignment', 'myAssignments', 'alreadyScanned'));
    }

}
