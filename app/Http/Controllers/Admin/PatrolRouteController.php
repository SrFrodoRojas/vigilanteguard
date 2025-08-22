<?php
// app/Http/Controllers/Patrol/Admin/PatrolRouteController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\PatrolRoute;
use Illuminate\Http\Request;

class PatrolRouteController extends Controller
{
    public function index()
    {
        $routes = PatrolRoute::with('branch')->orderByDesc('id')->paginate(15);
        return view('admin.patrol.routes.index', compact('routes'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->pluck('name', 'id');
        return view('admin.patrol.routes.create', compact('branches'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'branch_id'             => 'required|exists:branches,id',
            'name'                  => 'required|string|max:120',
            'expected_duration_min' => 'required|integer|min:5|max:480',
        ]);
        $data['active']       = $r->boolean('active');
        $data['qr_required']  = $r->boolean('qr_required');
        $data['min_radius_m'] = (int) $r->input('min_radius_m', 20);
        PatrolRoute::create($data);
        return redirect()->route('admin.patrol.routes.index')->with('success', 'Ruta creada.');
    }

    public function edit(PatrolRoute $route)
    {
        $branches = Branch::orderBy('name')->pluck('name', 'id');
        return view('admin.patrol.routes.edit', compact('route', 'branches'));
    }

    public function update(Request $r, PatrolRoute $route)
    {
        $data = $r->validate([
            'branch_id'             => 'required|exists:branches,id',
            'name'                  => 'required|string|max:120',
            'expected_duration_min' => 'required|integer|min:5|max:480',
        ]);
        $data['active']       = $r->boolean('active');
        $data['qr_required']  = $r->boolean('qr_required');
        $data['min_radius_m'] = (int) $r->input('min_radius_m', 20);
        $route->update($data);
        return back()->with('success', 'Ruta actualizada.');
    }

    public function destroy(PatrolRoute $route)
    {
        $route->delete();
        return back()->with('success', 'Ruta eliminada.');
    }
}
