<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Accesos</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #222; }
        h1,h2,h3 { margin: 0 0 6px; }
        .muted { color: #666; }
        .kpis { display: flex; gap: 8px; margin: 10px 0 14px; }
        .kpi { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px; }
        .kpi .t { font-size: 11px; color: #666; }
        .kpi .v { font-size: 20px; font-weight: 700; }
        .row { display: flex; gap: 10px; }
        .col { flex: 1; }
        img { max-width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; }
        th { background: #f3f4f6; text-align: left; }
        .small { font-size: 11px; }
        .footer { margin-top: 10px; color: #888; font-size: 10px; text-align: right; }
    </style>
</head>
<body>
    <h2>Reporte de Accesos</h2>
    <div class="muted small">
        Rango:
        {{ $fromStart->timezone($tz)->format('d/m/Y H:i') }}
        — {{ $toEnd->timezone($tz)->format('d/m/Y H:i') }}
        @if($branch) · Sucursal: {{ $branch->name }} @endif
    </div>

    <div class="kpis">
        <div class="kpi"><div class="t">Movimientos</div><div class="v">{{ $total }}</div></div>
        <div class="kpi"><div class="t">Vehículos</div><div class="v">{{ $vehiculos }}</div></div>
        <div class="kpi"><div class="t">A pie</div><div class="v">{{ $peatones }}</div></div>
        <div class="kpi"><div class="t">Promedio dentro (min)</div><div class="v">{{ $promedioMin }}</div></div>
    </div>

    <div class="row">
        <div class="col">
            <h3>Distribución</h3>
            @if($donutUrl)
                <img src="{{ $donutUrl }}" alt="Distribución Vehículo vs A pie">
            @endif
        </div>
        <div class="col">
            <h3>Entradas por día</h3>
            @if($barsUrl)
                <img src="{{ $barsUrl }}" alt="Entradas por día">
            @endif
        </div>
    </div>

    <h3 style="margin-top:14px;">Detalle</h3>
    <table>
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
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $a)
                <tr>
                    <td>{{ $a->id }}</td>
                    <td>{{ $a->branch->name ?? '—' }}</td>
                    <td>{{ $a->type === 'vehicle' ? 'Vehículo' : 'A pie' }}</td>
                    <td>{{ $a->plate ?? '—' }}</td>
                    <td>{{ $a->full_name }}</td>
                    <td>{{ $a->document }}</td>
                    <td>{{ optional($a->entry_at)->timezone($tz)?->format('d/m/Y H:i') ?? '—' }}</td>
                    <td>{{ optional($a->exit_at)->timezone($tz)?->format('d/m/Y H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Sin datos en el rango.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now($tz)->format('d/m/Y H:i') }}
    </div>
</body>
</html>
