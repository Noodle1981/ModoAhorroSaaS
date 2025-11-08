<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-solar-panel mr-2"></i>
            Potencial de Ahorro con Energía Solar
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Header con resumen total -->
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">
                            <i class="fas fa-piggy-bank mr-2"></i>
                            Ahorro Potencial Anual
                        </h3>
                        <p class="text-yellow-100">
                            Si instalaras paneles solares en tus propiedades
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-5xl font-bold">
                            ${{ number_format($totalSavingsPotential, 0, ',', '.') }}
                        </p>
                        <p class="text-yellow-100 text-sm mt-1">
                            menos en tu factura por año
                        </p>
                    </div>
                </div>
            </div>

            <!-- Análisis por propiedad -->
            @foreach($potentialAnalyses as $item)
                @php
                    $entity = $item['entity'];
                    $analysis = $item['analysis'];
                @endphp
                
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    
                    <!-- Header propiedad -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 border-b">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-home text-blue-600 text-xl"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-800">{{ $entity->name }}</h4>
                                    <p class="text-sm text-gray-600">
                                        {{ $entity->locality?->name }}, {{ $entity->locality?->province?->name ?? 'N/A' }}
                                    </p>
                                </div>
                            </div>
                            
                            @if($analysis['recommendation'])
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $analysis['recommendation'] === 'highly_recommended' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $analysis['recommendation'] === 'recommended' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $analysis['recommendation'] === 'consider' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                ">
                                    @switch($analysis['recommendation'])
                                        @case('highly_recommended')
                                            <i class="fas fa-star mr-1"></i>Muy Recomendado
                                            @break
                                        @case('recommended')
                                            <i class="fas fa-thumbs-up mr-1"></i>Recomendado
                                            @break
                                        @case('consider')
                                            <i class="fas fa-info-circle mr-1"></i>Viable
                                            @break
                                    @endswitch
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="p-6">
                        
                        <!-- KPIs de ahorro -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            
                            <!-- Potencia instalable -->
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-blue-700 font-medium">Potencia</span>
                                    <i class="fas fa-bolt text-blue-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-blue-900">
                                    {{ number_format($analysis['installable_kwp'], 2) }} kWp
                                </p>
                                @if(isset($analysis['roof_kwp']) && isset($analysis['ground_kwp']) && $analysis['roof_kwp'] > 0 && $analysis['ground_kwp'] > 0)
                                    <p class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-home mr-1"></i>{{ number_format($analysis['roof_kwp'], 2) }} + 
                                        <i class="fas fa-tree ml-1 mr-1"></i>{{ number_format($analysis['ground_kwp'], 2) }} kWp
                                    </p>
                                @elseif(isset($analysis['roof_kwp']) && $analysis['roof_kwp'] > 0)
                                    <p class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-home mr-1"></i>Techo
                                    </p>
                                @elseif(isset($analysis['ground_kwp']) && $analysis['ground_kwp'] > 0)
                                    <p class="text-xs text-blue-600 mt-1">
                                        <i class="fas fa-tree mr-1"></i>Terreno
                                    </p>
                                @else
                                    <p class="text-xs text-blue-600 mt-1">
                                        instalable
                                    </p>
                                @endif
                            </div>

                            <!-- Ahorro mensual -->
                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-green-700 font-medium">Ahorro Mensual</span>
                                    <i class="fas fa-dollar-sign text-green-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-green-900">
                                    ${{ number_format($analysis['savings_annual_ars'] / 12, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-green-600 mt-1">
                                    menos en tu factura
                                </p>
                            </div>

                            <!-- Cobertura de consumo -->
                            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-purple-700 font-medium">Cobertura</span>
                                    <i class="fas fa-percentage text-purple-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-purple-900">
                                    {{ number_format($analysis['coverage_percent'], 0) }}%
                                </p>
                                <p class="text-xs text-blue-600 mt-1">
                                    de tu consumo
                                </p>
                            </div>

                            <!-- Inversión -->
                            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm text-purple-700 font-medium">Inversión</span>
                                    <i class="fas fa-coins text-purple-600"></i>
                                </div>
                                <p class="text-2xl font-bold text-purple-900">
                                    ${{ number_format($analysis['investment_ars'] / 1000, 0) }}k
                                </p>
                                <p class="text-xs text-purple-600 mt-1">
                                    instalación completa
                                </p>
                            </div>

                        </div>

                        <!-- Mensaje contextual -->
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-4 mb-6">
                            <div class="flex items-start">
                                <i class="fas fa-lightbulb text-green-600 text-2xl mr-3 mt-1"></i>
                                <div>
                                    <h5 class="font-semibold text-green-900 mb-2">
                                        ¿Qué significa esto para tu factura?
                                    </h5>
                                    <p class="text-sm text-green-800 mb-2">
                                        <strong>Ahorro inmediato:</strong> 
                                        Pagarías <strong>${{ number_format($analysis['savings_annual_ars'] / 12, 0, ',', '.') }} menos por mes</strong> 
                                        desde el primer día de instalación.
                                    </p>
                                    <p class="text-sm text-green-800 mb-2">
                                        <strong>Recuperación:</strong> 
                                        En {{ number_format($analysis['payback_years'], 1) }} años habrás recuperado tu inversión 
                                        completamente, y después todo es ganancia pura.
                                    </p>
                                    <p class="text-sm text-green-800">
                                        <strong>20 años de ahorro:</strong> 
                                        Vida útil de los paneles = <strong>${{ number_format($analysis['savings_annual_ars'] * 20, 0, ',', '.') }} 
                                        total ahorrado</strong> en facturas.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Detalles técnicos -->
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Potencia</p>
                                <p class="text-lg font-bold text-gray-900">{{ number_format($analysis['installable_kwp'], 2) }} kWp</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Generación Verano</p>
                                <p class="text-lg font-bold text-gray-900">{{ number_format($analysis['generation_summer_kwh_month'], 0) }} kWh/mes</p>
                            </div>
                            <div class="text-center p-3 bg-gray-50 rounded-lg">
                                <p class="text-xs text-gray-600 mb-1">Generación Invierno</p>
                                <p class="text-lg font-bold text-gray-900">{{ number_format($analysis['generation_winter_kwh_month'], 0) }} kWh/mes</p>
                            </div>
                        </div>

                        <!-- CO2 -->
                        <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-lg p-4 mb-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <i class="fas fa-leaf text-green-600 text-3xl"></i>
                                    <div>
                                        <p class="text-sm text-gray-600">Impacto Ambiental</p>
                                        <p class="text-xl font-bold text-green-800">
                                            {{ number_format($analysis['co2_avoided_kg'], 0) }} kg CO₂ evitados/año
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="flex flex-wrap gap-3 pt-4 border-t">
                            <a href="{{ route('solar-panel.configure', $entity) }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                                <i class="fas fa-cog mr-2"></i>
                                Ajustar Datos
                            </a>
                            
                            <a href="{{ route('solar-panel.simulate', $entity) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Ver Escenarios
                            </a>

                            <form method="POST" action="{{ route('solar.lead.store') }}">
                                @csrf
                                <input type="hidden" name="entity_id" value="{{ $entity->id }}">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                    <i class="fas fa-phone mr-2"></i>
                                    Solicitar Presupuesto
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach

            <!-- CTA Final -->
            <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg shadow-lg p-8 text-white text-center">
                <i class="fas fa-rocket text-5xl mb-4 opacity-90"></i>
                <h3 class="text-2xl font-bold mb-3">
                    ¿Listo para reducir tu factura de luz?
                </h3>
                <p class="mb-6 text-blue-100">
                    Con energía solar podrías estar ahorrando <strong>${{ number_format($totalSavingsPotential / 12, 0, ',', '.') }} por mes</strong>
                </p>
                <a href="{{ route('solar-panel.index') }}" 
                   class="inline-flex items-center px-8 py-3 bg-white text-blue-600 hover:bg-gray-100 rounded-lg transition shadow-lg font-semibold">
                    <i class="fas fa-calculator mr-2"></i>
                    Ver Análisis Completo
                </a>
            </div>

            <!-- Info adicional -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h5 class="font-semibold text-blue-900 mb-2">Sobre estos cálculos</h5>
                        <p class="text-sm text-blue-800">
                            Los ahorros mostrados se basan en tu consumo real actual, radiación solar de tu zona,
                            y precios promedio de energía. Los paneles solares tienen vida útil de 20-25 años
                            con mantenimiento mínimo, convirtiendo tu techo en un activo productivo.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
