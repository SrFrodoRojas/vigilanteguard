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
        $data = $r->validate([
            'guard_id'        => 'required|exists:users,id',
            'patrol_route_id' => 'required|exists:patrol_routes,id',
            'scheduled_start' => 'required|date',
            'scheduled_end'   => 'required|date|after:scheduled_start',
            'status'          => 'nullable|in:scheduled,in_progress,completed,missed,cancelled',
        ]);

        $assignment->update($data);
        return back()->with('success', 'Asignación actualizada.');
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
}
