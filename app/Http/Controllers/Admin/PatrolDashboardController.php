<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CheckpointScan;
use App\Models\PatrolAssignment;
use App\Models\PatrolRoute;
use Carbon\Carbon;

class PatrolDashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart  = $now->copy()->subDays(6)->startOfDay(); // últimos 7 días

        // KPIs hoy
        $kpiTodayScans     = CheckpointScan::where('scanned_at', '>=', $todayStart)->count();
        $kpiTodayVerified  = CheckpointScan::where('scanned_at', '>=', $todayStart)->where('verified', true)->count();
        $kpiTodaySuspect   = CheckpointScan::where('scanned_at', '>=', $todayStart)->where('suspect', true)->count();
        $kpiActiveAssigns  = PatrolAssignment::whereIn('status', ['scheduled','in_progress'])->count();

        // Asignaciones últimos 7 días (para tasa de completion)
        $weekAssignmentsTotal = PatrolAssignment::where('scheduled_start', '>=', $weekStart)->count();
        $weekAssignmentsDone  = PatrolAssignment::where('scheduled_start', '>=', $weekStart)->where('status', 'completed')->count();
        $weekCompletionRate   = $weekAssignmentsTotal ? round($weekAssignmentsDone * 100 / $weekAssignmentsTotal) : 0;

        // Scans por día (últimos 7 días)
        $scansByDay = CheckpointScan::where('scanned_at', '>=', $weekStart)
            ->orderBy('scanned_at')
            ->get()
            ->groupBy(fn($s) => Carbon::parse($s->scanned_at)->format('Y-m-d'))
            ->map(fn($grp) => $grp->count())
            ->all();

        // Armar series completas con 7 días
        $labels = [];
        $series = [];
        for ($i=6; $i>=0; $i--) {
            $d = $now->copy()->subDays($i)->format('Y-m-d');
            $labels[] = $d;
            $series[] = $scansByDay[$d] ?? 0;
        }

        // Top rutas por scans (últimos 7 días)
        $topRoutes = PatrolRoute::query()
            ->withCount(['checkpoints as scans_count' => function($q) use ($weekStart) {
                $q->join('checkpoint_scans', 'checkpoint_scans.checkpoint_id', '=', 'checkpoints.id')
                  ->where('checkpoint_scans.scanned_at', '>=', $weekStart);
            }])
            ->orderByDesc('scans_count')
            ->limit(5)
            ->get(['id','name']);

        // Próximas asignaciones (12h)
        $upcoming = PatrolAssignment::with(['guardUser','route.branch'])
            ->where('scheduled_start', '>=', $now)
            ->where('scheduled_start', '<=', $now->copy()->addHours(12))
            ->orderBy('scheduled_start')
            ->limit(10)
            ->get();

        // Sospechosos recientes (10)
        $suspects = CheckpointScan::with(['assignment.guardUser','checkpoint.route'])
            ->where('suspect', true)
            ->orderByDesc('scanned_at')
            ->limit(10)
            ->get();

        return view('admin.patrol.dashboard', [
            'kpi' => [
                'today_scans'    => $kpiTodayScans,
                'today_verified' => $kpiTodayVerified,
                'today_suspect'  => $kpiTodaySuspect,
                'active_assigns' => $kpiActiveAssigns,
                'week_completion'=> $weekCompletionRate,
            ],
            'chart' => [
                'labels' => $labels,
                'series' => $series,
            ],
            'topRoutes' => $topRoutes,
            'upcoming'  => $upcoming,
            'suspects'  => $suspects,
        ]);
    }
}
