<?php
// app/Http/Controllers/Admin/PatrolAssignmentController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatrolAssignment;
use App\Models\PatrolRoute;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PatrolAssignmentController extends Controller
{
    public function index()
    {
        $assignments = PatrolAssignment::with(['route', 'guardUser'])->orderByDesc('id')->paginate(20);
        return view('admin.patrol.assignments.index', compact('assignments'));
    }

    public function create()
    {
        $routes = PatrolRoute::where('active', true)->with('branch')->orderBy('name')->get();
        $guards = $this->guardsList();
        return view('admin.patrol.assignments.create', compact('routes', 'guards'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'guard_id'        => 'required|exists:users,id',
            'patrol_route_id' => 'required|exists:patrol_routes,id',
            'scheduled_start' => 'required|date',
            'scheduled_end'   => 'required|date|after:scheduled_start',
        ]);

        // por defecto
        $data['status'] = 'scheduled';

        PatrolAssignment::create($data);
        return redirect()->route('admin.patrol.assignments.index')->with('success', 'Asignación creada.');
    }

    public function edit(PatrolAssignment $assignment)
    {
        $routes = PatrolRoute::where('active', true)->with('branch')->orderBy('name')->get();
        $guards = $this->guardsList(); // <-- misma lógica que create()
        return view('admin.patrol.assignments.edit', compact('assignment', 'routes', 'guards'));
    }

    public function update(Request $r, PatrolAssignment $assignment)
    {
        // Validación compatible con tu flujo actual
        $data = $r->validate([
            'guard_id'        => 'required|exists:users,id',
            'patrol_route_id' => 'required|exists:patrol_routes,id',
            'scheduled_start' => 'required|date',
            'scheduled_end'   => 'required|date|after:scheduled_start',
            'status'          => 'nullable|in:scheduled,in_progress,completed,missed,cancelled',
        ]);

        $strict = (bool) config('patrol.strict_assignment_transitions', false);

        $from = (string) ($assignment->status ?? 'scheduled');
        $to   = (string) ($data['status'] ?? $from);

        // Si el modo estricto está activo y pretenden cambiar a una transición inválida → 422
        if ($strict && $to !== $from && ! $this->isAllowedTransition($from, $to)) {
            throw ValidationException::withMessages([
                'status' => "Transición inválida: {$from} → {$to}",
            ]);
        }

        DB::transaction(function () use ($assignment, $data, $from, $to) {
            // Aplicar cambios básicos
            $assignment->fill($data);

            // Conveniencias suaves (solo si existen las columnas):
            // - al entrar en progreso, setear started_at si no vino
            if ($from !== 'in_progress' && $to === 'in_progress'
                && Schema::hasColumn($assignment->getTable(), 'started_at')
                && empty($assignment->started_at)) {
                $assignment->started_at = now();
            }

            // - al pasar a estado terminal, setear ended_at si no vino
            if ($from !== $to && in_array($to, ['completed', 'missed', 'cancelled'], true)
                && Schema::hasColumn($assignment->getTable(), 'ended_at')
                && empty($assignment->ended_at)) {
                $assignment->ended_at = now();
            }

            $assignment->save();
        });

        return back()->with('success', "Asignación actualizada: {$from} → {$to}");
    }

    public function destroy(PatrolAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', 'Asignación eliminada.');
    }

    /**
     * Devuelve los usuarios que pueden patrullar:
     * - con rol 'guard'  OR  con permiso 'patrol.scan'
     */
    private function guardsList()
    {
        $byRole = User::role('guard')->get();
        $byPerm = User::permission('patrol.scan')->get();

        return $byRole->merge($byPerm)
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    /**
     * Transiciones válidas:
     * scheduled -> in_progress
     * in_progress -> completed|missed|cancelled
     * completed/missed/cancelled -> terminales
     */
    private function isAllowedTransition(string $from, string $to): bool
    {
        $map = [
            'scheduled'   => ['in_progress'],
            'in_progress' => ['completed', 'missed', 'cancelled'],
            'completed'   => [],
            'missed'      => [],
            'cancelled'   => [],
        ];

        $from = $from ?: 'scheduled';
        $to   = $to ?: $from;

        // Permitimos "no cambio" (editar otros campos sin cambiar status)
        return in_array($to, $map[$from] ?? [], true) || $from === $to;
    }

}
