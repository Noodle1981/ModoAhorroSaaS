<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Recomendación: Calefón Solar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Intro -->
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border-l-4 border-orange-400 p-6 rounded-lg">
                <div class="flex items-start gap-4">
                    <i class="fas fa-sun text-4xl text-orange-500"></i>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">¿Por qué Calefón Solar?</h3>
                        <p class="text-sm text-gray-700 mb-2">
                            Los calefones solares aprovechan la energía del sol para calentar agua, reduciendo hasta un <strong>70% tu consumo eléctrico o de gas</strong>.
                            Son ideales para climas con buena radiación solar y tienen un retorno de inversión de 3-8 años.
                        </p>
                        <p class="text-xs text-gray-600">
                            💡 <strong>Tip:</strong> Si tenés calefón eléctrico, el ahorro es máximo. Con gas también es rentable si consumís mucho.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Análisis por entidad -->
            @forelse ($analyses as $entityId => $data)
                @php
                    $entity = $data['entity'];
                    $analysis = $data['analysis'];
                @endphp

                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800">{{ $entity->name }}</h3>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full 
                            @if($analysis['recommendation'] === 'highly_recommended') bg-green-200 text-green-800
                            @elseif($analysis['recommendation'] === 'recommended') bg-yellow-200 text-yellow-800
                            @elseif($analysis['recommendation'] === 'consider') bg-blue-200 text-blue-800
                            @else bg-gray-200 text-gray-600
                            @endif">
                            @if($analysis['recommendation'] === 'highly_recommended') ⭐ Muy Recomendado
                            @elseif($analysis['recommendation'] === 'recommended') ✅ Recomendado
                            @elseif($analysis['recommendation'] === 'consider') 💡 Considerar
                            @elseif($analysis['recommendation'] === 'no_heater_detected') ❓ Sin Calefón Detectado
                            @endif
                        </span>
                    </div>

                    @if($analysis['has_heater'])
                        <!-- Detección -->
                        <div class="mb-4 p-4 bg-blue-50 rounded-lg">
                            <h4 class="font-semibold text-gray-700 mb-2">🔍 Detección de Calefón:</h4>
                            
                            @if($analysis['calculation_method'] === 'electric' && !empty($analysis['detected_heaters']))
                                <p class="text-sm text-blue-700 mb-2"><strong>Calefones eléctricos detectados en inventario:</strong></p>
                                <ul class="space-y-1 text-sm">
                                    @foreach($analysis['detected_heaters'] as $heater)
                                    <li class="flex items-center gap-2">
                                        <i class="fas fa-bolt text-yellow-500"></i>
                                        <span><strong>{{ $heater['name'] }}</strong> - {{ $heater['power_watts'] }}W - {{ round($heater['daily_minutes']/60, 1) }}h/día (Cant: {{ $heater['quantity'] }})</span>
                                    </li>
                                    @endforeach
                                </ul>
                                <p class="text-xs text-gray-600 mt-2">
                                    <i class="fas fa-calculator mr-1"></i> Cálculo basado en consumo eléctrico real de tu inventario.
                                </p>
                            @elseif($analysis['calculation_method'] === 'estimated')
                                <p class="text-sm text-blue-700 mb-2">
                                    <i class="fas fa-info-circle mr-1"></i> 
                                    <strong>Calefón 
                                    @if($analysis['heater_type'] === 'gas') a Gas Natural
                                    @elseif($analysis['heater_type'] === 'glp') a GLP (garrafas)
                                    @elseif($analysis['heater_type'] === 'wood') a Leña
                                    @else detectado
                                    @endif
                                    :</strong>
                                </p>
                                <p class="text-xs text-gray-600 mt-2">
                                    <i class="fas fa-chart-line mr-1"></i> Cálculo estimado basado en consumo promedio ({{ $entity->details['people'] ?? 4 }} personas).
                                </p>
                                @if($analysis['heater_type'] === 'gas')
                                    <p class="text-xs text-gray-500 mt-1">📊 Consumo estimado: <strong>{{ $analysis['annual_gas_m3'] }} m³/año</strong> de gas natural</p>
                                @elseif($analysis['heater_type'] === 'glp')
                                    <p class="text-xs text-gray-500 mt-1">📊 Consumo estimado: <strong>{{ $analysis['annual_garrafas'] }} garrafas/año</strong> (10kg c/u)</p>
                                @elseif($analysis['heater_type'] === 'wood')
                                    <p class="text-xs text-gray-500 mt-1">📊 Consumo estimado: <strong>{{ $analysis['annual_wood_m3'] }} m³/año</strong> de leña</p>
                                @endif
                            @endif
                        </div>

                        <!-- Métricas -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="p-4 bg-red-50 rounded-lg text-center">
                                <p class="text-xs text-gray-600 mb-1">Costo Anual Actual</p>
                                @if($analysis['calculation_method'] === 'electric')
                                    <p class="text-2xl font-bold text-red-700">{{ number_format($analysis['annual_consumption_kwh'], 0) }} kWh</p>
                                    <p class="text-sm text-gray-600">${{ number_format($analysis['annual_cost_ars'], 0) }}/año</p>
                                @elseif($analysis['heater_type'] === 'gas')
                                    <p class="text-2xl font-bold text-red-700">{{ number_format($analysis['annual_gas_m3'], 0) }} m³</p>
                                    <p class="text-sm text-gray-600">${{ number_format($analysis['annual_cost_ars'], 0) }}/año</p>
                                @elseif($analysis['heater_type'] === 'glp')
                                    <p class="text-2xl font-bold text-red-700">{{ number_format($analysis['annual_garrafas'], 1) }} garrafas</p>
                                    <p class="text-sm text-gray-600">${{ number_format($analysis['annual_cost_ars'], 0) }}/año</p>
                                @else
                                    <p class="text-2xl font-bold text-red-700">${{ number_format($analysis['annual_cost_ars'], 0) }}</p>
                                    <p class="text-sm text-gray-600">Estimado/año</p>
                                @endif
                            </div>
                            <div class="p-4 bg-green-50 rounded-lg text-center">
                                <p class="text-xs text-gray-600 mb-1">Ahorro con Solar</p>
                                <p class="text-2xl font-bold text-green-700">{{ $analysis['solar_savings_percent'] }}%</p>
                                <p class="text-sm text-gray-600">${{ number_format($analysis['annual_cost_ars'] * $analysis['solar_savings_percent'] / 100, 0) }}/año</p>
                            </div>
                            <div class="p-4 bg-blue-50 rounded-lg text-center">
                                <p class="text-xs text-gray-600 mb-1">Retorno (Payback)</p>
                                <p class="text-2xl font-bold text-blue-700">{{ $analysis['payback_years'] }} años</p>
                                <p class="text-sm text-gray-600">Sistema: ${{ number_format($analysis['estimated_solar_cost_ars'], 0) }}</p>
                            </div>
                            <div class="p-4 bg-purple-50 rounded-lg text-center">
                                <p class="text-xs text-gray-600 mb-1">Ahorro a 20 años</p>
                                <p class="text-2xl font-bold text-purple-700">${{ number_format($analysis['savings_20_years'], 0) }}</p>
                                <p class="text-sm text-gray-600">CO₂ evitado: {{ number_format($analysis['co2_saved_kg'] * 20, 0) }} kg</p>
                            </div>
                        </div>

                        <!-- Recomendación detallada -->
                        <div class="p-4 border-l-4 
                            @if($analysis['recommendation'] === 'highly_recommended') border-green-500 bg-green-50
                            @elseif($analysis['recommendation'] === 'recommended') border-yellow-500 bg-yellow-50
                            @elseif($analysis['recommendation'] === 'consider_comfort') border-purple-500 bg-purple-50
                            @else border-blue-500 bg-blue-50
                            @endif rounded">
                            @if($analysis['recommendation'] === 'highly_recommended')
                                <p class="text-sm text-gray-700">
                                    <strong>¡Excelente oportunidad!</strong> Con tu calefón eléctrico y un retorno de inversión de {{ $analysis['payback_years'] }} años, 
                                    el calefón solar es altamente rentable. Recomendamos solicitar presupuestos.
                                </p>
                            @elseif($analysis['recommendation'] === 'recommended')
                                <p class="text-sm text-gray-700">
                                    <strong>Buena opción.</strong> 
                                    @if($analysis['heater_type'] === 'electric')
                                        El calefón solar reducirá significativamente tu consumo eléctrico.
                                    @elseif($analysis['heater_type'] === 'gas')
                                        Reemplazar tu calefón a gas por solar te dará independencia y ahorro a largo plazo.
                                    @elseif($analysis['heater_type'] === 'glp')
                                        ¡Dejá de cargar garrafas! El solar te da autonomía total y en {{ $analysis['payback_years'] }} años recuperás la inversión.
                                    @else
                                        El calefón solar es una inversión sólida para tu caso.
                                    @endif
                                    Con {{ $analysis['payback_years'] }} años de retorno, es rentable.
                                </p>
                            @elseif($analysis['recommendation'] === 'consider_comfort')
                                <p class="text-sm text-gray-700">
                                    <strong>Opción por comodidad y ecología.</strong> Tu calefón a leña tiene costos moderados, 
                                    pero el solar te dará comodidad (sin cargar leña) y es 100% renovable. 
                                    El retorno es de {{ $analysis['payback_years'] }} años, pero el valor está en el confort.
                                </p>
                            @else
                                <p class="text-sm text-gray-700">
                                    <strong>Opción a considerar.</strong> 
                                    @if($analysis['heater_type'] === 'gas')
                                        Con gas el ahorro económico es moderado, pero ganás independencia del servicio de red.
                                    @elseif($analysis['heater_type'] === 'glp')
                                        El ahorro es moderado pero eliminás el trabajo de cargar garrafas.
                                    @else
                                        El calefón solar suma valor ecológico y autonomía.
                                    @endif
                                    Evalúa si el retorno de {{ $analysis['payback_years'] }} años se ajusta a tu presupuesto. 
                                    En 20 años ahorrarías ${{ number_format($analysis['savings_20_years'], 0) }}.
                                </p>
                            @endif
                        </div>

                        <!-- Botón de acción -->
                        <div class="mt-4 flex justify-end">
                            <a href="{{ route('solar-heater.interest', $entity) }}" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                                <i class="fas fa-hand-point-up mr-2"></i>Me Interesa / Actualizar Info
                            </a>
                        </div>

                    @else
                        <!-- No hay calefón detectado -->
                        <div class="p-4 bg-gray-50 rounded-lg text-center">
                            <i class="fas fa-question-circle text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-600 mb-4">
                                No detectamos ningún calefón en tu inventario de <strong>{{ $entity->name }}</strong>.
                            </p>
                            <p class="text-sm text-gray-500 mb-4">
                                ¿Tenés un calefón que no registraste? Agregalo en tu inventario o indicanos tu situación actual:
                            </p>
                            <a href="{{ route('solar-heater.interest', $entity) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                <i class="fas fa-plus-circle mr-2"></i>Informar mi Calefón Actual
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-600">No tienes entidades registradas. <a href="{{ route('entities.create') }}" class="text-blue-600 underline">Crear una ahora</a>.</p>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
