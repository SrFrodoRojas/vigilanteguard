<?php
namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{

    public function summary(Request $request)
    {
        $tz      = 'America/Asuncion';
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        // --- Filtros ---
        $from = $request->filled('from')
        ? Carbon::parse($request->input('from'), $tz)->startOfDay()
        : Carbon::now($tz)->startOfDay();

        $to = $request->filled('to')
        ? Carbon::parse($request->input('to'), $tz)->endOfDay()
        : Carbon::now($tz)->endOfDay();

        $branchId = $request->input('branch_id');

        // Base query por rango + sucursal según rol
        $base = Access::query()
            ->whereBetween('entry_at', [$from->clone()->timezone('UTC'), $to->clone()->timezone('UTC')]) // por si guardás en UTC
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        // --- KPIs ---
        $totalToday      = (clone $base)->count();
        $todayVehicle    = (clone $base)->where('type', 'vehicle')->count();
        $todayPedestrian = (clone $base)->where('type', 'pedestrian')->count();

        $activeBase = Access::query()
            ->whereNull('exit_at')
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

        $activeNow        = (clone $activeBase)->count();
        $activeVehicleNow = (clone $activeBase)->where('type', 'vehicle')->count();
        $activePedNow     = (clone $activeBase)->where('type', 'pedestrian')->count();

        // --- Entradas por hora (del rango; si rango > 1 día, se toma el primer día como referencia) ---
        $hourCounts = array_fill(0, 24, 0);
        $firstDay   = $from->clone()->startOfDay();
        $endFirst   = $from->clone()->endOfDay();

        $hourQuery = Access::query()
            ->whereBetween('entry_at', [$firstDay->clone()->timezone('UTC'), $endFirst->clone()->timezone('UTC')])
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get(['entry_at']);

        foreach ($hourQuery as $a) {
            $h              = $a->entry_at->clone()->timezone($tz)->hour;
            $hourCounts[$h] = ($hourCounts[$h] ?? 0) + 1;
        }
        $hours = range(0, 23);

        // --- Últimos 7 días (incluye hoy o el 'to' seleccionado) ---
        $end7   = $to->clone()->endOfDay();
        $start7 = $end7->clone()->subDays(6)->startOfDay();

        $last7 = Access::query()
            ->whereBetween('entry_at', [$start7->clone()->timezone('UTC'), $end7->clone()->timezone('UTC')])
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get(['entry_at']);

        // armar serie por día
        $daysLabels = [];
        $daysCounts = [];
        $cursor     = $start7->clone();
        $map        = [];
        while ($cursor <= $end7) {
            $label        = $cursor->format('Y-m-d');
            $daysLabels[] = $label;
            $map[$label]  = 0;
            $cursor->addDay();
        }
        foreach ($last7 as $a) {
            $label = $a->entry_at->clone()->timezone($tz)->format('Y-m-d');
            if (isset($map[$label])) {
                $map[$label]++;
            }

        }
        $daysCounts = array_values($map);

        // --- Top guardias (usuarios que registraron más entradas en el rango) ---
        $topGuards = Access::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->whereBetween('entry_at', [$from->clone()->timezone('UTC'), $to->clone()->timezone('UTC')])
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->with(['user:id,name,email'])
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // --- Resumen por sucursal (solo admin y sin branch filter) ---
        $branchesSummary = [];
        $branches        = collect();
        if ($isAdmin) {
            $branches = Branch::orderBy('name')->get(['id', 'name']);
            if (! $branchId) {
                $branchesSummary = Branch::withCount([
                    'accesses as total_today'      => function ($q) use ($from, $to) {
                        $q->whereBetween('entry_at', [$from->clone()->timezone('UTC'), $to->clone()->timezone('UTC')]);
                    },
                    'accesses as today_vehicle'    => function ($q) use ($from, $to) {
                        $q->whereBetween('entry_at', [$from->clone()->timezone('UTC'), $to->clone()->timezone('UTC')])
                            ->where('type', 'vehicle');
                    },
                    'accesses as today_pedestrian' => function ($q) use ($from, $to) {
                        $q->whereBetween('entry_at', [$from->clone()->timezone('UTC'), $to->clone()->timezone('UTC')])
                            ->where('type', 'pedestrian');
                    },
                    'accesses as active_now'       => function ($q) {
                        $q->whereNull('exit_at');
                    },
                ])->get(['id', 'name']);
            }
        }

        // Info auxiliar para UI
        $peakHourIndex = array_keys($hourCounts) ? array_search(max($hourCounts), $hourCounts) : 0;
        $peakHourLabel = str_pad((string) $peakHourIndex, 2, '0', STR_PAD_LEFT) . ':00';

        return view('dashboard.summary', compact(
            'branches', 'branchId', 'isAdmin',
            'from', 'to',
            'totalToday', 'activeNow', 'activeVehicleNow', 'activePedNow',
            'todayVehicle', 'todayPedestrian',
            'hours', 'hourCounts',
            'daysLabels', 'daysCounts',
            'topGuards',
            'branchesSummary',
            'peakHourLabel'
        ));
    }

}
