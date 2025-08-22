@extends('adminlte::page')
@section('title', 'Resumen')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Resumen</h1>
            <small class="text-muted">
                {{ $from->timezone('America/Asuncion')->format('d/m/Y') }}
                @if($from->isSameDay($to))
                    (hoy)
                @else
                    — {{ $to->timezone('America/Asuncion')->format('d/m/Y') }}
                @endif
            </small>
        </div>

        <div class="mt-2 mt-md-0">
            <form method="GET" class="form-inline">
                @if($isAdmin)
                    <select name="branch_id" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                        <option value="">Todas las sucursales</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ (string)request('branch_id') === (string)$b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <input type="date" name="from" class="form-control form-control-sm mr-1"
                       value="{{ request('from') ?: $from->format('Y-m-d') }}">
                <input type="date" name="to" class="form-control form-control-sm mr-2"
                       value="{{ request('to') ?: $to->format('Y-m-d') }}">

                <button class="btn btn-primary btn-sm mr-1">
                    <i class="fas fa-filter"></i> Aplicar
                </button>
                <a href="{{ route('dashboard.summary') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-sync"></i> Limpiar
                </a>
            </form>

            {{-- accesos directos de rango --}}
            <div class="mt-2 text-right">
                <a class="badge badge-light border mr-1" href="{{ route('dashboard.summary', ['from'=>now('America/Asuncion')->format('Y-m-d'),'to'=>now('America/Asuncion')->format('Y-m-d'),'branch_id'=>request('branch_id')]) }}">Hoy</a>
                <a class="badge badge-light border mr-1" href="{{ route('dashboard.summary', ['from'=>now('America/Asuncion')->subDay()->format('Y-m-d'),'to'=>now('America/Asuncion')->subDay()->format('Y-m-d'),'branch_id'=>request('branch_id')]) }}">Ayer</a>
                <a class="badge badge-light border" href="{{ route('dashboard.summary', ['from'=>now('America/Asuncion')->startOfWeek()->format('Y-m-d'),'to'=>now('America/Asuncion')->endOfWeek()->format('Y-m-d'),'branch_id'=>request('branch_id')]) }}">Esta semana</a>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- PWA install --}}
    <div id="pwaInstallWrap" class="mb-3 d-none">
        <button id="btnPwaInstall" class="btn btn-sm btn-dark">
            <i class="fas fa-download"></i> Instalar app
        </button>
    </div>

    {{-- KPIs --}}
    <div class="row">
        <div class="col-12 col-sm-6 col-lg-3">
            <x-adminlte-small-box title="{{ $totalToday }}" text="Entradas" icon="fas fa-calendar-day" theme="primary"/>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <x-adminlte-small-box title="{{ $activeNow }}" text="Activos ahora" icon="fas fa-user-check" theme="warning"/>
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box title="{{ $activeVehicleNow }}" text="Activos: Vehículos" icon="fas fa-car" theme="info"/>
        </div>
        <div class="col-6 col-lg-3">
            <x-adminlte-small-box title="{{ $activePedNow }}" text="Activos: A pie" icon="fas fa-walking" theme="success"/>
        </div>
    </div>

    <div class="row">
        {{-- DONUT --}}
        <div class="col-12 col-lg-6">
            <x-adminlte-card title="Vehículo vs. A pie (rango)" theme="lightblue" icon="fas fa-chart-pie">
                <div id="donut-wrap" class="position-relative" style="height:280px; min-height:280px;">
                    <canvas id="chartDonut"></canvas>
                    <div id="donut-fallback" class="d-none p-3">
                        <p class="mb-1"><strong>Vehículo:</strong> {{ (int) $todayVehicle }}</p>
                        <p class="mb-0"><strong>A pie:</strong> {{ (int) $todayPedestrian }}</p>
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    <i class="fas fa-bolt"></i> Hora pico: {{ $peakHourLabel }}
                </div>
                <div class="mt-3 d-flex flex-wrap gap-2">
                    <a href="{{ route('access.create') }}" class="btn btn-sm btn-primary mb-2">
                        <i class="fas fa-sign-in-alt"></i> Registrar entrada
                    </a>
                    <a href="{{ route('access.active') }}" class="btn btn-sm btn-outline-warning mb-2">
                        <i class="fas fa-user-check"></i> Ver activos
                    </a>
                    <a href="{{ route('access.exit.form') }}" class="btn btn-sm btn-outline-secondary mb-2">
                        <i class="fas fa-sign-out-alt"></i> Registrar salida
                    </a>
                    @can('reports.view')
                        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-info mb-2">
                            <i class="fas fa-chart-bar"></i> Reportes
                        </a>
                    @endcan
                </div>
            </x-adminlte-card>
        </div>

        {{-- BARRAS (primer día del rango) --}}
        <div class="col-12 col-lg-6">
            <x-adminlte-card title="Entradas por hora (primer día del rango)" theme="teal" icon="fas fa-chart-line">
                <div class="position-relative" style="height:280px; min-height:280px;">
                    <canvas id="chartBars"></canvas>
                </div>
                @if($isAdmin)
                    <div class="mt-2 small text-muted">
                        <i class="fas fa-info-circle"></i>
                        Datos para {{ $branchId ? 'sucursal seleccionada' : 'todas las sucursales' }}
                    </div>
                @endif
            </x-adminlte-card>
        </div>
    </div>

    {{-- Últimos 7 días --}}
    <div class="row">
        <div class="col-12">
            <x-adminlte-card title="Entradas últimos 7 días" theme="indigo" icon="fas fa-calendar-week">
                <div class="position-relative" style="height:300px; min-height:300px;">
                    <canvas id="chart7d"></canvas>
                </div>
            </x-adminlte-card>
        </div>
    </div>

    {{-- Top guardias --}}
    <div class="row">
        <div class="col-12 col-lg-6">
            <x-adminlte-card title="Top guardias (por entradas)" theme="orange" icon="fas fa-user-shield">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Guardia</th>
                                <th class="text-right">Entradas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topGuards as $g)
                                <tr>
                                    <td>{{ $g->user?->name ?? '—' }}</td>
                                    <td class="text-right">{{ $g->total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2">Sin datos en el rango.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>

        {{-- Resumen por sucursal (solo admin, sin filtro de sucursal) --}}
        @if($isAdmin && empty($branchId))
        <div class="col-12 col-lg-6">
            <x-adminlte-card title="Resumen por Sucursal" theme="purple" icon="fas fa-building">
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th class="text-right">Entradas</th>
                                <th class="text-right">Activos</th>
                                <th class="text-right">Vehículos</th>
                                <th class="text-right">A pie</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchesSummary as $branch)
                                <tr>
                                    <td>{{ $branch->name }}</td>
                                    <td class="text-right">{{ $branch->total_today }}</td>
                                    <td class="text-right">{{ $branch->active_now }}</td>
                                    <td class="text-right">{{ $branch->today_vehicle }}</td>
                                    <td class="text-right">{{ $branch->today_pedestrian }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.summary', ['branch_id' => $branch->id, 'from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
                                           class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-chart-line"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">Sin datos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-adminlte-card>
        </div>
        @endif
    </div>
@endsection

@push('css')
<style>
    /* Compacto en móvil */
    @media (max-width: 767.98px) {
        .btn { padding: .35rem .6rem; font-size: .9rem; }
        .form-control, .form-control-sm { font-size: .9rem; }
        .card .table { font-size: .9rem; }
    }
</style>
@endpush

@push('js')
<script>
(() => {
    // Evitar multi-init
    if (window.__vigilanteDash) return; window.__vigilanteDash = true;

    const donutData = [ {{ (int)$todayVehicle }}, {{ (int)$todayPedestrian }} ];
    const hours     = @json($hours);
    const hourCounts= @json($hourCounts);
    const daysLbl   = @json($daysLabels);
    const daysCnt   = @json($daysCounts);

    const getCtx = id => {
        const el = document.getElementById(id);
        if (!el) return null;
        const wrap = el.parentElement;
        if (wrap && wrap.clientHeight > 0) el.height = wrap.clientHeight;
        return el.getContext('2d');
    };

    const showDonutFallback = () => {
        const cnv = document.getElementById('chartDonut');
        const fb  = document.getElementById('donut-fallback');
        if (cnv) cnv.classList.add('d-none');
        if (fb) fb.classList.remove('d-none');
    };

    let tries = 0;
    function boot() {
        if (!window.Chart) {
            if (++tries > 40) { showDonutFallback(); return; } // ~2s
            return void setTimeout(boot, 50);
        }

        try {
            // Donut
            const dctx = getCtx('chartDonut');
            if (dctx) {
                new Chart(dctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Vehículo','A pie'],
                        datasets: [{
                            data: donutData,
                            backgroundColor: ['rgba(54,162,235,.7)','rgba(75,192,192,.7)'],
                            borderColor: ['rgba(54,162,235,1)','rgba(75,192,192,1)'],
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio:false, animation:false, plugins:{ legend:{position:'bottom'} } }
                });
            } else {
                showDonutFallback();
            }

            // Barras por hora
            const bctx = getCtx('chartBars');
            if (bctx) {
                new Chart(bctx, {
                    type: 'bar',
                    data: {
                        labels: hours.map(h => String(h).padStart(2,'0')+':00'),
                        datasets: [{
                            label: 'Entradas',
                            data: hourCounts,
                            backgroundColor: 'rgba(0,128,128,.7)',
                            borderColor: 'rgba(0,128,128,1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false, animation:false,
                        scales: { y: { beginAtZero:true, ticks:{ stepSize:1, precision:0 } } },
                        plugins: { legend:{ display:false } }
                    }
                });
            }

            // Línea últimos 7 días
            const lctx = getCtx('chart7d');
            if (lctx) {
                new Chart(lctx, {
                    type: 'line',
                    data: {
                        labels: daysLbl.map(d => d.slice(5).replace('-', '/')),
                        datasets: [{
                            label: 'Entradas',
                            data: daysCnt,
                            fill: false,
                            tension: .2,
                            borderWidth: 2,
                            borderColor: 'rgba(99,102,241,1)', // indigo
                            backgroundColor: 'rgba(99,102,241,.6)'
                        }]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false, animation:false,
                        scales: { y: { beginAtZero:true, ticks:{ stepSize:1, precision:0 } } },
                        plugins: { legend:{ display:false } }
                    }
                });
            }
        } catch(e) {
            console.error(e); showDonutFallback();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once:true });
    } else { boot(); }
})();
</script>

{{-- Botón instalar PWA (igual al tuyo, resumido) --}}
<script>
(function() {
    const wrap = document.getElementById('pwaInstallWrap');
    const btn  = document.getElementById('btnPwaInstall');
    let deferredPrompt = null;

    function isInstalled() {
        return window.matchMedia('(display-mode: standalone)').matches ||
               window.navigator.standalone === true ||
               document.referrer.startsWith('android-app://');
    }
    function show(){ if (wrap && !isInstalled()) wrap.classList.remove('d-none'); }
    function hide(){ if (wrap) wrap.classList.add('d-none'); }

    window.addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); deferredPrompt = e; show(); });
    if (btn) btn.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        deferredPrompt = null; if (outcome === 'accepted') hide();
    });
    window.addEventListener('appinstalled', hide);
    if (isInstalled()) hide();

    // iOS Safari helper
    const isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    if (isIOS && isSafari && !isInstalled()) {
        show(); if (btn) btn.textContent = 'Agregar a pantalla de inicio';
    }
})();
</script>
@endpush
