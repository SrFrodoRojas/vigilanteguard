@extends('adminlte::page')
@section('title','Panel de Patrullas')

@push('css')
<style>
.kpi-card .kpi-number{ font-size:1.6rem; font-weight:700; }
@media (max-width: 576px){
  .table-responsive { font-size: .92rem; }
}
</style>
@endpush

@section('content_header')
  <h1>Panel de Patrullas</h1>
@endsection

@section('content')
  {{-- KPIs --}}
  <div class="row">
    <div class="col-6 col-md-3">
      <x-adminlte-small-box title="{{ $kpi['today_scans'] }}" text="Scans hoy" icon="fas fa-qrcode" theme="primary"/>
    </div>
    <div class="col-6 col-md-3">
      <x-adminlte-small-box title="{{ $kpi['today_verified'] }}" text="Verificados hoy" icon="fas fa-check-circle" theme="success"/>
    </div>
    <div class="col-6 col-md-3">
      <x-adminlte-small-box title="{{ $kpi['today_suspect'] }}" text="Sospechosos hoy" icon="fas fa-exclamation-triangle" theme="warning"/>
    </div>
    <div class="col-6 col-md-3">
      <x-adminlte-small-box title="{{ $kpi['active_assigns'] }}" text="Asignaciones activas" icon="fas fa-clipboard-check" theme="info"/>
    </div>
  </div>

  {{-- Gráfica + Top rutas --}}
  <div class="row">
    <div class="col-lg-8">
      <x-adminlte-card title="Scans últimos 7 días" theme="light" removable>
        <canvas id="scans7d" height="110"></canvas>
      </x-adminlte-card>
    </div>
    <div class="col-lg-4">
      <x-adminlte-card title="Top rutas por scans (7 días)" theme="light" removable>
        <ul class="list-group list-group-flush">
          @forelse($topRoutes as $r)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <span>{{ $r->name }}</span>
              <span class="badge bg-primary">{{ $r->scans_count }}</span>
            </li>
          @empty
            <li class="list-group-item text-muted">Sin datos</li>
          @endforelse
        </ul>
        <div class="mt-3 small text-muted">Tasa de finalización 7 días: <b>{{ $kpi['week_completion'] }}%</b></div>
      </x-adminlte-card>
    </div>
  </div>

  {{-- Próximas asignaciones --}}
  <x-adminlte-card title="Próximas 12 horas" theme="light" removable>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead><tr>
          <th>Hora</th><th>Guardia</th><th>Ruta</th><th>Sucursal</th><th>Estado</th>
        </tr></thead>
        <tbody>
        @forelse($upcoming as $a)
          <tr>
            <td>{{ $a->scheduled_start }}</td>
            <td>{{ $a->guardUser->name ?? '—' }}</td>
            <td>{{ $a->route->name ?? '—' }}</td>
            <td>{{ $a->route->branch->name ?? '—' }}</td>
            <td><span class="badge bg-secondary">{{ str_replace('_',' ',$a->status) }}</span></td>
          </tr>
        @empty
          <tr><td colspan="5" class="text-center text-muted">Sin asignaciones próximas.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </x-adminlte-card>

  {{-- Sospechosos recientes --}}
  <x-adminlte-card title="Sospechosos recientes" theme="light" collapsible>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead><tr>
          <th>Fecha</th><th>Guardia</th><th>Ruta</th><th>Punto</th><th>Vel (m/s)</th><th>Salto (m)</th><th>Motivo</th>
        </tr></thead>
        <tbody>
        @forelse($suspects as $s)
          <tr>
            <td>{{ $s->scanned_at }}</td>
            <td>{{ $s->assignment->guardUser->name ?? '—' }}</td>
            <td>{{ $s->checkpoint->route->name ?? '—' }}</td>
            <td>{{ $s->checkpoint->name ?? '—' }}</td>
            <td>{{ $s->speed_mps ?? '—' }}</td>
            <td>{{ $s->jump_m ?? '—' }}</td>
            <td><code>{{ $s->suspect_reason ?? '—' }}</code></td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted">Sin registros.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </x-adminlte-card>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
  const ctx = document.getElementById('scans7d');
  if (!ctx) return;

  const labels = @json($chart['labels']);
  const data   = @json($chart['series']);

  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Scans',
        data,
        tension: .25,
        borderWidth: 2,
        pointRadius: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: true } }
    }
  });
});
</script>
@endpush
