<?php
// app/Http/Controllers/Patrol/Admin/PatrolRouteController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatrolRoute;

class PatrolRouteController extends Controller
{
    public function index()
    {
        $routes = \App\Models\PatrolRoute::with('branch')->orderBy('id', 'desc')->paginate(20);
        return view('admin.patrol.routes.index', compact('routes'));
    }

    public function create()
    {
        $branches = \App\Models\Branch::orderBy('name')->get();
        return view('admin.patrol.routes.create', compact('branches'));
    }

    public function store(\Illuminate\Http\Request $r)
    {
        $data = $r->validate([
            'name'                  => 'required|string|max:255',
            'branch_id'             => 'required|exists:branches,id',
            'expected_duration_min' => 'required|integer|min:1|max:1440',
            'min_radius_m'          => 'required|integer|min:1|max:10000',
            'qr_required'           => 'nullable|in:0,1',
            'active'                => 'nullable|in:0,1',
        ]);

        // Normalizar booleans (checkboxes)
        $data['qr_required'] = (int) ($data['qr_required'] ?? 0);
        $data['active']      = (int) ($data['active'] ?? 0);

        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            \App\Models\PatrolRoute::create($data);
        });

        return redirect()->route('admin.patrol.routes.index')->with('success', 'Ruta creada.');
    }

    public function edit(\App\Models\PatrolRoute $route)
    {
        $branches = \App\Models\Branch::orderBy('name')->get();
        return view('admin.patrol.routes.edit', compact('route', 'branches'));
    }

    public function update(\Illuminate\Http\Request $r, \App\Models\PatrolRoute $route)
    {
        $data = $r->validate([
            'name'                  => 'required|string|max:255',
            'branch_id'             => 'required|exists:branches,id',
            'expected_duration_min' => 'required|integer|min:1|max:1440',
            'min_radius_m'          => 'required|integer|min:1|max:10000',
            'qr_required'           => 'nullable|in:0,1',
            'active'                => 'nullable|in:0,1',
        ]);

        $data['qr_required'] = (int) ($data['qr_required'] ?? 0);
        $data['active']      = (int) ($data['active'] ?? 0);

        \Illuminate\Support\Facades\DB::transaction(function () use ($route, $data) {
            $route->update($data);
        });

        return redirect()->route('admin.patrol.routes.edit', $route)->with('success', 'Ruta actualizada.');
    }

    public function destroy(PatrolRoute $route)
    {
        $route->delete();
        return back()->with('success', 'Ruta eliminada.');
    }
}
