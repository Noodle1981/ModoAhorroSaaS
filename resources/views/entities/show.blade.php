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
        <a href="{{ route('entities.equipment.index', $entity) }}" style="background-color: #28a745; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Gestionar inventario
        </a>
        @php
            $supply = $entity->supplies->first();
        @endphp
        <a href="{{ $supply ? route('supplies.show', $supply) : route('entities.supplies.create', $entity) }}" style="background-color: #17a2b8; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            {{ $supply ? 'Gestionar Suministro' : 'Añadir Suministro' }}
        </a>
    </div>

    <!-- ======================================================= -->
    <!-- ANÁLISIS DE CONSUMO                                     -->
    <!-- ======================================================= -->
    <div style="margin-bottom: 30px;">
        
        @if(empty($periodsAnalysis))
            <div style="text-align: center; padding: 40px 20px; background-color: #f8f9fa; border-radius: 8px;">
                <h3 style="font-size: 1.5em; font-weight: bold; margin-bottom: 15px;">Empieza a Analizar tu Consumo</h3>
                <p style="color: #6c757d; margin-bottom: 20px;">Para desbloquear el análisis de consumo y las recomendaciones, necesitas añadir tu información de suministro y cargar tu primera factura.</p>
                @php
                    $firstSupply = $entity->supplies->first();
                    $firstContract = $firstSupply?->contracts->where('is_active', true)->first() ?? $firstSupply?->contracts->first();
                @endphp
                @if ($firstContract)
                    <a href="{{ route('contracts.invoices.create', $firstContract) }}" style="display: inline-block; background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 1.1em;">
                        + Cargar Primera Factura
                    </a>
                @elseif ($firstSupply)
                    <p style="margin-top: 10px;">El siguiente paso es <a href="{{ route('supplies.contracts.create', $firstSupply) }}" style="color: #007bff; font-weight: bold;">crear un contrato para tu suministro</a>.</p>
                @else
                    <p style="margin-top: 10px;">Para empezar, <a href="{{ route('entities.supplies.create', $entity) }}" style="color: #007bff; font-weight: bold;">añade un punto de suministro</a>.</p>
                @endif
            </div>
        @else

            <!-- == RESUMEN GENERAL == -->
            @if($summary)
            <div style="margin-bottom: 30px; padding: 25px; border: 1px solid #007bff; border-radius: 8px; background-color: #f0f7ff;">
                <h3 style="font-size: 1.6em; font-weight: bold; margin-bottom: 20px; color: #0056b3;">Análisis General Acumulado</h3>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <h4 style="font-weight: bold;">Período Total Analizado</h4>
                        <p style="font-size: 1.1em; color: #333;">{{ $summary->start_date }} - {{ $summary->end_date }}</p>
                    </div>
                    <div style="text-align: right;">
                        <h4 style="font-weight: bold;">Nivel de Acierto Promedio</h4>
                        <p style="font-size: 1.5em; font-weight: bold; color: #007bff;">{{ number_format($summary->average_percentage_explained, 0) }}%</p>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; text-align: center;">
                    <div>
                        <span style="font-size: 0.9em; color: #666;">Consumo Real Total</span>
                        <p style="font-weight: bold; font-size: 1.3em;">{{ number_format($summary->total_real_consumption, 0, ',', '.') }} kWh</p>
                    </div>
                    <div>
                        <span style="font-size: 0.9em; color: #666;">Consumo Explicado Total</span>
                        <p style="font-weight: bold; font-size: 1.3em;">{{ number_format($summary->total_estimated_consumption, 0, ',', '.') }} kWh</p>
                    </div>
                    <div>
                        <span style="font-size: 0.9em; color: #666;">Coste Total</span>
                        <p style="font-weight: bold; font-size: 1.3em;">$ {{ number_format($summary->total_amount, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- == HISTORIAL DE PERÍODOS == -->
            <h3 style="font-size: 1.5em; font-weight: bold; margin-bottom: 15px; padding-top: 15px; border-top: 1px solid #ddd;">Historial de Períodos Analizados</h3>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                @foreach ($periodsAnalysis as $analysis)
                    <div style="padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <h4 style="font-weight: bold;">Período: {{ $analysis->period_label }}</h4>
                                <p style="font-size: 0.9em; color: #555;">
                                    Tu inventario explica el <strong style="font-size: 1.1em;">{{ number_format($analysis->percentage_explained, 0) }}%</strong> de tu consumo.
                                </p>
                            </div>
                            <a href="{{ route('snapshots.create', $analysis->invoice) }}" style="background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; white-space: nowrap;">
                                Ajustar Equipos
                            </a>
                        </div>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px; text-align: center;">
                            <div>
                                <span style="font-size: 0.85em; color: #666;">Consumo Real</span>
                                <p style="font-weight: bold; font-size: 1.2em;">{{ number_format($analysis->real_consumption, 0, ',', '.') }} kWh</p>
                            </div>
                            <div>
                                <span style="font-size: 0.85em; color: #666;">Consumo Explicado</span>
                                <p style="font-weight: bold; font-size: 1.2em;">{{ number_format($analysis->estimated_consumption, 0, ',', '.') }} kWh</p>
                            </div>
                            <div>
                                <span style="font-size: 0.85em; color: #666;">Importe Factura</span>
                                <p style="font-weight: bold; font-size: 1.2em;">$ {{ number_format($analysis->total_amount, 2, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
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
             <p style="margin-top: 10px;"><strong>Secciones:</strong> {{ count($entity->details['rooms'] ?? []) }}</p>
        </div>

        <!-- Columna de Inventario y Suministros -->
        <div style="padding: 20px; border: 1px solid #eee; border-radius: 8px; background-color: #fdfdff;">
            <h3 style="font-size: 1.2em; font-weight: bold; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 15px;">Inventario y Suministros</h3>
            <p>Tienes <strong>{{ $entity->equipments->count() }}</strong> equipos registrados.</p>
            <p style="margin-top: 10px;">Tienes <strong>{{ $entity->supplies->count() }}</strong> suministros eléctricos asociados.</p>
        </div>

    </div>

</x-app-layout>
