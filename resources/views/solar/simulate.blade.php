<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-chart-bar mr-2"></i>
            Simulación de Escenarios - {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <i class="fas fa-sliders-h text-blue-500 mr-2"></i>
                        Comparación de Escenarios
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Compara diferentes tamaños de instalación para elegir el que mejor se adapte a tus necesidades y presupuesto.
                    </p>
                </div>

                <!-- Tabla Comparativa -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Métrica
                                </th>
                                @foreach($scenarios as $scenario)
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider
                                        {{ $scenario['percent'] == 100 ? 'bg-blue-100' : '' }}">
                                        {{ $scenario['percent'] }}%
                                        @if($scenario['percent'] == 100)
                                            <br><span class="text-blue-600">(Máximo)</span>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            
                            <!-- Área utilizada -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-ruler-combined text-blue-500 mr-2"></i>
                                    Área Utilizada (m²)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        {{ number_format($scenario['analysis']['total_usable_m2'] ?? 0, 1) }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Potencia -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                                    Potencia (kWp)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        {{ number_format($scenario['analysis']['total_kwp'] ?? 0, 2) }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Generación anual -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-sun text-orange-500 mr-2"></i>
                                    Generación Anual (kWh)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        {{ number_format($scenario['analysis']['generation_annual_kwh'], 0) }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Cobertura -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-percentage text-purple-500 mr-2"></i>
                                    Cobertura de Consumo (%)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-semibold {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        <span class="
                                            {{ $scenario['analysis']['coverage_percent'] >= 80 ? 'text-green-700' : '' }}
                                            {{ $scenario['analysis']['coverage_percent'] >= 50 && $scenario['analysis']['coverage_percent'] < 80 ? 'text-blue-700' : '' }}
                                            {{ $scenario['analysis']['coverage_percent'] < 50 ? 'text-orange-700' : '' }}
                                        ">
                                            {{ number_format(max($scenario['analysis']['coverage_percent'], 0), 1) }}%
                                        </span>
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Inversión -->
                            <tr class="hover:bg-gray-50 bg-yellow-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-dollar-sign text-green-600 mr-2"></i>
                                    Inversión Inicial (ARS)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        ${{ number_format($scenario['analysis']['investment_ars'], 0, ',', '.') }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Ahorro anual -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-piggy-bank text-green-600 mr-2"></i>
                                    Ahorro Anual (ARS)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        ${{ number_format($scenario['analysis']['savings_annual_ars'], 0, ',', '.') }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Payback -->
                            <tr class="hover:bg-gray-50 bg-green-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-chart-line text-teal-600 mr-2"></i>
                                    Payback (años)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center font-bold {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        <span class="
                                            {{ $scenario['analysis']['payback_years'] <= 7 ? 'text-green-700' : '' }}
                                            {{ $scenario['analysis']['payback_years'] > 7 && $scenario['analysis']['payback_years'] <= 10 ? 'text-blue-700' : '' }}
                                            {{ $scenario['analysis']['payback_years'] > 10 ? 'text-orange-700' : '' }}
                                        ">
                                            {{ number_format($scenario['analysis']['payback_years'], 1) }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>

                            <!-- CO2 evitado -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-leaf text-green-600 mr-2"></i>
                                    CO₂ Evitado Anual (kg)
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        {{ number_format($scenario['analysis']['co2_avoided_kg'], 0) }}
                                    </td>
                                @endforeach
                            </tr>

                            <!-- Recomendación -->
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <i class="fas fa-star text-yellow-500 mr-2"></i>
                                    Recomendación
                                </td>
                                @foreach($scenarios as $scenario)
                                    <td class="px-6 py-4 text-sm text-center {{ $scenario['percent'] == 100 ? 'bg-blue-50' : '' }}">
                                        @switch($scenario['analysis']['recommendation'])
                                            @case('highly_recommended')
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-star"></i> Ideal
                                                </span>
                                                @break
                                            @case('recommended')
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-thumbs-up"></i> Bueno
                                                </span>
                                                @break
                                            @case('consider')
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                                    <i class="fas fa-info-circle"></i> Viable
                                                </span>
                                                @break
                                            @default
                                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                                                    Parcial
                                                </span>
                                        @endswitch
                                    </td>
                                @endforeach
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Gráfico Visual (placeholder) -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-chart-area text-blue-500 mr-2"></i>
                    Comparación Visual
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Cobertura -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3">Cobertura de Consumo</h4>
                        <div class="space-y-3">
                            @foreach($scenarios as $scenario)
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-600">{{ $scenario['percent'] }}%</span>
                                        <span class="font-semibold text-gray-900">
                                            {{ number_format($scenario['analysis']['coverage_percent'], 1) }}%
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div 
                                            class="h-4 rounded-full transition-all
                                                {{ $scenario['analysis']['coverage_percent'] >= 80 ? 'bg-green-500' : '' }}
                                                {{ $scenario['analysis']['coverage_percent'] >= 50 && $scenario['analysis']['coverage_percent'] < 80 ? 'bg-blue-500' : '' }}
                                                {{ $scenario['analysis']['coverage_percent'] < 50 ? 'bg-orange-500' : '' }}
                                            "
                                            style="width: {{ min($scenario['analysis']['coverage_percent'], 100) }}%"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Payback -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-3">Tiempo de Retorno</h4>
                        <div class="space-y-3">
                            @foreach($scenarios as $scenario)
                                @php
                                    $maxYears = 15;
                                    $widthPercent = min(($scenario['analysis']['payback_years'] / $maxYears) * 100, 100);
                                @endphp
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-600">{{ $scenario['percent'] }}%</span>
                                        <span class="font-semibold text-gray-900">
                                            {{ number_format($scenario['analysis']['payback_years'], 1) }} años
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div 
                                            class="h-4 rounded-full transition-all
                                                {{ $scenario['analysis']['payback_years'] <= 7 ? 'bg-green-500' : '' }}
                                                {{ $scenario['analysis']['payback_years'] > 7 && $scenario['analysis']['payback_years'] <= 10 ? 'bg-blue-500' : '' }}
                                                {{ $scenario['analysis']['payback_years'] > 10 ? 'bg-orange-500' : '' }}
                                            "
                                            style="width: {{ $widthPercent }}%"
                                        ></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            <!-- Resumen y Recomendación -->
            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                    Nuestra Recomendación
                </h3>
                
                @php
                    $bestScenario = collect($scenarios)->sortBy(function($s) {
                        // Priorizar: payback < 10 años y mayor cobertura
                        if ($s['analysis']['payback_years'] <= 10) {
                            return -$s['analysis']['coverage_percent'];
                        }
                        return $s['analysis']['payback_years'];
                    })->first();
                @endphp

                <div class="bg-white rounded-lg p-6 border-2 border-blue-300">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-xl font-bold text-blue-900">
                            Escenario {{ $bestScenario['percent'] }}%
                        </h4>
                        <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>
                            Óptimo
                        </span>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <p class="text-sm text-gray-600">Potencia</p>
                            <p class="text-lg font-bold text-gray-900">
                                {{ number_format($bestScenario['analysis']['total_kwp'] ?? 0, 2) }} kWp
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Cobertura</p>
                            <p class="text-lg font-bold text-green-700">
                                {{ number_format($bestScenario['analysis']['coverage_percent'] ?? 0, 1) }}%
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Inversión</p>
                            <p class="text-lg font-bold text-gray-900">
                                ${{ number_format($bestScenario['analysis']['investment_ars'] ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Payback</p>
                            <p class="text-lg font-bold text-blue-700">
                                {{ number_format($bestScenario['analysis']['payback_years'] ?? 0, 1) }} años
                            </p>
                        </div>
                    </div>

                    <p class="text-gray-700">
                        Este escenario ofrece el mejor balance entre inversión, cobertura de consumo y retorno financiero.
                        Generarías <strong>{{ number_format($bestScenario['analysis']['generation_annual_kwh'], 0) }} kWh/año</strong>
                        y ahorrarías <strong>${{ number_format($bestScenario['analysis']['savings_annual_ars'], 0, ',', '.') }}/año</strong>.
                    </p>
                </div>
            </div>

            <!-- Acciones -->
            <div class="flex justify-between">
                <a href="{{ route('solar.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al Análisis
                </a>

                <div class="space-x-3">
                    <a href="{{ route('solar.configure', $entity) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        <i class="fas fa-cog mr-2"></i>
                        Ajustar Configuración
                    </a>

                    <button 
                        onclick="alert('Funcionalidad de presupuesto en desarrollo')"
                        class="inline-flex items-center px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        <i class="fas fa-phone mr-2"></i>
                        Solicitar Presupuesto
                    </button>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
