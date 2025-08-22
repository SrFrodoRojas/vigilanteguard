<?php
namespace App\Http\Controllers\Patrol;

use App\Http\Controllers\Controller;
use App\Models\Checkpoint;
use App\Models\CheckpointScan;
use App\Models\PatrolAssignment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    use AuthorizesRequests;

    /**
     * Pantalla del escáner / registro de checkpoint.
     * Recibe ?c=qr_token (UUID) desde el QR físico.
     */
    public function showScanner(Request $request)
    {
        $qr = $request->query('c');

        $checkpoint = null;
        if ($qr) {
            $checkpoint = \App\Models\Checkpoint::with(['route.branch'])
                ->where('qr_token', $qr)
                ->first();
        }

        $assignment = null;
        if ($checkpoint) {
            $assignment = \App\Models\PatrolAssignment::where('guard_id', auth()->id())
                ->where('patrol_route_id', $checkpoint->patrol_route_id)
                ->where(function ($q) {
                    $now = now();
                    $q->whereBetween('scheduled_start', [$now->copy()->subHours(2), $now->copy()->addHours(2)])
                        ->orWhereBetween('scheduled_end', [$now->copy()->subHours(2), $now->copy()->addHours(2)]);
                })
                ->orderBy('scheduled_start', 'desc')
                ->first();
        }

        // Si NO hay checkpoint (entraste sin ?c), mostrar las asignaciones activas del guardia para que elija una.
        $myAssignments = null;
        if (! $checkpoint) {
            $myAssignments = \App\Models\PatrolAssignment::with('route.branch')
                ->where('guard_id', auth()->id())
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->orderBy('scheduled_start', 'desc')
                ->get();
        }

        return view('patrol.scan', [
            'checkpoint'    => $checkpoint,
            'assignment'    => $assignment,
            'myAssignments' => $myAssignments, // puede ser null
        ]);
    }

    /**
     * Registrar un paso por el checkpoint (requiere QR + GPS).
     */
    public function store(Request $request)
    {
        
        // Validación básica del flujo QR + GPS
        $data = $request->validate(
            [
                'qr_token'      => 'required|uuid',
                'assignment_id' => 'required|integer|exists:patrol_assignments,id',
                'lat'           => 'required|numeric',
                'lng'           => 'required|numeric',
                'accuracy_m'    => 'nullable|integer|min:0',
            ],
            [],
            ['lat' => 'latitud', 'lng' => 'longitud']
        );

        // Resolver checkpoint por token
        $checkpoint = Checkpoint::with('route')
            ->where('qr_token', $data['qr_token'])
            ->first();

        if (! $checkpoint) {
            return back()->withErrors('QR desconocido o inválido.');
        }

        // Resolver asignación: debe ser del usuario y de la ruta del checkpoint
        $assignment = PatrolAssignment::where('id', $data['assignment_id'])
            ->where('guard_id', auth()->id())
            ->where('patrol_route_id', $checkpoint->patrol_route_id)
            ->first();

        if (! $assignment) {
            return back()->withErrors('No tenés una patrulla activa para esta ruta.');
        }

        // Policy (PatrolAssignmentPolicy@update)
        $this->authorize('update', $assignment);

        // Validar ventana horaria
        $now = now();
        if (! ($assignment->scheduled_start <= $now && $now <= $assignment->scheduled_end)) {
            return back()->withErrors('Estás fuera del horario programado de la patrulla.');
        }

        // Evitar duplicados de este punto en esta asignación
        $already = CheckpointScan::where('patrol_assignment_id', $assignment->id)
            ->where('checkpoint_id', $checkpoint->id)
            ->exists();

        if ($already) {
            return back()->withErrors('Este punto ya fue registrado en esta patrulla.');
        }

                                               // Distancia y verificación por radio
        $route           = $checkpoint->route; // tiene min_radius_m y qr_required (si lo configuraste)
        $effectiveRadius = max((int) $checkpoint->radius_m, (int) ($route->min_radius_m ?? 20));

        [$distanceM, $verified] = $this->evaluateDistance(
            (float) $checkpoint->latitude,
            (float) $checkpoint->longitude,
            (float) $data['lat'],
            (float) $data['lng'],
            $effectiveRadius
        );

        // Aviso por precisión pobre
        if (! empty($data['accuracy_m']) && $data['accuracy_m'] > 200) {
            session()->flash('warning', 'La precisión del GPS es baja (± ' . (int) $data['accuracy_m'] . ' m).');
        }

        // Anti-fraude: salto/velocidad vs último scan del guardia
        $lastScan = CheckpointScan::whereHas('assignment', function ($q) {
            $q->where('guard_id', auth()->id());
        })
            ->latest('scanned_at')
            ->first();

        $jumpM         = null;
        $speedMps      = null;
        $suspect       = false;
        $suspectReason = null;

        if ($lastScan && $lastScan->lat && $lastScan->lng) {
            $dist = $this->haversineM((float) $lastScan->lat, (float) $lastScan->lng, (float) $data['lat'], (float) $data['lng']);
            $dt   = max(1, $now->diffInSeconds($lastScan->scanned_at));
            $spd  = (int) round($dist / $dt);

            $jumpM    = (int) round($dist);
            $speedMps = $spd;

            // Reglas: salto > 5km en ≤60s o velocidad > 120km/h (~33 m/s)
            if (($dist >= 5000 && $dt <= 60) || $spd > 33) {
                $suspect       = true;
                $suspectReason = ($dist >= 5000 && $dt <= 60)
                ? 'jump_gt_5km_in_1min'
                : 'speed_gt_120kmh';
            }
        }

        // Guardar scan
        CheckpointScan::create([
            'patrol_assignment_id' => $assignment->id,
            'checkpoint_id'        => $checkpoint->id,
            'scanned_at'           => $now,
            'lat'                  => $data['lat'],
            'lng'                  => $data['lng'],
            'distance_m'           => $distanceM,
            'accuracy_m'           => $data['accuracy_m'] ?? null,
            'device_info'          => substr($request->userAgent() ?? '', 0, 255),
            'verified'             => (bool) $verified,
            'source_ip'            => $request->ip(),

            // anti-fraude
            'speed_mps'            => $speedMps,
            'jump_m'               => $jumpM,
            'suspect'              => $suspect,
            'suspect_reason'       => $suspectReason,
        ]);

        // Actualizar estado de la asignación
        $total = $assignment->route?->checkpoints()->count() ?? 0;
        $done  = $assignment->scans()->count();

        if ($total > 0 && $done >= $total) {
            $assignment->update(['status' => 'completed']);
        } elseif ($assignment->status === 'scheduled') {
            $assignment->update(['status' => 'in_progress']);
        }

        return redirect()
            ->route('patrol.index')
            ->with('success', $verified ? 'Checkpoint verificado.' : 'Checkpoint registrado fuera de radio (revisión).');
    }

    // --------------------------------------
    // Utils
    // --------------------------------------

    private function evaluateDistance(float $latCk, float $lngCk, float $lat, float $lng, int $radiusM): array
    {
        $d = $this->haversineM($latCk, $lngCk, $lat, $lng);
        return [$d, $d <= $radiusM];
    }

    private function haversineM(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $R     = 6371000; // m
        $toRad = fn($x) => $x * M_PI / 180;
        $dLat  = $toRad($lat2 - $lat1);
        $dLng  = $toRad($lng2 - $lng1);
        $a     = sin($dLat / 2) ** 2 + cos($toRad($lat1)) * cos($toRad($lat2)) * sin($dLng / 2) ** 2;
        return (int) round(2 * $R * atan2(sqrt($a), sqrt(1 - $a)));
    }
}
