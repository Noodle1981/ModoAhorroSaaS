<x-app-layout>
    <div>
        <h1>Detalles del Equipo: {{ $equipment->custom_name ?? $equipment->equipmentType->name }}</h1>
        <p style="color: #666; font-size: 0.9em;">
            Ubicado en: <strong>{{ $equipment->location ?? 'N/A' }}</strong>
        </p>
        <p>
            <a href="{{ route('entities.show', $equipment->entity) }}">&larr; Volver al inventario de '{{ $equipment->entity->name }}'</a>
        </p>
    </div>

    <div style="margin-top: 20px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <h3>Información del Equipo</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <p><strong>Tipo de Equipo:</strong> {{ $equipment->equipmentType->name }}</p>
            <p><strong>Categoría:</strong> {{ $equipment->equipmentType->equipmentCategory->name }}</p>
            <p><strong>Cantidad:</strong> {{ $equipment->quantity }}</p>
            <p><strong>Potencia (si especificada):</strong> {{ $equipment->power_watts_override ? $equipment->power_watts_override . ' W' : 'Usando valor por defecto' }}</p>
            <p><strong>Uso diario (si especificado):</strong> {{ $equipment->avg_daily_use_hours_override ? $equipment->avg_daily_use_hours_override . ' hs' : 'Usando valor por defecto' }}</p>
        </div>
        <div style="margin-top: 20px;">
            <a href="{{ route('equipment.edit', $equipment) }}" style="background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                Editar Equipo
            </a>
        </div>
    </div>
</x-app-layout>