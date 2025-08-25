<?php
// app/Http/Controllers/Admin/PatrolAssignmentController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatrolAssignment;
use App\Models\PatrolRoute;
use App\Models\User;
use Illuminate\Http\Request;

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

        // estado inicial
        $data['status'] = 'scheduled';

        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            /** @var \App\Models\PatrolAssignment $assignment */
            $assignment = \App\Models\PatrolAssignment::create($data);

            // SNAPSHOT: checkpoint_ids actuales de la ruta
            $checkpointIds = \App\Models\Checkpoint::where('patrol_route_id', $assignment->patrol_route_id)
                ->pluck('id')->all();

            if (! empty($checkpointIds)) {
                // sync sin detach (recién creada, por seguridad igual no quita nada)
                $assignment->checkpoints()->sync($checkpointIds, false);
            }
        });

        return redirect()->route('admin.patrol.assignments.index')
            ->with('success', 'Asignación creada y snapshot de checkpoints tomado.');
    }

    public function edit(PatrolAssignment $assignment)
    {
        $routes = PatrolRoute::where('active', true)->with('branch')->orderBy('name')->get();
        $guards = $this->guardsList(); // <-- misma lógica que create()
        return view('admin.patrol.assignments.edit', compact('assignment', 'routes', 'guards'));
    }

    public function update(Request $r, \App\Models\PatrolAssignment $assignment)
    {
        $data = $r->validate([
            'guard_id'        => 'required|exists:users,id',
            'patrol_route_id' => 'required|exists:patrol_routes,id',
            'scheduled_start' => 'required|date',
            'scheduled_end'   => 'required|date|after:scheduled_start',
            'status'          => 'nullable|in:scheduled,in_progress,completed,missed,cancelled',
        ]);

        $currentStatus = $assignment->status;
        $targetStatus  = $data['status'] ?? $currentStatus;

        // 1) Transición de estado válida
        if ($targetStatus !== $currentStatus && ! $this->isAllowedTransition($currentStatus, $targetStatus)) {
            return back()->withErrors("Transición no permitida: {$currentStatus} → {$targetStatus}")->withInput();
        }

        // 2) Si hay scans, NO permitir cambiar guardia ni ruta
        $changingGuard = (int) $data['guard_id'] !== (int) $assignment->guard_id;
        $changingRoute = (int) $data['patrol_route_id'] !== (int) $assignment->patrol_route_id;
        if (($changingGuard || $changingRoute) && $assignment->scans()->exists()) {
            return back()->withErrors('No se puede cambiar guardia/ruta porque ya existen registros de escaneo.')->withInput();
        }

        // 3) Fechas solo editables cuando está scheduled
        $startChanged  = ! $assignment->scheduled_start->equalTo(\Carbon\Carbon::parse($data['scheduled_start']));
        $endChanged    = ! $assignment->scheduled_end->equalTo(\Carbon\Carbon::parse($data['scheduled_end']));
        $changingDates = $startChanged || $endChanged;

        \Illuminate\Support\Facades\DB::transaction(function () use ($assignment, $data, $targetStatus) {
            $data['status'] = $targetStatus; // normaliza cuando viene null
            $assignment->update($data);
        });

        return back()->with('success', 'Asignación actualizada.');
    }

    public function destroy(PatrolAssignment $assignment)
    {
        if ($assignment->scans()->exists()) {
            return back()->withErrors('No se puede eliminar: la asignación ya tiene escaneos.');
        }
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
     * Reglas de transición de estado:
     * scheduled   -> in_progress | cancelled
     * in_progress -> completed | missed | cancelled
     * completed/missed/cancelled -> terminales (sin cambios)
     */
    private function isAllowedTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return match ($from) {
            'scheduled' => in_array($to, ['in_progress', 'cancelled'], true),
            'in_progress' => in_array($to, ['completed', 'missed', 'cancelled'], true),
            default => false,
        };
    }

}
