<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-solar-panel mr-2"></i>
            Análisis de Potencial Solar
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        <i class="fas fa-sun text-yellow-500 mr-2"></i>
                        Calculá el Ahorro en tu Factura con Energía Solar
                    </h3>
                    <span class="text-sm text-gray-500">
                        <i class="fas fa-lightbulb mr-1"></i>
                        Análisis basado en tu consumo real
                    </span>
                </div>

                <p class="text-gray-600 mb-6">
                    Descubrí cuánto menos pagarías mensualmente en tu factura de luz instalando paneles solares.
                    El análisis considera tu consumo actual, radiación solar de tu zona, y características de tu propiedad.
                </p>

                @forelse($entities as $entity)
                    @php
                        $analysis = $analyses[$entity->id];
                    @endphp
                    
                    <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition">
                        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4">
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
                                
                                <div class="flex items-center gap-2">
                                @if($analysis['recommendation'])
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $analysis['recommendation'] === 'highly_recommended' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $analysis['recommendation'] === 'recommended' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $analysis['recommendation'] === 'consider' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $analysis['recommendation'] === 'need_roof_data' ? 'bg-gray-100 text-gray-800' : '' }}
                                        {{ $analysis['recommendation'] === 'insufficient_space' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $analysis['recommendation'] === 'partial_coverage' ? 'bg-orange-100 text-orange-800' : '' }}
                                    ">
                                        @switch($analysis['recommendation'])
                                            @case('highly_recommended')
                                                <i class="fas fa-star mr-1"></i>Altamente Recomendado
                                                @break
                                            @case('recommended')
                                                <i class="fas fa-thumbs-up mr-1"></i>Recomendado
                                                @break
                                            @case('consider')
                                                <i class="fas fa-info-circle mr-1"></i>A Considerar
                                                @break
                                            @case('need_roof_data')
                                                <i class="fas fa-question-circle mr-1"></i>Datos Incompletos
                                                @break
                                            @case('insufficient_space')
                                                <i class="fas fa-times-circle mr-1"></i>Espacio Insuficiente
                                                @break
                                            @case('partial_coverage')
                                                <i class="fas fa-chart-line mr-1"></i>Cobertura Parcial
                                                @break
                                        @endswitch
                                    </span>
                                @endif
                                @if(($analysis['simple_source'] ?? '') === 'fallback_details_area')
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800" title="Usando 20% del área del lote como proxy de techo">
                                        <i class="fas fa-ruler-combined mr-1"></i>
                                        Estimación por lote: 20% de {{ number_format($analysis['simple_plot_area_m2'] ?? 0, 0) }} m²
                                    </span>
                                @endif
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            @if($analysis['recommendation'] === 'need_roof_data')
                                <div class="text-center py-8">
                                    <i class="fas fa-ruler-combined text-gray-300 text-5xl mb-4"></i>
                                    <p class="text-gray-600 mb-4">
                                        Para calcular cuánto ahorrarías, necesitamos conocer las características del techo.
                                    </p>
                                    <a href="{{ route('solar.configure', $entity) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                        <i class="fas fa-cog mr-2"></i>
                                        Ingresar Datos del Techo
                                    </a>
                                    @if(($analysis['simple_paneles'] ?? 0) > 0)
                                        <div class="mt-6 mx-auto max-w-2xl text-left bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                                            <h5 class="font-semibold text-yellow-900 mb-1">
                                                Estimación base por m² (sin datos de techo)
                                            </h5>
                                            <p class="text-sm text-yellow-800 mb-2">
                                                Para orientarte, usando una regla simple estimamos <strong>{{ number_format($analysis['simple_potencia_kwp'], 2) }} kWp</strong>
                                                con <strong>{{ $analysis['simple_paneles'] }} paneles</strong> en <strong>{{ number_format($analysis['simple_area_util_m2'], 1) }} m² útiles</strong>.
                                            </p>
                                            @if(($analysis['simple_source'] ?? '') === 'fallback_details_area')
                                                <p class="text-xs text-yellow-700">
                                                    Tomamos como base el área del lote ({{ number_format($analysis['simple_plot_area_m2'] ?? 0, 0) }} m²) y asumimos un {{ \App\Services\SolarPotentialAnalysisService::FALLBACK_ROOF_RATIO_FROM_PLOT * 100 }}% de superficie de techo para esta estimación.
                                                </p>
                                            @endif
                                            <p class="text-xs text-yellow-700 mt-1">
                                                Esta es una referencia rápida. El presupuesto final depende de orientación, sombras y obstáculos.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @elseif($analysis['recommendation'] === 'insufficient_space')
                                <div class="text-center py-8">
                                    <i class="fas fa-compress-alt text-orange-300 text-5xl mb-4"></i>
                                    <p class="text-gray-600 mb-2">
                                        El espacio disponible ({{ number_format($analysis['total_usable_m2'], 1) }} m²) es insuficiente para una instalación solar estándar.
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Se recomienda al menos 10 m² de área útil para una instalación viable.
                                    </p>
                                </div>
                            @else
                                <!-- GRID DE MÉTRICAS -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                                    
                                    <!-- Área útil -->
                                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-blue-700 font-medium">Área Útil</span>
                                            <i class="fas fa-ruler-combined text-blue-600"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-blue-900">
                                            {{ number_format($analysis['total_usable_m2'], 1) }} m²
                                        </p>
                                        @if(isset($analysis['roof_usable_m2']) && isset($analysis['ground_usable_m2']))
                                            <p class="text-xs text-blue-600 mt-1">
                                                Techo: {{ number_format($analysis['roof_usable_m2'], 1) }} m² + Terreno: {{ number_format($analysis['ground_usable_m2'], 1) }} m²
                                            </p>
                                        @elseif(isset($analysis['roof_usable_m2']))
                                            <p class="text-xs text-blue-600 mt-1">
                                                de {{ number_format($analysis['roof_area_m2'] ?? 0, 1) }} m² totales en techo
                                            </p>
                                        @elseif(isset($analysis['ground_usable_m2']))
                                            <p class="text-xs text-blue-600 mt-1">
                                                de {{ number_format($analysis['ground_area_m2'] ?? 0, 1) }} m² totales en terreno
                                            </p>
                                        @else
                                            <p class="text-xs text-blue-600 mt-1">
                                                de {{ number_format($analysis['roof_area_m2'] ?? 0, 1) }} m² totales
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Potencia instalable -->
                                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-green-700 font-medium">Potencia</span>
                                            <i class="fas fa-bolt text-green-600"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-green-900">
                                            {{ number_format($analysis['total_kwp'], 2) }} kWp
                                        </p>
                                        @if(isset($analysis['roof_kwp']) && isset($analysis['ground_kwp']) && $analysis['roof_kwp'] > 0 && $analysis['ground_kwp'] > 0)
                                            <p class="text-xs text-green-600 mt-1">
                                                <i class="fas fa-home mr-1"></i>{{ number_format($analysis['roof_kwp'], 2) }} kWp + 
                                                <i class="fas fa-tree ml-1 mr-1"></i>{{ number_format($analysis['ground_kwp'], 2) }} kWp
                                            </p>
                                        @elseif(isset($analysis['roof_kwp']) && $analysis['roof_kwp'] > 0)
                                            <p class="text-xs text-green-600 mt-1">
                                                <i class="fas fa-home mr-1"></i>Instalación en techo
                                            </p>
                                        @elseif(isset($analysis['ground_kwp']) && $analysis['ground_kwp'] > 0)
                                            <p class="text-xs text-green-600 mt-1">
                                                <i class="fas fa-tree mr-1"></i>Instalación en terreno
                                            </p>
                                        @else
                                            <p class="text-xs text-green-600 mt-1">
                                                kilowatt-pico instalable
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Generación anual -->
                                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-yellow-700 font-medium">Generación</span>
                                            <i class="fas fa-sun text-yellow-600"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-yellow-900">
                                            {{ number_format($analysis['generation_annual_kwh'], 0) }} kWh
                                        </p>
                                        <p class="text-xs text-yellow-600 mt-1">
                                            por año (estimado)
                                        </p>
                                    </div>

                                    <!-- Cobertura de consumo -->
                                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-purple-700 font-medium">Cobertura</span>
                                            <i class="fas fa-percentage text-purple-600"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-purple-900">
                                            {{ number_format($analysis['coverage_percent'], 1) }}%
                                        </p>
                                        <p class="text-xs text-purple-600 mt-1">
                                            de tu consumo anual
                                        </p>
                                        @if($analysis['coverage_percent'] == 0)
                                            <p class="text-xs text-yellow-700 mt-1">
                                                @if(($analysis['generation_annual_kwh'] ?? 0) == 0 && ($analysis['consumption_annual_kwh'] ?? 0) == 0)
                                                    No se puede calcular la cobertura porque falta información de consumo y generación.
                                                @elseif(($analysis['generation_annual_kwh'] ?? 0) == 0)
                                                    No se puede calcular la cobertura porque la generación estimada es 0 kWh (revisá área útil, potencia o configuración).
                                                @elseif(($analysis['consumption_annual_kwh'] ?? 0) == 0)
                                                    No se puede calcular la cobertura porque falta información de consumo (facturas o inventario de equipos).
                                                @endif
                                            </p>
                                        @endif
                                        @if($analysis['coverage_percent'] > 0)
                                            <p class="text-xs text-purple-700 mt-1">
                                                Cobertura mensual estimada: <strong>{{ number_format($analysis['coverage_percent'], 1) }}%</strong>
                                            </p>
                                        @endif
                                    </div>

                                </div>

                                <!-- Estimación base por m² (MVP) -->
                                @if(($analysis['simple_paneles'] ?? 0) > 0)
                                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded">
                                    <div class="flex items-start">
                                        <i class="fas fa-calculator text-yellow-600 text-xl mr-3 mt-1"></i>
                                        <div>
                                            <h5 class="font-semibold text-yellow-900 mb-1">Estimación base por m²</h5>
                                            <p class="text-sm text-yellow-800 mb-2">
                                                Con una regla simple (60% de área útil y paneles de 500Wp ~2.2 m²), estimamos una potencia de
                                                <strong>{{ number_format($analysis['simple_potencia_kwp'], 2) }} kWp</strong> usando aproximadamente
                                                <strong>{{ $analysis['simple_paneles'] }} paneles</strong> en <strong>{{ number_format($analysis['simple_area_util_m2'], 1) }} m² útiles</strong>.
                                            </p>
                                            <p class="text-xs text-yellow-700">
                                                Es una aproximación inicial. El presupuesto final puede variar según orientación, sombras y estructura.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- VARIACIÓN ESTACIONAL -->
                                <div class="bg-gradient-to-r from-orange-50 to-blue-50 rounded-lg p-4 mb-6">
                                    <h5 class="font-semibold text-gray-800 mb-3">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        Variación Estacional
                                    </h5>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-sun text-orange-500 text-2xl"></i>
                                            <div>
                                                <p class="text-sm text-gray-600">Verano</p>
                                                <p class="text-lg font-bold text-orange-700">
                                                    {{ number_format($analysis['generation_summer_kwh_month'], 0) }} kWh/mes
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-snowflake text-blue-500 text-2xl"></i>
                                            <div>
                                                <p class="text-sm text-gray-600">Invierno</p>
                                                <p class="text-lg font-bold text-blue-700">
                                                    {{ number_format($analysis['generation_winter_kwh_month'], 0) }} kWh/mes
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ROI Y FINANCIERO -->
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    
                                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-indigo-700 font-medium">Inversión</span>
                                            <i class="fas fa-dollar-sign text-indigo-600"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-indigo-900">
                                            ${{ number_format($analysis['investment_ars'], 0, ',', '.') }}
                                        </p>
                                        <p class="text-xs text-indigo-600 mt-1">
                                            instalación completa
                                        </p>
                                    </div>

                                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-green-700 font-medium">Ahorro Anual</span>
                                            <i class="fas fa-piggy-bank text-green-600"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-green-900">
                                            @if($analysis['savings_annual_ars'] > 0)
                                                ${{ number_format($analysis['savings_annual_ars'], 0, ',', '.') }}
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                        <p class="text-xs text-green-600 mt-1">
                                            menos en tu factura/año
                                        </p>
                                        @if($analysis['savings_annual_ars'] > 0)
                                            <p class="text-xs text-green-700 mt-1">
                                                Ahorro mensual estimado: <strong>${{ number_format($analysis['savings_annual_ars']/12, 0, ',', '.') }}</strong>
                                            </p>
                                        @endif
                                        @if($analysis['savings_annual_ars'] == 0)
                                            <p class="text-xs text-yellow-700 mt-1">No se pudo estimar el ahorro anual porque falta información de consumo (facturas o inventario de equipos).</p>
                                        @endif
                                    </div>

                                    <div class="bg-teal-50 rounded-lg p-4 border border-teal-200">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm text-teal-700 font-medium">Payback</span>
                                            <i class="fas fa-chart-line text-teal-600"></i>
                                        </div>
                                        <p class="text-2xl font-bold text-teal-900">
                                            @if($analysis['payback_years'] > 0)
                                                {{ number_format($analysis['payback_years'], 1) }} años
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                        <p class="text-xs text-teal-600 mt-1">
                                            retorno de inversión
                                        </p>
                                        @if($analysis['payback_years'] == 0)
                                            <p class="text-xs text-yellow-700 mt-1">No se puede calcular el payback sin estimación de ahorro anual.</p>
                                        @endif
                                    </div>

                                </div>

                                <!-- GENERACIÓN DISTRIBUIDA -->
                                @if($analysis['distributed_gen_allowed'])
                                    <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6">
                                        <div class="flex items-start">
                                            <i class="fas fa-network-wired text-emerald-600 text-xl mr-3 mt-1"></i>
                                            <div>
                                                <h5 class="font-semibold text-emerald-900 mb-1">
                                                    Generación Distribuida Disponible
                                                </h5>
                                                <p class="text-sm text-emerald-700 mb-2">
                                                    <strong>{{ $analysis['distributed_gen_info']['law'] ?? 'Legislación provincial' }}</strong>
                                                </p>
                                                <p class="text-sm text-emerald-600">
                                                    Podrás inyectar excedentes a la red y compensar tu consumo.
                                                    @if($analysis['surplus_kwh'] > 0)
                                                        Excedente estimado: <strong>{{ number_format($analysis['surplus_kwh'], 0) }} kWh/año</strong> vendibles.
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="bg-gray-50 border-l-4 border-gray-400 p-4 mb-6">
                                        <div class="flex items-start">
                                            <i class="fas fa-info-circle text-gray-600 text-xl mr-3 mt-1"></i>
                                            <div>
                                                <h5 class="font-semibold text-gray-800 mb-1">
                                                    Generación Distribuida No Disponible
                                                </h5>
                                                <p class="text-sm text-gray-600">
                                                    Tu provincia aún no cuenta con legislación para inyección a la red.
                                                    La instalación solar será para autoconsumo únicamente.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- IMPACTO AMBIENTAL -->
                                <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-lg p-4 mb-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3">
                                            <i class="fas fa-leaf text-green-600 text-3xl"></i>
                                            <div>
                                                <p class="text-sm text-gray-600">CO₂ Evitado Anual</p>
                                                <p class="text-2xl font-bold text-green-800">
                                                    {{ number_format($analysis['co2_avoided_kg'], 0) }} kg
                                                </p>
                                                <p class="text-xs text-green-600 mt-1">
                                                    equivalente a {{ number_format($analysis['co2_avoided_kg'] / 21, 0) }} árboles plantados/año
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACCIONES -->
                                <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                                    <a href="{{ route('solar.configure', $entity) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                                        <i class="fas fa-cog mr-2"></i>
                                        Ajustar Configuración
                                    </a>

                                    @if(($entity->roof_area_m2 > 0) || ($entity->ground_area_m2 > 0))
                                        <a href="{{ route('solar.simulate', $entity) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                            <i class="fas fa-chart-bar mr-2"></i>
                                            Simular Escenarios
                                        </a>
                                    @else
                                        <div class="flex flex-col items-start">
                                            <button type="button" disabled
                                                class="inline-flex items-center px-4 py-2 bg-blue-300 text-white rounded-lg opacity-60 cursor-not-allowed mb-1">
                                                <i class="fas fa-chart-bar mr-2"></i>
                                                Simular Escenarios
                                            </button>
                                            <span class="text-xs text-blue-700 pl-1">Completá primero los datos de techo o terreno para habilitar la simulación.</span>
                                        </div>
                                    @endif

                                    @if($analysis['recommendation'] === 'highly_recommended' || $analysis['recommendation'] === 'recommended')
                                        <button 
                                            onclick="alert('Funcionalidad de contacto en desarrollo')"
                                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                            <i class="fas fa-phone mr-2"></i>
                                            Solicitar Presupuesto
                                        </button>
                                    @endif
                                </div>

                            @endif
                        </div>
                    </div>

                @empty
                    <div class="text-center py-12">
                        <i class="fas fa-home text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-600 text-lg mb-4">
                            No tienes propiedades registradas todavía.
                        </p>
                        <a href="{{ route('entities.create') }}" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>
                            Agregar Primera Propiedad
                        </a>
                    </div>
                @endforelse

            </div>

            <!-- INFO ADICIONAL -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-lightbulb text-blue-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h5 class="font-semibold text-blue-900 mb-2">¿Cómo se calcula el ahorro?</h5>
                        <p class="text-sm text-blue-800 mb-2">
                            Los paneles solares generan electricidad que usás directamente en tu casa.
                            Cada kWh que generás es un kWh que NO comprás de la red = menos dinero en tu factura.
                        </p>
                        <p class="text-sm text-blue-800">
                            Los cálculos se basan en radiación solar real de tu provincia (Atlas Solar Argentino),
                            tu consumo actual, y consideran orientación, sombras y legislación de generación distribuida.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
