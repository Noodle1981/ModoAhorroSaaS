<x-app-layout>
    <h1>Análisis de Mejoras para: {{ $entity->name }}</h1>
    <p>
        <a href="{{ route('entities.show', $entity) }}">&larr; Volver a la Entidad</a>
    </p>

    @if ($hasBillingData)
        <p style="margin-top: 10px;">Este es el análisis de tu inventario de equipos. Hemos comparado cada equipo con nuestro catálogo de modelos eficientes.</p>

        <div style="margin-top: 30px;">
            @forelse ($opportunities as $analysis)
                @switch($analysis['status'])
                    
                    @case('OPPORTUNITY_FOUND')
                        {{-- ESTADO 1: EQUIPO DEFICIENTE (OPORTUNIDAD ENCONTRADA) --}}
                        <div style="border-left: 5px solid #fd7e14; background-color: #fff; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <span style="background-color: #fd7e14; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">
                                        Equipo Deficiente
                                    </span>
                                    <strong style="margin-left: 10px; font-size: 1.1em;">{{ $analysis['user_equipment_name'] }}</strong>
                                </div>
                                <div style="text-align: right;">
                                    <strong style="font-size: 1.2em; color: #28a745;">Ahorro Anual: ${{ number_format($analysis['ahorro_anual_pesos'], 2, ',', '.') }}</strong>
                                </div>
                            </div>
                            <p style="margin-top: 15px; margin-bottom: 5px; color: #333; font-size: 1em;"><strong>Sugerencia:</strong> {{ $analysis['suggestion'] }}</p>
                            @if (isset($analysis['retorno_inversion_anios']))
                                <small style="color: #666;">Retorno de la inversión estimado: {{ number_format($analysis['retorno_inversion_anios'], 1, ',', '.') }} años.</small>
                            @endif
                        </div>
                        @break

                    @case('ALREADY_EFFICIENT')
                        {{-- ESTADO 2: EQUIPO EFICIENTE --}}
                        <div style="border-left: 5px solid #28a745; background-color: #f8f9fa; padding: 10px 15px; margin-bottom: 15px; border-radius: 5px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong>{{ $analysis['user_equipment_name'] }}</strong>
                                <span style="color: #28a745; font-weight: bold; font-size: 0.9em;">Equipo Eficiente</span>
                            </div>
                        </div>
                        @break

                    @case('NO_CATALOG_ENTRY')
                        {{-- ESTADO 3: EQUIPO SIN COMPARATIVA --}}
                        <div style="border-left: 5px solid #6c757d; background-color: #f8f9fa; padding: 10px 15px; margin-bottom: 15px; border-radius: 5px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong>{{ $analysis['user_equipment_name'] }}</strong>
                                <span style="color: #6c757d; font-weight: bold; font-size: 0.9em;">Equipo sin Comparativa</span>
                            </div>
                        </div>
                        @break
                        
                @endswitch
            @empty
                <div style="margin-top: 30px; padding: 40px; text-align: center; background-color: #f9f9f9; border-radius: 8px;">
                    <h3>No tienes equipos en tu inventario.</h3>
                    <p>Para poder analizar tus oportunidades de ahorro, primero necesitas <a href="{{ route('entities.show', $entity) }}">añadir equipos a tu entidad</a>.</p>
                </div>
            @endforelse
        </div>
    @else
        {{-- ESTADO 0: SIN DATOS DE FACTURACIÓN --}}
        <div style="margin-top: 30px; padding: 40px; text-align: center; background-color: #fffbe6; border: 1px solid #ffe58f; border-radius: 8px;">
            <h3>Recomendaciones Desactivadas</h3>
            <p>Para poder calcular ahorros y ofrecerte recomendaciones personalizadas, necesitamos que cargues al menos una factura de energía con consumo.</p>
            <p>Los cálculos de ahorro se basan en el costo por kWh de tu última factura.</p>
            <a href="#" style="margin-top: 15px; display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Cargar Factura</a>
        </div>
    @endif

</x-app-layout>