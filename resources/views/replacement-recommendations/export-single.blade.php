<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recomendación #{{ $recommendation->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color:#222; margin:0; padding:24px; }
        h1 { font-size:20px; margin-bottom:4px; }
        h2 { font-size:16px; margin-top:24px; border-bottom:1px solid #eee; padding-bottom:4px; }
        table { width:100%; border-collapse: collapse; margin-top:8px; }
        th, td { border:1px solid #ddd; padding:6px 8px; text-align:left; }
        th { background:#f5f5f5; }
        .tag { display:inline-block; padding:4px 8px; background:#4caf50; color:#fff; font-size:11px; border-radius:4px; }
        .muted { color:#666; font-size:11px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .box { border:1px solid #ddd; border-radius:6px; padding:12px; }
        .small { font-size:11px; }
        .center { text-align:center; }
    </style>
</head>
<body>
    <h1>Recomendación de Reemplazo #{{ $recommendation->id }}</h1>
    <p class="muted">Generado: {{ now()->format('d/m/Y H:i') }}</p>

    <h2>Resumen</h2>
    <div class="grid">
        <div class="box">
            <strong>Equipo Actual</strong><br>
            {{ $recommendation->current_equipment_name }}<br>
            Potencia: {{ $recommendation->current_power_watts }} W<br>
            Consumo anual estimado: {{ number_format($recommendation->current_annual_kwh,0) }} kWh
        </div>
        <div class="box">
            <strong>Equipo Recomendado</strong><br>
            {{ $recommendation->recommended_equipment_name }}<br>
            Potencia: {{ $recommendation->recommended_power_watts }} W<br>
            Etiqueta: {{ $recommendation->recommended_energy_label }}<br>
            Consumo anual estimado: {{ number_format($recommendation->recommended_annual_kwh,0) }} kWh
        </div>
    </div>

    <h2>Métricas Económicas</h2>
    <table>
        <tr>
            <th>Ahorro anual (kWh)</th>
            <td>{{ number_format($recommendation->kwh_saved_per_year,0) }}</td>
        </tr>
        <tr>
            <th>Ahorro anual ($)</th>
            <td>${{ number_format($recommendation->money_saved_per_year,0) }}</td>
        </tr>
        <tr>
            <th>Inversión requerida</th>
            <td>${{ number_format($recommendation->investment_required,0) }}</td>
        </tr>
        <tr>
            <th>ROI meses</th>
            <td>{{ $recommendation->roi_months }}</td>
        </tr>
        <tr>
            <th>Porcentaje ahorro</th>
            <td>{{ $recommendation->savings_percentage }}%</td>
        </tr>
    </table>

    <h2>Datos Adicionales</h2>
    <table>
        <tr>
            <th>Entidad</th>
            <td>{{ $recommendation->entityEquipment?->entity?->name ?? '—' }}</td>
        </tr>
        <tr>
            <th>Estado</th>
            <td>{{ $recommendation->status }}</td>
        </tr>
        <tr>
            <th>Fecha creación</th>
            <td>{{ $recommendation->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>URL compra</th>
            <td>
                @if($recommendation->marketEquipment?->purchase_link)
                    <a href="{{ $recommendation->marketEquipment->purchase_link }}">{{ $recommendation->marketEquipment->purchase_link }}</a>
                @else
                    —
                @endif
            </td>
        </tr>
    </table>

    <p class="small">Documento generado automáticamente por el módulo de optimización energética.</p>
</body>
</html>