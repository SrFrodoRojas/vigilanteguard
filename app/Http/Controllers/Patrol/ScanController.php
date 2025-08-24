<?php
namespace App\Http\Controllers\Patrol;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    use AuthorizesRequests;

    /**
     * Pantalla del escáner / registro de checkpoint.
     * Recibe ?c=qr_token (UUID) desde el QR físico.
     */

    private function patrolPolicy(): array
    {
        return [
            'accuracyMax'          => (int) config('patrol.accuracy_max', 50),
            'modoEstrictoAccuracy' => (bool) config('patrol.strict_accuracy', false),
            'modoEstrictoradio'    => (bool) config('patrol.strict_radius', false),
            // Quedan disponibles si el flujo ya los usa:
            'speedMaxMps'          => (float) config('patrol.speed_max_mps', 15),
            'jumpMaxM'             => (float) config('patrol.jump_max_m', 150),
            'jumpWindowS'          => (int) config('patrol.jump_window_s', 10),
        ];
    }

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
    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'patrol_assignment_id' => ['required', 'integer', 'exists:patrol_assignments,id'],
            'checkpoint_id'        => ['required', 'integer', 'exists:checkpoints,id'],
            'lat'                  => ['nullable', 'numeric', 'between:-90,90'],
            'lng'                  => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy_m'           => ['nullable', 'integer', 'min:0', 'max:5000'],
            'device_info'          => ['nullable', 'string', 'max:255'],
        ]);

        [
            'accuracyMax'          => $accuracyMax,
            'modoEstrictoAccuracy' => $modoEstrictoAccuracy,
            'modoEstrictoradio'    => $modoEstrictoradio,
            'speedMaxMps'          => $speedMaxMps,
            'jumpMaxM'             => $jumpMaxM,
            'jumpWindowS'          => $jumpWindowS,
        ] = $this->patrolPolicy();

        // 1) Entidades
        $assignment = \App\Models\PatrolAssignment::findOrFail($data['patrol_assignment_id']);
        $checkpoint = \App\Models\Checkpoint::findOrFail($data['checkpoint_id']);

        // 2) Autorización / coherencia
        if ($assignment->guard_id !== $request->user()->id) {
            abort(403, 'Esta asignación no te pertenece.');
        }
        if ($assignment->patrol_route_id !== $checkpoint->patrol_route_id) {
            abort(422, 'El checkpoint no pertenece a la ruta de esta asignación.');
        }
        if (in_array($assignment->status, ['cancelled', 'missed', 'completed'], true)) {
            return back()->with('warn', 'La asignación no está activa.');
        }

        // 2.1) Requiere GPS si la ruta lo pide
        $requiereGps = (int) ($checkpoint->route?->qr_required ?? 1) === 1;
        if ($requiereGps && (is_null($data['lat']) || is_null($data['lng']))) {
            return back()->with('warn', 'Este checkpoint requiere GPS: habilita la ubicación e intenta de nuevo.');
        }

        // 3) Evitar doble escaneo (pre-chequeo)
        $already = \App\Models\CheckpointScan::where('patrol_assignment_id', $assignment->id)
            ->where('checkpoint_id', $checkpoint->id)
            ->exists();
        if ($already) {
            return back()->with('warn', 'Este checkpoint ya fue escaneado para esta asignación.');
        }

        // 4) Distancia y verificación
        $distance = null;
        if (! is_null($data['lat']) && ! is_null($data['lng'])) {
            $distance = $this->haversine(
                (float) $data['lat'], (float) $data['lng'],
                (float) $checkpoint->latitude, (float) $checkpoint->longitude
            );
        }

        // Radio efectivo: mayor entre radio del checkpoint y mínimo de la ruta
        $radioMinRuta = (int) ($checkpoint->route->min_radius_m ?? 0);
        $radioEff     = max((int) $checkpoint->radius_m, $radioMinRuta);

        // Política de accuracy
        $accuracy     = $data['accuracy_m'] ?? null;
        $accuracyBaja = $requiereGps && ! is_null($accuracy) && $accuracy > $accuracyMax;

        // Si es estricto por accuracy, no guardamos con precisión pobre
        if ($modoEstrictoAccuracy && $accuracyBaja) {
            return back()->with('warn', "Precisión GPS insuficiente (>{$accuracyMax} m). Acércate y reintenta.");
        }

        // Verificado solo si dentro del radio efectivo y sin accuracy baja (en modo no estricto, accuracy baja => no verificado)
        $verified = (! is_null($distance) && $distance <= $radioEff && ! ($accuracyBaja)) ? 1 : 0;

        // 4.1) Modo estricto de radio: si está activo, bloquea guardado fuera de radio
        if ($modoEstrictoradio) {
            if (is_null($distance)) {
                return back()->with('warn', 'Checkpoint requiere GPS válido.');
            }
            if ($distance > $radioEff) {
                return back()->with('warn', 'Fuera de radio: acércate al checkpoint y vuelve a escanear.');
            }
        }

        // 5) Anti-fraude velocidad/salto
        $prev = \App\Models\CheckpointScan::where('patrol_assignment_id', $assignment->id)
            ->latest('scanned_at')->first();

        $speedMps = null;
        $jumpM    = null;
        $suspect  = 0;
        $reason   = null;

        if (
            $prev &&
            ! is_null($data['lat']) && ! is_null($data['lng']) &&
            ! is_null($prev->lat) && ! is_null($prev->lng)
        ) {
            $dt       = max(1, abs(now()->diffInSeconds($prev->scanned_at)));
            $distPrev = $this->haversine((float) $data['lat'], (float) $data['lng'], (float) $prev->lat, (float) $prev->lng);
            $speedMps = (int) round($distPrev / $dt);
            $jumpM    = (int) round($distPrev);

            if ($speedMps > 15 || ($jumpM > 150 && $dt < 10)) {
                $suspect = 1;
                $reason  = $speedMps > 15 ? 'speed' : 'jump';
            }
        }

        // 6) Guardar con transacción (manejo de carrera)
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $assignment, $checkpoint, $data, $distance, $verified, $speedMps, $jumpM, $suspect, $reason) {
                \App\Models\CheckpointScan::create([
                    'patrol_assignment_id' => $assignment->id,
                    'checkpoint_id'        => $checkpoint->id,
                    'scanned_at'           => now(),
                    'lat'                  => $data['lat'] ?? null,
                    'lng'                  => $data['lng'] ?? null,
                    'distance_m'           => is_null($distance) ? null : (int) round($distance),
                    'accuracy_m'           => $data['accuracy_m'] ?? null,
                    'device_info'          => $data['device_info'] ?? null,
                    'verified'             => $verified,
                    'source_ip'            => $request->ip(),
                    'speed_mps'            => $speedMps,
                    'jump_m'               => $jumpM,
                    'suspect'              => $suspect,
                    'suspect_reason'       => $reason,
                ]);

                if ($assignment->status === 'scheduled') {
                    $assignment->status = 'in_progress';
                    $assignment->save();
                }
            }, 1);
        } catch (\Illuminate\Database\QueryException $e) {
            if (($e->errorInfo[1] ?? null) === 1062) {
                return back()->with('warn', 'Este checkpoint ya fue escaneado para esta asignación.');
            }
            throw $e;
        }

        // 7) Mensaje final (si accuracy fue baja y no se bloqueó, queda como warn/no verificado)
        if ($verified === 1) {
            return back()->with('ok', 'Checkpoint verificado.');
        }
        if ($accuracyBaja) {
            return back()->with('warn', "Baja precisión GPS (>{$accuracyMax} m). Escaneo guardado como no verificado.");
        }
        return back()->with('warn', 'Fuera de radio, escaneo guardado como no verificado.');
    }

/** Distancia Haversine en metros */
    private function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R    = 6371000; // metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a    = sin($dLat / 2) * sin($dLat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

}
