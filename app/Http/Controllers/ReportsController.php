<?php
namespace App\Http\Controllers;

use App\Exports\AccessesExport;
use App\Models\Access;
use App\Models\Branch;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $tz      = 'America/Asuncion';
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        $request->validate([
            'from'      => ['nullable', 'date_format:Y-m-d'],
            'to'        => ['nullable', 'date_format:Y-m-d'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $from = $request->input('from');
        $to   = $request->input('to');

        $fromStart = $from ? Carbon::createFromFormat('Y-m-d', $from, $tz)->startOfDay()
        : Carbon::now($tz)->startOfDay();
        $toEnd = $to ? Carbon::createFromFormat('Y-m-d', $to, $tz)->endOfDay()
        : Carbon::now($tz)->endOfDay();

        if ($toEnd->lt($fromStart)) {
            return back()->withErrors(['to' => 'La fecha "Hasta" no puede ser menor que "Desde".'])->withInput();
        }

        $branchId = $request->input('branch_id');

        $query = Access::query()
            ->with(['user', 'branch'])
            ->withCount(['people as inside_count' => function ($q) {
                $q->whereNull('exit_at');
            }])
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where(function ($q) use ($fromStart, $toEnd) {
                $q->whereBetween('entry_at', [$fromStart, $toEnd])
                    ->orWhereBetween('exit_at', [$fromStart, $toEnd]);
            })
            ->orderByDesc('entry_at');

        $total     = (clone $query)->count();
        $vehiculos = (clone $query)->where('type', 'vehicle')->count();
        $peatones  = (clone $query)->where('type', 'pedestrian')->count();

        $promedioMin = (int) Access::whereNotNull('exit_at')
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('exit_at', [$fromStart, $toEnd])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, entry_at, exit_at)) as avgmin'))
            ->value('avgmin') ?? 0;

        $accesses = $query->paginate(20)->appends($request->only('from', 'to', 'branch_id'));

        // para filtros en la vista
        $branches = $isAdmin ? Branch::orderBy('name')->get(['id', 'name']) : collect();

        return view('reportes.index', compact(
            'from', 'to', 'branchId', 'branches',
            'accesses', 'total', 'vehiculos', 'peatones', 'promedioMin'
        ));
    }

    public function exportExcel(Request $request)
    {
        $tz      = 'America/Asuncion';
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        $request->validate([
            'from'      => ['nullable', 'date_format:Y-m-d'],
            'to'        => ['nullable', 'date_format:Y-m-d'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $fromStart = $request->filled('from')
        ? Carbon::createFromFormat('Y-m-d', $request->from, $tz)->startOfDay()
        : Carbon::now($tz)->startOfDay();

        $toEnd = $request->filled('to')
        ? Carbon::createFromFormat('Y-m-d', $request->to, $tz)->endOfDay()
        : Carbon::now($tz)->endOfDay();

        $branchId = $request->input('branch_id');

        $export = new AccessesExport($fromStart, $toEnd, $isAdmin, $user->branch_id, $branchId, $tz);

        $fileName = 'reporte_accesos_' . ($request->from ?? $fromStart->format('Y-m-d'))
            . '_' . ($request->to ?? $toEnd->format('Y-m-d')) . '.xlsx';

        return Excel::download($export, $fileName);
    }

    public function exportPdf(Request $request)
    {
        $tz      = 'America/Asuncion';
        $user    = auth()->user();
        $isAdmin = $user->hasRole('admin');

        $request->validate([
            'from'      => ['nullable', 'date_format:Y-m-d'],
            'to'        => ['nullable', 'date_format:Y-m-d'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        $fromStart = $request->filled('from')
        ? Carbon::createFromFormat('Y-m-d', $request->from, $tz)->startOfDay()
        : Carbon::now($tz)->startOfDay();

        $toEnd = $request->filled('to')
        ? Carbon::createFromFormat('Y-m-d', $request->to, $tz)->endOfDay()
        : Carbon::now($tz)->endOfDay();

        $branchId = $request->input('branch_id');
        $branch   = $branchId ? Branch::find($branchId) : null;

        // Totales para gráficos
        $base = Access::query()
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where(function ($q) use ($fromStart, $toEnd) {
                $q->whereBetween('entry_at', [$fromStart, $toEnd])
                    ->orWhereBetween('exit_at', [$fromStart, $toEnd]);
            });

        $vehiculos = (clone $base)->where('type', 'vehicle')->count();
        $peatones  = (clone $base)->where('type', 'pedestrian')->count();
        $total     = (clone $base)->count();

        $promedioMin = (int) Access::whereNotNull('exit_at')
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereBetween('exit_at', [$fromStart, $toEnd])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, entry_at, exit_at)) as avgmin'))
            ->value('avgmin') ?? 0;

        // Serie por día para el rango (máx 31 días recomendado para PDF)
        $days   = [];
        $counts = [];
        $cursor = $fromStart->copy();
        $map    = [];
        while ($cursor <= $toEnd) {
            $key       = $cursor->format('Y-m-d');
            $days[]    = $key;
            $map[$key] = 0;
            $cursor->addDay();
        }

        $byDay = (clone $base)->get(['entry_at']);
        foreach ($byDay as $a) {
            $k = $a->entry_at->timezone($tz)->format('Y-m-d');
            if (isset($map[$k])) {
                $map[$k]++;
            }

        }
        $counts = array_values($map);

        // Datos tabulares (limite defensivo)
        $rows = Access::query()
            ->with(['branch', 'user'])
            ->when(! $isAdmin, fn($q) => $q->where('branch_id', $user->branch_id))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where(function ($q) use ($fromStart, $toEnd) {
                $q->whereBetween('entry_at', [$fromStart, $toEnd])
                    ->orWhereBetween('exit_at', [$fromStart, $toEnd]);
            })
            ->orderBy('entry_at')
            ->limit(5000)
            ->get();

        // Gráficos server-side (QuickChart): URLs de imágenes
        // Doughnut
        $donutCfg = [
            'type'    => 'doughnut',
            'data'    => [
                'labels'   => ['Vehículo', 'A pie'],
                'datasets' => [[
                    'data'            => [$vehiculos, $peatones],
                    'backgroundColor' => ['#36A2EB', '#4BC0C0'],
                    'borderColor'     => ['#1e88e5', '#009688'],
                    'borderWidth'     => 1,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['position' => 'bottom']],
            ],
        ];
        $donutUrl = 'https://quickchart.io/chart?w=600&h=350&c=' . urlencode(json_encode($donutCfg));

        // Barras por día
        $barCfg = [
            'type'    => 'bar',
            'data'    => [
                'labels'   => array_map(fn($d) => Str::of($d)->after('-')->replace('-', '/'), $days),
                'datasets' => [[
                    'label'           => 'Entradas',
                    'data'            => $counts,
                    'backgroundColor' => 'rgba(99,102,241,0.7)',
                    'borderColor'     => 'rgba(99,102,241,1)',
                    'borderWidth'     => 1,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales'  => ['y' => ['beginAtZero' => true]],
            ],
        ];
        $barsUrl = 'https://quickchart.io/chart?w=900&h=350&c=' . urlencode(json_encode($barCfg));

        // Render PDF
        Pdf::setOptions(['isRemoteEnabled' => true]);
        $pdf = Pdf::loadView('reportes.pdf', [
            'tz'          => $tz,
            'fromStart'   => $fromStart,
            'toEnd'       => $toEnd,
            'branch'      => $branch,
            'total'       => $total,
            'vehiculos'   => $vehiculos,
            'peatones'    => $peatones,
            'promedioMin' => $promedioMin,
            'rows'        => $rows,
            'donutUrl'    => $donutUrl,
            'barsUrl'     => $barsUrl,
        ])->setPaper('a4', 'portrait');

        $fileName = 'reporte_accesos_' . $fromStart->format('Ymd') . '-' . $toEnd->format('Ymd') . '.pdf';
        return $pdf->download($fileName);
    }
}
