<!-- Fila de la tabla para un solo equipo -->
<tr>
    <td style="padding: 12px; border: 1px solid #ddd; padding-left: 25px;">
        <strong style="display: block;">{{ $equipment->custom_name ?? $equipment->equipmentType->name }}</strong>
        <small style="color: #666;">(x{{ $equipment->quantity }}) - {{ $equipment->equipmentType->name }}</small>
    </td>
    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
        {{ number_format($equipment->energia_secundaria_kwh, 2, ',', '.') }}
    </td>
    <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
        {{ number_format($equipment->standby_kwh, 2, ',', '.') }}
    </td>
    <td style="padding: 12px; border: 1px solid #ddd; text-align: right; font-weight: bold;">
        {{ number_format($equipment->energia_total_anual_kwh, 2, ',', '.') }}
    </td>
    <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
        <a href="{{ route('equipment.edit', $equipment) }}">Editar</a>
        <!-- Aquí podría ir el form de eliminar si lo quieres -->
    </td>
</tr>