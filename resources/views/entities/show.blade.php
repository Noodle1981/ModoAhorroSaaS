<x-app-layout>
    <!-- ======================================================= -->
    <!-- CABECERA DE LA PÁGINA (Nombre de la entidad y botones)  -->
    <!-- ======================================================= -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1>{{ $entity->name }}</h1>
            <p><a href="{{ route('entities.index') }}">&larr; Volver a Mis Entidades</a></p>
        </div>
        <a href="{{ route('entities.edit', $entity) }}" style="background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            Editar Entidad y Habitaciones
        </a>
    </div>

    <!-- ======================================================= -->
    <!-- SECCIÓN DE CARACTERÍSTICAS DE LA VIVIENDA             -->
    <!-- ======================================================= -->
    <div style="margin-top: 20px; padding: 20px; border: 1px solid #eee; border-radius: 8px; background-color: #f9f9f9;">
        <h3>Características de la Entidad</h3>
        @if(empty($entity->details) || empty($entity->details['rooms']))
            <p>Aún no has completado las características de esta entidad. <a href="{{ route('entities.edit', $entity) }}">Complétalas ahora</a> para un análisis más preciso.</p>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <p><strong>Ocupantes:</strong> {{ $entity->details['occupants_count'] ?? 'N/A' }}</p>
                <p><strong>Habitaciones definidas:</strong> {{ count($entity->details['rooms']) }}</p>
            </div>
        @endif
    </div>

    <!-- ======================================================= -->
    <!-- SECCIÓN DE SUMINISTROS ENERGÉTICOS                       -->
    <!-- ======================================================= -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <!-- ... (código de la tabla de Suministros, que ya tenías bien) ... -->
    </div>

    <!-- ======================================================= -->
    <!-- SECCIÓN DE INVENTARIO DE EQUIPOS Y ANÁLISIS              -->
    <!-- ======================================================= -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Inventario y Análisis de Consumo</h2>
            <a href="{{ route('entities.equipment.create', $entity) }}" style="background-color: #fd7e14; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                + Añadir Equipo
            </a>
        </div>

        @if($inventoryReport->isEmpty())
            <p>Aún no has añadido ningún equipo a esta entidad. ¡Añade tus electrodomésticos para empezar el análisis!</p>
        @else
            <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Equipo / Ubicación</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Consumo Activo (kWh/año)</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Consumo Stand By (kWh/año)</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">TOTAL (kWh/año)</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Lógica de Agrupación por Ubicación -->
                    @foreach($inventoryReport->where('location', '!=', null)->groupBy('location') as $location => $equipments)
                        <tr style="background-color: #e9ecef;">
                            <td colspan="5" style="padding: 10px; font-weight: bold; border: 1px solid #ddd;">
                                Ubicación: {{ $location }}
                            </td>
                        </tr>
                        @foreach($equipments as $equipment)
                            @include('entities.partials.equipment-row', ['equipment' => $equipment])
                        @endforeach
                    @endforeach

                    <!-- Sección para Equipos Portátiles -->
                    @php
                        $portableEquipments = $inventoryReport->filter(fn($eq) => optional($eq->equipmentType)->is_portable);
                    @endphp
                    @if($portableEquipments->isNotEmpty())
                        <tr style="background-color: #e9ecef;">
                            <td colspan="5" style="padding: 10px; font-weight: bold; border: 1px solid #ddd;">
                                Equipos Portátiles
                            </td>
                        </tr>
                        @foreach($portableEquipments as $equipment)
                            @include('entities.partials.equipment-row', ['equipment' => $equipment])
                        @endforeach
                    @endif
                    
                    <!-- Fila del Total General -->
                    <tr style="background-color: #343a40; color: white; font-weight: bold;">
                        <td colspan="3" style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                            Consumo Total Estimado del Inventario:
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right; font-size: 1.2em;">
                            {{ number_format($inventoryReport->sum('energia_total_anual_kwh'), 2, ',', '.') }} kWh/año
                        </td>
                        <td style="border: 1px solid #ddd;"></td>
                    </tr>
                </tbody>
            </table>
        @endif
    </div>

    <!-- ======================================================= -->
    <!-- SECCIÓN DE ANÁLISIS Y MEJORAS (Botón de Acción)        -->
    <!-- ======================================================= -->
    <div style="margin-top: 30px; padding: 20px; background-color: #e3f2fd; border: 1px solid #b3e5fc; border-radius: 8px; text-align: center;">
        <h2>Análisis y Oportunidades</h2>
        <p style="margin-top: 10px; margin-bottom: 20px;">Analiza el consumo de esta entidad y descubre cómo puedes empezar a ahorrar.</p>
        <a href="{{ route('entities.reports.improvements', $entity) }}" style="background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-size: 1.1em;">
            Ver Informe de Mejoras
        </a>
    </div>
</x-app-layout>