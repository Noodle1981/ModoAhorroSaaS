<x-app-layout>
    <!-- ... (la parte de arriba con los detalles de la entidad y la lista de suministros queda igual) ... -->

    <!-- Sección de Inventario de Equipos (¡AHORA CON AGRUPACIÓN!) -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Inventario de Equipos</h2>
            <a href="{{ route('entities.equipment.create', $entity) }}" style="background-color: #fd7e14; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                + Añadir Equipo
            </a>
        </div>

        @if($entity->entityEquipment->isEmpty())
            <p>Aún no has añadido ningún equipo a esta entidad.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Nombre / Tipo de Equipo</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Cantidad</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- =========== LÓGICA DE AGRUPACIÓN =========== -->
                    @foreach($entity->entityEquipment->groupBy('location') as $location => $equipments)
                        <tr style="background-color: #e9ecef;">
                            <td colspan="3" style="padding: 10px; font-weight: bold; border: 1px solid #ddd;">
                                Ubicación: {{ $location ?: 'Sin Ubicación Asignada' }}
                            </td>
                        </tr>
                        @foreach($equipments as $equipment)
                            <tr>
                                <td style="padding: 12px; border: 1px solid #ddd; padding-left: 25px;">
                                    <strong style="display: block;">{{ $equipment->custom_name ?? $equipment->equipmentType->name }}</strong>
                                    <small style="color: #666;">{{ $equipment->equipmentType->name }}</small>
                                </td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">{{ $equipment->quantity }}</td>
                                <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                    <a href="{{ route('equipment.edit', $equipment) }}">Editar</a> |
                                    <form action="{{ route('equipment.destroy', $equipment) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="color: red; background: none; border: none; cursor: pointer; padding: 0; font-size: inherit; text-decoration: underline;">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    <!-- ============================================ -->
                </tbody>
            </table>

<!-- Añade esto en entities/show.blade.php -->
<div style="margin-top: 20px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
    <h3>Características de la Vivienda</h3>
    
    @if(empty($entity->details))
        <p>Aún no has completado las características de esta vivienda. <a href="{{ route('entities.edit', $entity) }}">Complétalas ahora</a> para obtener un análisis más preciso.</p>
    @else
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <p><strong>Tipo Propiedad:</strong> {{ ucfirst($entity->details['property_type'] ?? 'No especificado') }}</p>
            <p><strong>Ocupantes:</strong> {{ $entity->details['occupants_count'] ?? 'N/A' }}</p>
            <p><strong>Habitaciones:</strong> {{ $entity->details['bedrooms_count'] ?? 'N/A' }}</p>
            <p><strong>Baños:</strong> {{ $entity->details['bathrooms_count'] ?? 'N/A' }}</p>
        </div>
        @if(!empty($entity->details['shared_spaces_description']))
            <p style="margin-top: 15px;"><strong>Espacios Compartidos:</strong> {{ $entity->details['shared_spaces_description'] }}</p>
        @endif
        @if(!empty($entity->details['mixed_use_description']))
            <p style="margin-top: 15px;"><strong>Uso Mixto (Negocio/Taller):</strong> {{ $entity->details['mixed_use_description'] }}</p>
        @endif
    @endif
</div>

        @endif
    </div>
</x-app-layout>