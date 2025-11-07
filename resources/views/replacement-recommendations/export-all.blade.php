<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Recomendaciones Reemplazo</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color:#222; margin:0; padding:28px; }
        h1 { font-size:22px; margin-bottom:4px; }
        h2 { font-size:16px; margin-top:24px; border-bottom:1px solid #e1e1e1; padding-bottom:4px; }
        table { width:100%; border-collapse: collapse; margin-top:8px; }
        th, td { border:1px solid #ddd; padding:5px 6px; text-align:left; }
        th { background:#f7f7f7; }
        .summary-grid { display:grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap:12px; }
        .card { border:1px solid #ddd; border-radius:6px; padding:10px; background:#fafafa; }
        .muted { color:#666; font-size:10px; }
        .badge { display:inline-block; padding:2px 6px; font-size:10px; border-radius:4px; }
        .pending { background:#ffe9b3; }
        .accepted { background:#cfe3ff; }
        .in_recovery { background:#e7d4ff; }
        .good { background:#d2f8d2; }
    </style>
</head>
<body>
    <h1>Reporte Global de Recomendaciones de Reemplazo</h1>
    <p class="muted">Generado: {{ $generated_at->format('d/m/Y H:i') }} · Total recomendaciones: {{ $recommendations->count() }}</p>

    <h2>Resumen Financiero</h2>
    <div class="summary-grid">
        <div class="card">
            <strong>Ahorro anual total</strong><br>
            ${{ number_format($total_annual_savings,0) }}
        </div>
        <div class="card">
            <strong>Inversión total</strong><br>
            ${{ number_format($total_investment,0) }}
        </div>
        <div class="card">
            <strong>ROI promedio (meses)</strong><br>
            @php $avgRoi = $recommendations->where('roi_months','>',0)->avg('roi_months'); @endphp
            {{ $avgRoi ? round($avgRoi,1) : '—' }}
        </div>
        <div class="card">
            <strong>Recomendaciones con ROI &lt;= 24m</strong><br>
            {{ $recommendations->where('roi_months','<=',24)->count() }}
        </div>
    </div>

    <h2>Listado Detallado</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Entidad</th>
                <th>Equipo Actual</th>
                <th>Equipo Recomendado</th>
                <th>Consumo Actual kWh/año</th>
                <th>Consumo Recomendado kWh/año</th>
                <th>Ahorro kWh/año</th>
                <th>Ahorro $/año</th>
                <th>Inversión $</th>
                <th>ROI meses</th>
                <th>Ahorro %</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recommendations as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->entityEquipment?->entity?->name ?? '—' }}</td>
                <td>{{ $r->current_equipment_name }}</td>
                <td>{{ $r->recommended_equipment_name }}</td>
                <td>{{ number_format($r->current_annual_kwh,0) }}</td>
                <td>{{ number_format($r->recommended_annual_kwh,0) }}</td>
                <td>{{ number_format($r->kwh_saved_per_year,0) }}</td>
                <td>${{ number_format($r->money_saved_per_year,0) }}</td>
                <td>${{ number_format($r->investment_required,0) }}</td>
                <td>{{ $r->roi_months }}</td>
                <td>{{ $r->savings_percentage }}%</td>
                <td>
                    <span class="badge {{ $r->status }}">{{ $r->status }}</span>
                    @if($r->roi_months && $r->roi_months <= 24)
                        <span class="badge good">ROI &lt;=24m</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="muted" style="margin-top:24px">Documento generado automáticamente por el módulo de optimización energética.</p>
</body>
</html>