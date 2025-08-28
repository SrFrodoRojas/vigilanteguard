<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $tz         = 'America/Asuncion';
        $todayStart = Carbon::now($tz)->startOfDay();
        $todayEnd   = Carbon::now($tz)->endOfDay();

        $request->validate([
            'q'          => ['nullable', 'string', 'max:120'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'order'      => ['nullable', Rule::in(['name', 'users_count', 'active_now', 'total_today'])],
            'dir'        => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page'   => ['nullable', 'integer', 'min:5', 'max:100'],
            'branch_id'  => ['nullable', 'integer', 'exists:branches,id'], // para enlaces a reportes/resumen
        ]);

        $q         = $request->input('q');
        $managerId = $request->input('manager_id');
        $order     = $request->input('order', 'name');
        $dir       = $request->input('dir', 'asc');
        $perPage   = (int) ($request->input('per_page') ?: 12);

        $branches = Branch::query()
            ->with(['manager:id,name,email,phone'])
            ->withCount([
                'users',
                'accesses as active_now'       => fn($q)       => $q->whereNull('exit_at'),
                'accesses as total_today'      => fn($q)      => $q->whereBetween('entry_at', [$todayStart, $todayEnd]),
                'accesses as vehicle_today'    => fn($q)    => $q->whereBetween('entry_at', [$todayStart, $todayEnd])->where('type', 'vehicle'),
                'accesses as pedestrian_today' => fn($q) => $q->whereBetween('entry_at', [$todayStart, $todayEnd])->where('type', 'pedestrian'),
            ])
            ->when($q, function ($query) use ($q) {
                $term = trim($q);
                $query->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%")
                        ->orWhereHas('manager', fn($mq) => $mq->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($managerId, fn($query) => $query->where('manager_id', $managerId))
            ->when(in_array($order, ['name', 'users_count', 'active_now', 'total_today']), fn($query) => $query->orderBy($order, $dir))
            ->paginate($perPage)
            ->withQueryString();

        $managers = User::role('admin')->orderBy('name')->get(['id', 'name', 'email']);

        return view('admin.branches.index', compact('branches', 'managers', 'q', 'managerId', 'order', 'dir', 'perPage'));
    }

    public function create()
    {
        $managers = User::role('admin')->orderBy('name')->get(['id', 'name', 'email', 'phone']);
        return view('admin.branches.create', compact('managers'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'location'   => ['required', 'string', 'max:255'],
            'color'      => ['nullable', 'string', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $color = $data['color'] ?? null;
        if ($color) {
            $color = ltrim($color, '#');
            $color = '#' . strtoupper($color);
        }

        Branch::create([
            'name'       => $data['name'],
            'location'   => $data['location'],
            'color'      => $color,
            'manager_id' => $data['manager_id'] ?? null,
        ]);

        return redirect()->route('branches.index')->with('ok', 'Sucursal creada.');
    }

    public function update(\Illuminate\Http\Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'location'   => ['required', 'string', 'max:255'],
            'color'      => ['nullable', 'string', 'regex:/^#?[0-9A-Fa-f]{6}$/'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $color = $data['color'] ?? null;
        if ($color) {
            $color = ltrim($color, '#');
            $color = '#' . strtoupper($color);
        }

        $branch->update([
            'name'       => $data['name'],
            'location'   => $data['location'],
            'color'      => $color,
            'manager_id' => $data['manager_id'] ?? null,
        ]);

        return redirect()->route('branches.index')->with('ok', 'Sucursal actualizada.');
    }

    public function edit(Branch $branch)
    {
        $managers = User::role('admin')->orderBy('name')->get(['id', 'name', 'email', 'phone']);
        $branch->load(['users' => fn($q) => $q->orderBy('name')]);
        return view('admin.branches.edit', compact('branch', 'managers'));
    }

    public function destroy(Branch $branch)
    {
        if ($branch->users()->exists()) {
            return back()->withErrors('No se puede eliminar una sucursal con guardias asignados.');
        }
        if ($branch->accesses()->exists()) {
            return back()->withErrors('No se puede eliminar una sucursal con movimientos registrados.');
        }
        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Sucursal eliminada correctamente.');
    }

    // ---- Acciones masivas de guardias en una sucursal ----
    public function massUpdate(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'user_ids'         => ['required', 'array', 'min:1'],
            'user_ids.*'       => ['integer', 'exists:users,id'],
            'action'           => ['required', Rule::in(['activate', 'deactivate', 'transfer'])],
            'target_branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $ids = collect($data['user_ids'])->unique()->values();

        // Filtrar a solo guardias de ESTA sucursal (para activar/desactivar/transferir)
        $ids = $branch->users()->whereIn('id', $ids)->pluck('id');

        if ($ids->isEmpty()) {
            return back()->withErrors('No se seleccionaron guardias válidos de esta sucursal.');
        }

        $updated = 0;

        DB::transaction(function () use ($data, $ids, $branch, &$updated) {
            switch ($data['action']) {
                case 'activate':
                    $updated = User::whereIn('id', $ids)->update(['is_active' => 1]);
                    break;

                case 'deactivate':
                    $updated = User::whereIn('id', $ids)->update(['is_active' => 0]);
                    break;

                case 'transfer':
                    $target = (int) ($data['target_branch_id'] ?? 0);
                    if (! $target || $target === $branch->id) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'target_branch_id' => 'Selecciona una sucursal de destino válida.',
                        ]);
                    }
                    $updated = User::whereIn('id', $ids)->update(['branch_id' => $target]);
                    break;
            }
        });

        return back()->with('success', "Operación '{$data['action']}' aplicada a {$updated} guardia(s).");
    }
}
