<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sun mr-2"></i>
            Panel Solar - Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @foreach($solarData as $data)
                <div class="bg-white rounded-lg shadow-lg p-6">
                    
                    <!-- Header de la instalación -->
                    <div class="flex items-center justify-between mb-6 pb-4 border-b">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">
                                <i class="fas fa-solar-panel text-yellow-500 mr-2"></i>
                                {{ $data->installation->entity->name }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Capacidad: {{ number_format($data->installation->installed_kwp, 2) }} kWp
                                @if($data->installation->installation_date)
                                    • Instalado: {{ \Carbon\Carbon::parse($data->installation->installation_date)->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                        <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                            <i class="fas fa-check-circle mr-1"></i>
                            Operativo
                        </span>
                    </div>

                    <!-- KPIs -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-orange-700 font-medium">Hoy</span>
                                <i class="fas fa-sun text-orange-500"></i>
                            </div>
                            <p class="text-3xl font-bold text-orange-900">
                                {{ number_format($data->kpis->today, 1) }}
                            </p>
                            <p class="text-xs text-orange-700 mt-1">kWh generados</p>
                        </div>

                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-blue-700 font-medium">Este Mes</span>
                                <i class="fas fa-calendar-alt text-blue-500"></i>
                            </div>
                            <p class="text-3xl font-bold text-blue-900">
                                {{ number_format($data->kpis->this_month, 0) }}
                            </p>
                            <p class="text-xs text-blue-700 mt-1">kWh generados</p>
                        </div>

                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-green-700 font-medium">Total Acumulado</span>
                                <i class="fas fa-chart-line text-green-500"></i>
                            </div>
                            <p class="text-3xl font-bold text-green-900">
                                {{ number_format($data->kpis->total, 0) }}
                            </p>
                            <p class="text-xs text-green-700 mt-1">kWh generados</p>
                        </div>

                    </div>

                    <!-- Mensaje de ahorro -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-4 mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-piggy-bank text-green-600 text-2xl mr-3 mt-1"></i>
                            <div>
                                <h5 class="font-semibold text-green-900 mb-1">
                                    Ahorro en tu Factura
                                </h5>
                                <p class="text-sm text-green-800">
                                    Con {{ number_format($data->kpis->this_month, 0) }} kWh generados este mes, 
                                    <strong>estás pagando menos en tu factura de luz</strong>. 
                                    Cada kWh que generás es un kWh que no comprás de la red eléctrica.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico (placeholder simple) -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="font-semibold text-gray-800 mb-4">
                            <i class="fas fa-chart-area text-blue-500 mr-2"></i>
                            Producción Últimos 30 Días
                        </h4>
                        
                        @if($data->chartData->labels->isNotEmpty())
                            <div class="space-y-2">
                                @foreach($data->chartData->labels as $index => $label)
                                    @php
                                        $value = $data->chartData->data[$index] ?? 0;
                                        $maxValue = $data->chartData->data->max() ?: 1;
                                        $widthPercent = ($value / $maxValue) * 100;
                                    @endphp
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs text-gray-600 w-12">{{ $label }}</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-6 relative overflow-hidden">
                                            <div 
                                                class="bg-gradient-to-r from-yellow-400 to-orange-500 h-6 rounded-full transition-all"
                                                style="width: {{ $widthPercent }}%"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-700 w-16 text-right">
                                            {{ number_format($value, 1) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">
                                <i class="fas fa-info-circle mr-2"></i>
                                No hay datos de producción en los últimos 30 días
                            </p>
                        @endif
                    </div>

                    <!-- Acciones -->
                    <div class="mt-6 pt-4 border-t flex justify-between items-center">
                        <a href="{{ route('entities.show', $data->installation->entity) }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <i class="fas fa-arrow-right mr-1"></i>
                            Ver Propiedad
                        </a>
                        <button 
                            onclick="alert('Funcionalidad de detalle en desarrollo')"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-sm">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Ver Detalle
                        </button>
                    </div>

                </div>
            @endforeach

            <!-- Botón para analizar más -->
            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            ¿Más propiedades con potencial solar?
                        </h3>
                        <p class="text-gray-600 text-sm">
                            Analiza el potencial de instalación solar en tus otras propiedades
                        </p>
                    </div>
                    <a href="{{ route('solar.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                        <i class="fas fa-search mr-2"></i>
                        Analizar Potencial
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
