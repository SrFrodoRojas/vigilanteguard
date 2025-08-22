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

        $assignments = PatrolAssignment::with(['route.checkpoints', 'route.branch', 'scans'])
            ->where('guard_id', auth()->id())
            ->where('scheduled_end', '>=', $now->subHours(12)) // mostrar activas y las últimas
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
    public function finish(PatrolAssignment $assignment)
    {
        $this->authorize('update', $assignment);

        $assignment->update(['status' => 'completed']);

        return back()->with('success', 'Patrulla finalizada.');
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
