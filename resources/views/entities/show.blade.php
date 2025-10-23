<x-app-layout>

    <!-- ======================================================= -->
    <!-- ENCABEZADO Y ACCIONES RÁPIDAS                         -->
    <!-- ======================================================= -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
        <div>
            <h1 style="font-size: 2em; font-weight: bold;">Dashboard: {{ $entity->name }}</h1>
            <a href="{{ route('entities.index') }}" style="text-decoration: none; color: #007bff;">&larr; Volver a la lista de entidades</a>
        </div>
        <div style="margin-top: 10px;">
            <a href="{{ route('entities.edit', $entity) }}" style="background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-size: 0.9em;">
                Editar Entidad
            </a>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- ACCIONES PRINCIPALES                                    -->
    <!-- ======================================================= -->
    <div style="margin-top: 20px; margin-bottom: 30px; display: flex; gap: 15px; flex-wrap: wrap;">
        <a href="{{ route('entities.equipment.create', $entity) }}" style="background-color: #007bff; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            + Añadir Equipo al Inventario
        </a>
        <a href="{{ route('entities.supplies.index', $entity) }}" style="background-color: #17a2b8; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Gestionar Suministros
        </a>
        {{-- El enlace para cargar factura se muestra en la sección de análisis si es necesario --}}
    </div>


    <!-- ======================================================= -->
    <!-- ANÁLISIS DEL PERÍODO ACTIVO (LÓGICA EXISTENTE)          -->
    <!-- ======================================================= -->
    <div style="margin-bottom: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px; background-color: #f9f9f9;">
        <h3 style="font-size: 1.5em; font-weight: bold; margin-bottom: 15px;">Análisis del Período Activo</h3>
        
        @if($periodSummary->real_consumption === null)
            @php
                $firstSupply = $entity->supplies->first();
                $firstContract = $firstSupply?->contracts->where('is_active', true)->first() ?? $firstSupply?->contracts->first();
            @endphp
            <div style="text-align: center; padding: 20px; background-color: #fff3cd; border-radius: 8px;">
                <p>Aún no has cargado ninguna factura para analizar.</p>
                @if ($firstContract)
                    <a href="{{ route('contracts.invoices.create', $firstContract) }}" style="display: inline-block; margin-top: 10px; background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                        + Carga tu primera factura
                    </a>
                @elseif ($firstSupply)
                    <p style="margin-top: 10px;">Para cargar una factura, primero debes <a href="{{ route('supplies.contracts.create', $firstSupply) }}" style="color: #007bff; font-weight: bold;">crear un contrato para tu suministro</a>.</p>
                @else
                    <p style="margin-top: 10px;">Para empezar, <a href="{{ route('entities.supplies.create', $entity) }}" style="color: #007bff; font-weight: bold;">añade un punto de suministro</a>.</p>
                @endif
            </div>
        @else
            <p style="text-align: center; font-weight: bold; margin-bottom: 20px;">
                Período analizado: {{ $periodSummary->period_label }} ({{ $periodSummary->period_days }} días)
            </p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 15px; background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <span style="font-size: 0.9em; color: #666;">Consumo Real (Factura)</span>
                    <p style="font-size: 2em; font-weight: bold; margin: 5px 0; color: #007bff;">
                        {{ number_format($periodSummary->real_consumption, 0, ',', '.') }} kWh
                    </p>
                </div>
                <div style="text-align: center; padding: 15px; background-color: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <span style="font-size: 0.9em; color: #666;">Consumo Explicado (Inventario)</span>
                    <p style="font-size: 2em; font-weight: bold; margin: 5px 0; color: #17a2b8;">
                        {{ number_format($periodSummary->estimated_consumption, 0, ',', '.') }} kWh
                    </p>
                </div>
            </div>
            @php
                $difference = $periodSummary->real_consumption - $periodSummary->estimated_consumption;
                $percentageExplained = $periodSummary->real_consumption > 0 ? ($periodSummary->estimated_consumption / $periodSummary->real_consumption) * 100 : 0;
            @endphp
            <div style="margin-top: 20px; text-align: center; color: #333;">
                <p>Tu inventario actual explica el <strong>{{ number_format($percentageExplained, 0) }}%</strong> de tu consumo real.</p>
                <p style="margin-top: 5px;"><small>Hay una diferencia de <strong>{{ number_format($difference, 0, ',', '.') }} kWh</strong> sin explicar.</small></p>
            </div>
        @endif
    </div>

    <!-- ======================================================= -->
    <!-- DETALLES DE LA ENTIDAD E INVENTARIO                   -->
    <!-- ======================================================= -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        
        <!-- Columna de Detalles -->
        <div style="padding: 20px; border: 1px solid #eee; border-radius: 8px; background-color: #fdfdff;">
            <h3 style="font-size: 1.2em; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Detalles de la Entidad</h3>
            <p><strong>Tipo:</strong> {{ ucfirst($entity->type) }}</p>
            <p style="margin-top: 10px;"><strong>Dirección:</strong> {{ $entity->address_street }}, {{ $entity->locality->name ?? 'N/A' }}</p>
            <p style="margin-top: 10px;"><strong>Ocupantes:</strong> {{ $entity->details['occupants_count'] ?? 'No especificado' }}</p>
        </div>

        <!-- Columna de Inventario y Suministros -->
        <div style="padding: 20px; border: 1px solid #eee; border-radius: 8px; background-color: #fdfdff;">
            <h3 style="font-size: 1.2em; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Inventario y Suministros</h3>
            <p>Tienes <strong>{{ $entity->equipments->count() }}</strong> equipos registrados.</p>
            <p style="margin-top: 10px;">Tienes <strong>{{ $entity->supplies->count() }}</strong> suministros eléctricos asociados.</p>
        </div>

    </div>

</x-app-layout>
