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
    public function index(\Illuminate\Http\Request $request)
    {
        $user    = $request->user();
        $isAdmin = $user && method_exists($user, 'hasRole') ? $user->hasRole('admin') : false;

        // 1) Validación segura (rango de fechas y filtros básicos)
        $data = $request->validate([
            'from'      => ['nullable', 'date'],
            'to'        => ['nullable', 'date', 'after_or_equal:from'],
            'branch_id' => ['nullable', 'integer'],
            'type'      => ['nullable', 'in:vehicle,pedestrian'],
            'status'    => ['nullable', 'in:inside,closed'],
            'q'         => ['nullable', 'string', 'max:120'],
            'page'      => ['nullable', 'integer'],
        ]);

        $from     = $data['from'] ?? null;
        $to       = $data['to'] ?? null;
        $branchId = $data['branch_id'] ?? null;
        $type     = $data['type'] ?? null;
        $status   = $data['status'] ?? null;
        $q        = trim($data['q'] ?? '');

        // 2) Query base con scopes típicos
        $qAccess = \App\Models\Access::query()
            ->with(['user', 'branch'])
            ->withCount(['people as inside_count' => fn($qq) => $qq->whereNull('exit_at')])
            ->when($from, fn($qq) => $qq->where('entry_at', '>=', \Illuminate\Support\Carbon::parse($from)->startOfDay()))
            ->when($to, fn($qq) => $qq->where('entry_at', '<=', \Illuminate\Support\Carbon::parse($to)->endOfDay()))
            ->when($type, fn($qq) => $qq->where('type', $type))
            ->when($status === 'inside', fn($qq) => $qq->whereNull('exit_at'))
            ->when($status === 'closed', fn($qq) => $qq->whereNotNull('exit_at'))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($x) use ($q) {
                    $x->where('full_name', 'like', "%{$q}%")
                        ->orWhere('document', 'like', "%{$q}%")
                        ->orWhere('plate', 'like', "%{$q}%");
                });
            });

        // Scope por sucursal (no admin ve solo su sucursal)
        if (! $isAdmin && $user) {
            $qAccess->where('branch_id', $user->branch_id);
        } elseif ($isAdmin && $branchId) {
            $qAccess->where('branch_id', $branchId);
        }

        // 3) KPIs (consultas baratas, aprovechan índices recién creados)
        $kpi = [
            'total'       => (clone $qAccess)->count('*'),
            'inside'      => (clone $qAccess)->whereNull('exit_at')->count('*'),
            'closed'      => (clone $qAccess)->whereNotNull('exit_at')->count('*'),
            'vehicles'    => (clone $qAccess)->where('type', 'vehicle')->count('*'),
            'pedestrians' => (clone $qAccess)->where('type', 'pedestrian')->count('*'),
        ];

        // 4) Listado paginado
        $accesses = $qAccess->latest('entry_at')->paginate(20)->withQueryString();

        // 5) Datos para filtros (solo admins)
        $branches = $isAdmin ? \App\Models\Branch::orderBy('name')->get() : collect();

        return view('reportes.index', [
            'accesses' => $accesses,
            'kpi'      => $kpi,
            'branches' => $branches,
            'filters'  => [
                'from'      => $from, 'to'     => $to,
                'branch_id' => $branchId,
                'type'      => $type, 'status' => $status, 'q' => $q,
            ],
            'isAdmin'  => $isAdmin,
        ]);
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
