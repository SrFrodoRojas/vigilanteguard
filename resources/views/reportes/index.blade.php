@extends('adminlte::page')
@section('title', 'Reportes')

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
    <x-adminlte-card theme="secondary" icon="fas fa-filter" title="Filtro por fechas">
        <form method="GET" class="mb-2">
            <div class="form-row">
                @if(auth()->user()->hasRole('admin'))
                    <div class="form-group col-12 col-md-3">
                        <label>Sucursal</label>
                        <select name="branch_id" class="form-control">
                            <option value="">Todas</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)request('branch_id') === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="form-group col-12 col-md-3">
                    <label>Desde</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="form-group col-12 col-md-3">
                    <label>Hasta</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="form-group col-12 col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary btn-block"><i class="fas fa-search"></i> Aplicar</button>
                </div>
            </div>
        </form>

        {{-- Presets rápidos --}}
        <div class="d-flex flex-wrap gap-2">
            @php
                $tz    = 'America/Asuncion';
                $hoy   = \Illuminate\Support\Carbon::now($tz)->toDateString();
                $ayer  = \Illuminate\Support\Carbon::now($tz)->subDay()->toDateString();
                $ini7  = \Illuminate\Support\Carbon::now($tz)->subDays(6)->toDateString();
                $iniMes= \Illuminate\Support\Carbon::now($tz)->startOfMonth()->toDateString();
                $finMes= \Illuminate\Support\Carbon::now($tz)->endOfMonth()->toDateString();
            @endphp
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from'=>$hoy, 'to'=>$hoy, 'branch_id'=>request('branch_id')])) }}">Hoy</a>
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from'=>$ayer, 'to'=>$ayer, 'branch_id'=>request('branch_id')])) }}">Ayer</a>
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from'=>$ini7, 'to'=>$hoy, 'branch_id'=>request('branch_id')])) }}">Últimos 7 días</a>
            <a class="btn btn-sm btn-outline-secondary mr-2 mb-2"
               href="{{ route('reports.index', array_filter(['from'=>$iniMes, 'to'=>$finMes, 'branch_id'=>request('branch_id')])) }}">Este mes</a>
        </div>
    </x-adminlte-card>

    <div class="row">
        <div class="col-12 col-md-3">
            <x-adminlte-small-box title="{{ $total }}" text="Movimientos" icon="fas fa-exchange-alt" theme="primary" />
        </div>
        <div class="col-12 col-md-3">
            <x-adminlte-small-box title="{{ $vehiculos }}" text="Vehículos" icon="fas fa-car-side" theme="info" />
        </div>
        <div class="col-12 col-md-3">
            <x-adminlte-small-box title="{{ $peatones }}" text="A pie" icon="fas fa-walking" theme="success" />
        </div>
        <div class="col-12 col-md-3">
            <x-adminlte-small-box title="{{ $promedioMin }} min" text="Tiempo promedio dentro" icon="fas fa-clock" theme="warning" />
        </div>
    </div>

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
                    @forelse($accesses as $a)
                        @php
                            $isVehicle = $a->type === 'vehicle';
                            $inside = (int) ($a->inside_count ?? 0);
                            $rowHref =
                                $inside > 0
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
                            <td>{{ $a->entry_at?->timezone('America/Asuncion')?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $a->exit_at?->timezone('America/Asuncion')?->format('d/m/Y H:i') ?? '—' }}</td>

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
                                    <a href="{{ $rowHref }}" class="btn btn-sm btn-success"
                                       onclick="event.stopPropagation();">
                                       <i class="fas fa-sign-out-alt"></i> Salida
                                    </a>
                                @else
                                    <a href="{{ route('access.show', $a) }}" class="btn btn-sm btn-outline-primary"
                                       onclick="event.stopPropagation();">
                                       <i class="fas fa-eye"></i> Ver
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10">Sin movimientos en el rango seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $accesses->appends(request()->query())->links('pagination::bootstrap-4') }}

        @push('css')
            <style>
                #report-table tr.row-link { cursor: pointer; }
            </style>
        @endpush
        @push('js')
            <script>
                document.querySelectorAll('#report-table tr.row-link').forEach(tr => {
                    tr.addEventListener('click', (e) => {
                        if (e.target.closest('a,button,input,label')) return;
                        const href = tr.dataset.href;
                        if (href) window.location.assign(href);
                    });
                });

                // Helpers export/compartir
                const qs = new URLSearchParams(window.location.search);
                const baseExcel = "{{ route('reports.export.excel') }}";
                const basePdf   = "{{ route('reports.export.pdf') }}";
                document.getElementById('btnExportExcel').href = baseExcel + (qs.toString() ? ('?'+qs.toString()) : '');
                document.getElementById('btnExportPdf').href   = basePdf   + (qs.toString() ? ('?'+qs.toString()) : '');

                // WhatsApp / Web Share / copiar enlace
                const shareUrl = window.location.href;
                const text = `Reporte de accesos (${qs.get('from') || 'inicio'} a ${qs.get('to') || 'hoy'})`;
                document.getElementById('btnShareWA').href = `https://wa.me/?text=${encodeURIComponent(text + ' ' + shareUrl)}`;

                const btnWebShare = document.getElementById('btnWebShare');
                btnWebShare.addEventListener('click', async () => {
                    if (navigator.share) {
                        try {
                            await navigator.share({ title: 'Reporte de accesos', text, url: shareUrl });
                        } catch {}
                    } else {
                        alert('La API de compartir no está disponible en este navegador.');
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
                    } catch {
                        alert('No se pudo copiar el enlace.');
                    }
                });
            </script>
        @endpush
    </x-adminlte-card>
@endsection
