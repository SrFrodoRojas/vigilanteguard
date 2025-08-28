{{-- resources/views/reportes/index.blade.php --}}
@extends('adminlte::page')
@section('title', 'Reportes')

@php
    $tz   = 'America/Asuncion';
    $from = data_get($filters ?? [], 'from', now($tz)->toDateString());
    $to   = data_get($filters ?? [], 'to',   now($tz)->toDateString());

    $isAdmin = isset($isAdmin) ? (bool)$isAdmin : (auth()->check() && method_exists(auth()->user(), 'hasRole') ? auth()->user()->hasRole('admin') : false);

    $hasKpi = isset($kpi) && is_array($kpi);
    if ($hasKpi) {
        $k_total       = (int) ($kpi['total']       ?? 0);
        $k_vehiculos   = (int) ($kpi['vehicles']    ?? 0);
        $k_peatones    = (int) ($kpi['pedestrians'] ?? 0);
        $k_promedioMin = (int) ($kpi['avg_min']     ?? 0);
    }
@endphp

@push('css')
<style>
    /* Cards mobile */
    @media (max-width: 767.98px) {
        .access-card .kv { font-size: .92rem; }
        .access-card .kv .k { color: #6c757d; min-width: 92px; display:inline-block; }
        .access-card .actions .btn { flex: 1 1 48%; }
    }
</style>
@endpush

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h1 class="mb-0">Reportes</h1>
            <small class="text-muted">Exportá a Excel/PDF y compartí el enlace</small>
        </div>

        <div class="mt-2 mt-md-0 d-flex flex-wrap">
            <div class="btn-group mr-2 mb-2" role="group" aria-label="Exportar">
                <a id="btnExportExcel" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a id="btnExportPdf" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>

            <div class="btn-group mb-2" role="group" aria-label="Compartir">
                <a id="btnShareWA" class="btn btn-sm btn-outline-success" title="Compartir por WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <button id="btnWebShare" class="btn btn-sm btn-outline-primary" title="Compartir">
                    <i class="fas fa-share-alt"></i>
                </button>
                <button id="btnCopyLink" class="btn btn-sm btn-outline-secondary" title="Copiar enlace">
                    <i class="fas fa-link"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')
    {{-- Mensajes --}}
    @if (session('ok'))
        <x-adminlte-alert theme="success" title="OK">{{ session('ok') }}</x-adminlte-alert>
    @endif
    @if (session('warning'))
        <x-adminlte-alert theme="warning" title="Atención">{{ session('warning') }}</x-adminlte-alert>
    @endif
    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Error">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <x-adminlte-card theme="secondary" icon="fas fa-filter" title="Filtro por fechas">
        <form method="GET" class="mb-2" id="filterForm">
            <div class="form-row">
                @if ($isAdmin)
                    <div class="form-group col-12 col-md-3">
                        <label>Sucursal</label>
                        <select name="branch_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach (($branches ?? collect()) as $b)
                                <option value="{{ $b->id }}"
                                    {{ (string) request('branch_id') === (string) $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="form-group col-12 col-md-3">
                    <label>Desde</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                    <div class="form-text">Por defecto: hoy</div>
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Hasta</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                    <div id="dateError" class="text-danger small d-none">La fecha fin no puede ser menor a la fecha inicio.</div>
                </div>
                <div class="form-group col-12 col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-block" id="btnApply"><i class="fas fa-search"></i> Aplicar</button>
                </div>
            </div>
        </form>

        {{-- Presets rápidos --}}
        @php
            $hoy    = \Illuminate\Support\Carbon::now($tz)->toDateString();
            $ayer   = \Illuminate\Support\Carbon::now($tz)->subDay()->toDateString();
            $ini7   = \Illuminate\Support\Carbon::now($tz)->subDays(6)->toDateString();
            $iniMes = \Illuminate\Support\Carbon::now($tz)->startOfMonth()->toDateString();
            $finMes = \Illuminate\Support\Carbon::now($tz)->endOfMonth()->toDateString();
        @endphp
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from' => $hoy, 'to' => $hoy, 'branch_id' => request('branch_id')])) }}">Hoy</a>
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from' => $ayer, 'to' => $ayer, 'branch_id' => request('branch_id')])) }}">Ayer</a>
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from' => $ini7, 'to' => $hoy, 'branch_id' => request('branch_id')])) }}">Últimos 7 días</a>
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from' => $iniMes, 'to' => $finMes, 'branch_id' => request('branch_id')])) }}">Este mes</a>
        </div>
    </x-adminlte-card>

    {{-- KPIs --}}
    @if ($hasKpi)
        <div class="row">
            <div class="col-12 col-md-3">
                <x-adminlte-small-box title="{{ number_format($k_total) }}" text="Movimientos" icon="fas fa-exchange-alt" theme="primary" />
            </div>
            <div class="col-12 col-md-3">
                <x-adminlte-small-box title="{{ number_format($k_vehiculos) }}" text="Vehículos" icon="fas fa-car-side" theme="info" />
            </div>
            <div class="col-12 col-md-3">
                <x-adminlte-small-box title="{{ number_format($k_peatones) }}" text="A pie" icon="fas fa-walking" theme="success" />
            </div>
            <div class="col-12 col-md-3">
                <x-adminlte-small-box title="{{ number_format($k_promedioMin) }} min" text="Tiempo promedio dentro" icon="fas fa-clock" theme="warning" />
            </div>
        </div>
    @else
        <x-adminlte-alert theme="warning" title="KPIs no disponibles">
            No recibimos métricas del controlador (<code>$kpi</code>). Prefiero no mostrar “0” para evitar reportes incorrectos.
        </x-adminlte-alert>
    @endif

    {{-- Desktop: tabla --}}
    <div class="d-none d-md-block">
        <x-adminlte-card theme="light" title="Detalle de movimientos" icon="fas fa-list">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle" id="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Sucursal</th>
                            <th>Tipo</th>
                            <th>Placa</th>
                            <th>Nombre</th>
                            <th>Documento</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Observación</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($accesses ?? collect()) as $a)
                            @php
                                $isVehicle = $a->type === 'vehicle';
                                $inside = (int) ($a->inside_count ?? 0);
                                $rowHref = $inside > 0
                                    ? ($isVehicle && $a->plate
                                        ? route('access.search', ['plate' => $a->plate])
                                        : route('access.search', ['document' => $a->document]))
                                    : route('access.show', $a);
                            @endphp
                            <tr class="row-link" data-href="{{ $rowHref }}">
                                <td>{{ $a->id }}</td>
                                <td>{{ $a->branch->name ?? '—' }}</td>
                                <td>{{ $isVehicle ? 'Vehículo' : 'A pie' }}</td>
                                <td>{{ $a->plate ?? '—' }}</td>
                                <td>{{ $a->full_name }}</td>
                                <td>{{ $a->document }}</td>
                                <td>{{ optional($a->entry_at)->timezone($tz)?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>{{ optional($a->exit_at)->timezone($tz)?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    @if ($a->exit_at && $a->exit_note)
                                        <span class="badge badge-secondary">{{ \Illuminate\Support\Str::limit($a->exit_note, 40) }}</span>
                                    @elseif($a->entry_note)
                                        <span class="badge badge-light">{{ \Illuminate\Support\Str::limit($a->entry_note, 40) }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-nowrap">
                                    @if ($inside > 0)
                                        <a href="{{ $rowHref }}" class="btn btn-sm btn-success" onclick="event.stopPropagation();">
                                            <i class="fas fa-sign-out-alt"></i> Salida
                                        </a>
                                    @else
                                        <a href="{{ route('access.show', $a) }}" class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation();">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">Sin movimientos en el rango seleccionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ ($accesses ?? collect())->appends(request()->query())->links('pagination::bootstrap-4') }}
        </x-adminlte-card>
    </div>

    {{-- Móvil: cards --}}
    <div class="d-block d-md-none">
        @forelse(($accesses ?? collect()) as $a)
            @php
                $isVehicle = $a->type === 'vehicle';
                $inside = (int) ($a->inside_count ?? 0);
                $rowHref = $inside > 0
                    ? ($isVehicle && $a->plate
                        ? route('access.search', ['plate' => $a->plate])
                        : route('access.search', ['document' => $a->document]))
                    : route('access.show', $a);
            @endphp
            <div class="card access-card shadow-sm mb-2" onclick="window.location='{{ $rowHref }}'">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-bold">{{ $a->branch->name ?? '—' }}</div>
                        <span class="badge {{ $isVehicle ? 'badge-info' : 'badge-success' }}">
                            {{ $isVehicle ? 'Vehículo' : 'A pie' }}
                        </span>
                    </div>
                    <div class="kv"><span class="k">#</span> <span class="v">{{ $a->id }}</span></div>
                    <div class="kv"><span class="k">Placa</span> <span class="v">{{ $a->plate ?? '—' }}</span></div>
                    <div class="kv"><span class="k">Nombre</span> <span class="v">{{ $a->full_name }}</span></div>
                    <div class="kv"><span class="k">Doc</span> <span class="v">{{ $a->document }}</span></div>
                    <div class="kv"><span class="k">Entrada</span> <span class="v">{{ optional($a->entry_at)->timezone($tz)?->format('d/m/Y H:i') ?? '—' }}</span></div>
                    <div class="kv"><span class="k">Salida</span> <span class="v">{{ optional($a->exit_at)->timezone($tz)?->format('d/m/Y H:i') ?? '—' }}</span></div>
                    @if ($a->exit_at && $a->exit_note)
                        <div class="kv"><span class="k">Obs</span> <span class="v">{{ \Illuminate\Support\Str::limit($a->exit_note, 60) }}</span></div>
                    @elseif($a->entry_note)
                        <div class="kv"><span class="k">Obs</span> <span class="v">{{ \Illuminate\Support\Str::limit($a->entry_note, 60) }}</span></div>
                    @endif

                    <div class="d-flex gap-2 mt-2 actions" onclick="event.stopPropagation();">
                        @if ($inside > 0)
                            <a href="{{ $rowHref }}" class="btn btn-sm btn-success">
                                <i class="fas fa-sign-out-alt"></i> Salida
                            </a>
                        @else
                            <a href="{{ route('access.show', $a) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-adminlte-alert theme="info" title="Sin datos">
                No hay movimientos en el rango seleccionado.
            </x-adminlte-alert>
        @endforelse

        {{ ($accesses ?? collect())->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
@endsection

@push('js')
<script>
    // Click fila (desktop)
    document.querySelectorAll('#report-table tr.row-link').forEach(tr => {
        tr.addEventListener('click', (e) => {
            if (e.target.closest('a,button,input,label')) return;
            const href = tr.dataset.href;
            if (href) window.location.assign(href);
        });
    });

    // Exports conservando filtros
    const qs = new URLSearchParams(window.location.search);
    const baseExcel = "{{ route('reports.export.excel') }}";
    const basePdf   = "{{ route('reports.export.pdf') }}";
    const qstr = qs.toString();
    document.getElementById('btnExportExcel').href = baseExcel + (qstr ? ('?' + qstr) : '');
    document.getElementById('btnExportPdf').href   = basePdf   + (qstr ? ('?' + qstr) : '');

    // Compartir (sin alerts)
    const shareUrl = window.location.href;
    const text = `Reporte de accesos (${qs.get('from') || 'hoy'} a ${qs.get('to') || 'hoy'})`;
    document.getElementById('btnShareWA').href = `https://wa.me/?text=${encodeURIComponent(text + ' ' + shareUrl)}`;

    const btnWebShare = document.getElementById('btnWebShare');
    btnWebShare.addEventListener('click', async () => {
        if (navigator.share) {
            try { await navigator.share({ title: 'Reporte de accesos', text, url: shareUrl }); } catch {}
        } else {
            // fallback: copiar enlace y marcar éxito
            try {
                await navigator.clipboard.writeText(shareUrl);
                btnWebShare.classList.remove('btn-outline-primary');
                btnWebShare.classList.add('btn-success');
                btnWebShare.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(() => {
                    btnWebShare.classList.remove('btn-success');
                    btnWebShare.classList.add('btn-outline-primary');
                    btnWebShare.innerHTML = '<i class="fas fa-share-alt"></i>';
                }, 1500);
            } catch {}
        }
    });

    const btnCopy = document.getElementById('btnCopyLink');
    btnCopy.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(shareUrl);
            btnCopy.classList.remove('btn-outline-secondary');
            btnCopy.classList.add('btn-success');
            btnCopy.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                btnCopy.classList.remove('btn-success');
                btnCopy.classList.add('btn-outline-secondary');
                btnCopy.innerHTML = '<i class="fas fa-link"></i>';
            }, 1500);
        } catch {}
    });

    // Validación en tiempo real: 'to' >= 'from' + UX
    const form = document.getElementById('filterForm');
    const fromInput = form.querySelector('input[name="from"]');
    const toInput   = form.querySelector('input[name="to"]');
    const applyBtn  = document.getElementById('btnApply');
    const errBox    = document.getElementById('dateError');

    function iso(d){ return d ? new Date(d + 'T00:00:00') : null; }
    function todayStr(){ return new Date().toISOString().slice(0,10); }

    function validateDates({autoFix=false} = {}) {
        if (!fromInput.value) fromInput.value = todayStr();
        if (!toInput.value)   toInput.value   = todayStr();

        const fd = iso(fromInput.value), td = iso(toInput.value);
        if (td < fd) {
            if (autoFix) {
                toInput.value = fromInput.value;
                applyBtn.disabled = false;
                errBox.classList.add('d-none');
                fromInput.classList.remove('is-invalid');
                toInput.classList.remove('is-invalid');
            } else {
                applyBtn.disabled = true;
                errBox.classList.remove('d-none');
                fromInput.classList.add('is-invalid');
                toInput.classList.add('is-invalid');
                return false;
            }
        } else {
            applyBtn.disabled = false;
            errBox.classList.add('d-none');
            fromInput.classList.remove('is-invalid');
            toInput.classList.remove('is-invalid');
        }
        toInput.min = fromInput.value;
        return true;
    }

    fromInput.addEventListener('change', () => validateDates({autoFix:true}));
    toInput.addEventListener('change',   () => validateDates());
    validateDates({autoFix:true});
</script>
@endpush
