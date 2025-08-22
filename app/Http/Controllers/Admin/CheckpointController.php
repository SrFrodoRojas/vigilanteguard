<?php
// app/Http/Controllers/Patrol/Admin/CheckpointController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatrolRoute;
use App\Models\Checkpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckpointController extends Controller
{
    public function index(PatrolRoute $route)
    {
        // Cambié .get() por .paginate() para habilitar la paginación
        $checkpoints = $route->checkpoints()->orderBy('id')->paginate(10); // 10 es el número de items por página

        return view('admin.patrol.checkpoints.index', compact('route', 'checkpoints'));
    }

    public function create(PatrolRoute $route)
    {
        return view('admin.patrol.checkpoints.create', compact('route'));
    }

    public function store(Request $r, PatrolRoute $route)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_m' => 'required|integer|min:5|max:200',
        ]);

        $data['patrol_route_id'] = $route->id;
        $data['qr_token']  = (string) Str::uuid();
        $data['short_code'] = strtoupper(Str::random(8));

        Checkpoint::create($data);
        return redirect()->route('admin.patrol.routes.checkpoints.index', $route)->with('success','Checkpoint creado.');
    }

    public function edit(Checkpoint $checkpoint)
    {
        $route = $checkpoint->route;
        return view('admin.patrol.checkpoints.edit', compact('route','checkpoint'));
    }

    public function update(Request $r, Checkpoint $checkpoint)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_m' => 'required|integer|min:5|max:200',
        ]);
        $checkpoint->update($data);
        return back()->with('success','Checkpoint actualizado.');
    }

    public function destroy(Checkpoint $checkpoint)
    {
        $route = $checkpoint->route;
        $checkpoint->delete();
        return redirect()->route('admin.patrol.routes.checkpoints.index', $route)->with('success','Checkpoint eliminado.');
    }
}
