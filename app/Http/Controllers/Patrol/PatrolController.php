<?php
namespace App\Http\Controllers\Patrol;

use App\Http\Controllers\Controller;
use App\Models\PatrolAssignment;

class PatrolController extends Controller
{
    /**
     * Panel del guardia: ver sus patrullas recientes/activas.
     */
    public function index()
    {
        $now = now();

        $assignments = \App\Models\PatrolAssignment::with(['route.branch', 'scans'])
            ->withCount([
                // total del snapshot
                'checkpoints as checkpoints_total',
                // cantidad de scans (evita ->count() N+1 en la vista)
                'scans as scans_count',
            ])
            ->where('guard_id', auth()->id())
            ->where('scheduled_end', '>=', $now->subHours(12))
            ->orderBy('scheduled_start', 'desc')
            ->get();

        return view('patrol.index', compact('assignments', 'now'));
    }

    /**
     * Iniciar patrulla (Policy: update).
     */
    public function start(PatrolAssignment $assignment)
    {
        $this->authorize('update', $assignment);

        $assignment->update(['status' => 'in_progress']);

        return back()->with('success', 'Patrulla iniciada.');
    }

    /**
     * Finalizar patrulla (Policy: update).
     */
    public function finish(\App\Models\PatrolAssignment $assignment)
    {
        $this->authorize('update', $assignment);

        // Total del snapshot (si no hay, lo construimos al vuelo)
        $total = (int) $assignment->checkpoints()->count();

        if ($total === 0) {
            // Si no existe snapshot: preferimos congelar a lo escaneado (si hay),
            // sino a los checkpoints actuales de la ruta (fallback).
            $scannedIds = $assignment->scans()
                ->pluck('checkpoint_id')->unique()->values()->all();

            if (count($scannedIds)) {
                $assignment->checkpoints()->sync($scannedIds);
                $total = count($scannedIds);
            } else {
                $routeIds = \App\Models\Checkpoint::where('patrol_route_id', $assignment->patrol_route_id)
                    ->pluck('id')->all();
                $assignment->checkpoints()->sync($routeIds);
                $total = count($routeIds);
            }
        }

        // Hechos (distinct por checkpoint)
        $done = (int) $assignment->scans()
            ->distinct('checkpoint_id')
            ->count('checkpoint_id');

        $newStatus = ($total > 0 && $done >= $total) ? 'completed' : 'missed';

        $assignment->update(['status' => $newStatus]);

        return back()->with(
            'success',
            $newStatus === 'completed'
            ? 'Patrulla finalizada (completa).'
            : 'Patrulla finalizada como PERDIDA (faltan puntos).'
        );
    }

    /**
     * Posponer patrullaje (snooze).
     */
    public function snooze(\Illuminate\Http\Request $r, \App\Models\PatrolAssignment $assignment)
    {
        $this->authorize('update', $assignment);

        // Validar razón de la posposición
        $r->validate(['reason' => 'required|string|max:255']);

        // Verificar que no se haya pospuesto más de tres veces
        if ($assignment->snooze_count >= 3) {
            return back()->withErrors('Ya usaste los 3 pospuestos permitidos.');
        }

        // Desplazar el patrullaje 10 minutos
        $minutes = 10;
        $assignment->update([
            'scheduled_start' => $assignment->scheduled_start->addMinutes($minutes),
            'scheduled_end'   => $assignment->scheduled_end->addMinutes($minutes),
            'snooze_count'    => $assignment->snooze_count + 1,
        ]);

        \App\Models\PatrolAssignmentSnooze::create([
            'patrol_assignment_id' => $assignment->id,
            'user_id'              => auth()->id(),
            'minutes'              => $minutes,
            'reason'               => $r->reason,
        ]);

        return back()->with('success', 'Inicio de patrulla pospuesto 10 minutos.');
    }
}
