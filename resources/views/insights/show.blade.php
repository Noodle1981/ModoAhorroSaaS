<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-chart-line mr-2 text-purple-600"></i>
                Insights Inteligentes - {{ $entity->name }}
            </h2>
            <a href="{{ route('entities.show', $entity) }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Mensaje si no hay datos suficientes --}}
            @if(!$correlation['success'] && !$prediction['success'])
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-yellow-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="font-semibold text-yellow-800">Datos insuficientes para análisis completo</h3>
                        <p class="text-sm text-yellow-700 mt-1">
                            Necesitas al menos 3 facturas con consumo registrado y datos climáticos para activar todos los insights.
                            <a href="{{ route('contracts.invoices.create', $entity->supplies->first()->contracts->first() ?? 1) }}" class="underline font-medium">Agregar facturas</a>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Fila 1: Análisis de Correlación + Predicción --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- Gráfico de Correlación Clima-Consumo --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-chart-scatter text-blue-600 mr-2"></i>
                                Correlación Clima-Consumo
                            </h3>
                            @if($correlation['success'])
                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $correlation['correlation']['coefficient'] > 0.7 ? 'bg-red-100 text-red-800' : ($correlation['correlation']['coefficient'] < -0.7 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $correlation['correlation']['strength'] }}
                            </span>
                            @endif
                        </div>

                        @if($correlation['success'])
                        <div style="height: 300px;">
                            <canvas id="correlationChart"></canvas>
                        </div>
                        
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                {{ $correlation['correlation']['interpretation'] }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                <strong>R² = {{ $correlation['correlation']['coefficient'] }}</strong> | 
                                {{ $correlation['summary']['total_periods'] }} períodos analizados
                            </p>
                        </div>
                        @else
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-chart-scatter text-6xl mb-3"></i>
                            <p>{{ $correlation['message'] ?? 'Sin datos disponibles' }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Gráfico de Predicción --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-crystal-ball text-purple-600 mr-2"></i>
                                Predicción Próximos 14 Días
                            </h3>
                            @if($prediction['success'])
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                {{ $prediction['summary']['total_predicted_kwh'] }} kWh
                            </span>
                            @endif
                        </div>

                        @if($prediction['success'])
                        <div style="height: 300px;">
                            <canvas id="predictionChart"></canvas>
                        </div>
                        
                        <div class="mt-4 p-3 bg-purple-50 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <i class="fas fa-lightbulb text-purple-500 mr-1"></i>
                                {{ $prediction['model']['interpretation'] }}
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                <strong>Promedio diario:</strong> {{ $prediction['summary']['avg_daily_kwh'] }} kWh
                            </p>
                        </div>
                        @else
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-crystal-ball text-6xl mb-3"></i>
                            <p>{{ $prediction['message'] ?? 'Sin datos disponibles' }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Fila 2: Perfil Climático + Alertas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- Perfil Climático --}}
                @if($correlation['success'])
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas {{ $correlation['climate_profile']['icon'] }} text-{{ $correlation['climate_profile']['color'] }}-600 mr-2"></i>
                            Tu Perfil Climático
                        </h3>

                        <div class="space-y-4">
                            <div class="text-center py-6">
                                <div class="inline-block p-6 rounded-full bg-{{ $correlation['climate_profile']['color'] }}-100">
                                    <i class="fas {{ $correlation['climate_profile']['icon'] }} text-5xl text-{{ $correlation['climate_profile']['color'] }}-600"></i>
                                </div>
                                <h4 class="mt-4 text-xl font-bold text-gray-900">
                                    {{ ucfirst(str_replace('_', ' ', $correlation['climate_profile']['profile'])) }}
                                </h4>
                                <p class="text-sm text-gray-600 mt-2 max-w-md mx-auto">
                                    {{ $correlation['climate_profile']['description'] }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-orange-50 rounded-lg text-center">
                                    <i class="fas fa-fan text-orange-500 mb-2"></i>
                                    <div class="text-2xl font-bold text-orange-700">{{ $correlation['climate_profile']['cooling_ratio'] }}%</div>
                                    <div class="text-xs text-gray-600">Refrigeración</div>
                                </div>
                                <div class="p-4 bg-blue-50 rounded-lg text-center">
                                    <i class="fas fa-fire text-blue-500 mb-2"></i>
                                    <div class="text-2xl font-bold text-blue-700">{{ $correlation['climate_profile']['heating_ratio'] }}%</div>
                                    <div class="text-xs text-gray-600">Calefacción</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Alertas Activas --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-bell text-yellow-600 mr-2"></i>
                                Alertas Activas
                            </h3>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                {{ $alerts->count() }}
                            </span>
                        </div>

                        @if($alerts->isNotEmpty())
                        <div class="space-y-3 max-h-96 overflow-y-auto">
                            @foreach($alerts as $alert)
                            <div class="flex items-start gap-3 p-3 border border-{{ $alert->color_class }}-200 bg-{{ $alert->color_class }}-50 rounded-lg">
                                <i class="fas {{ $alert->icon }} text-{{ $alert->color_class }}-600 mt-1"></i>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm text-gray-900">{{ $alert->title }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">{{ $alert->description }}</p>
                                    <p class="text-xs text-gray-400 mt-2">{{ $alert->created_at->diffForHumans() }}</p>
                                </div>
                                <button onclick="dismissAlert({{ $alert->id }})" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-check-circle text-6xl mb-3 text-green-400"></i>
                            <p class="font-medium text-gray-600">¡Todo en orden!</p>
                            <p class="text-sm mt-1">No tienes alertas activas</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Fila 3: Comparativa + Recomendaciones --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- Comparativa de Períodos --}}
                @if($comparison['success'])
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                            Comparativa de Períodos
                        </h3>

                        <div style="height: 250px;">
                            <canvas id="comparisonChart"></canvas>
                        </div>

                        <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                            <div class="p-3 bg-blue-50 rounded-lg">
                                <div class="text-xs text-gray-600">Actual</div>
                                <div class="text-lg font-bold text-blue-700">{{ number_format($comparison['current']['kwh'], 0) }}</div>
                                <div class="text-xs text-gray-500">{{ $comparison['current']['period'] }}</div>
                            </div>
                            @if($comparison['previous'])
                            <div class="p-3 bg-gray-50 rounded-lg">
                                <div class="text-xs text-gray-600">Anterior</div>
                                <div class="text-lg font-bold text-gray-700">{{ number_format($comparison['previous']['kwh'], 0) }}</div>
                                <div class="text-xs {{ $comparison['previous']['change_percent'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $comparison['previous']['change_percent'] > 0 ? '+' : '' }}{{ $comparison['previous']['change_percent'] }}%
                                </div>
                            </div>
                            @endif
                            @if($comparison['year_ago'])
                            <div class="p-3 bg-purple-50 rounded-lg">
                                <div class="text-xs text-gray-600">Año pasado</div>
                                <div class="text-lg font-bold text-purple-700">{{ number_format($comparison['year_ago']['kwh'], 0) }}</div>
                                <div class="text-xs {{ $comparison['year_ago']['change_percent'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $comparison['year_ago']['change_percent'] > 0 ? '+' : '' }}{{ $comparison['year_ago']['change_percent'] }}%
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                {{-- Recomendaciones --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            Recomendaciones Inteligentes
                        </h3>

                        @if(count($recommendations) > 0)
                        <div class="space-y-3">
                            @foreach($recommendations as $rec)
                            <div class="p-4 border-l-4 border-{{ $rec['priority'] === 'high' ? 'red' : 'yellow' }}-400 bg-gray-50 rounded-r-lg">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-sm text-gray-900">{{ $rec['title'] }}</h4>
                                        <p class="text-xs text-gray-600 mt-1">{{ $rec['description'] }}</p>
                                    </div>
                                    @if(isset($rec['potential_saving_percent']))
                                    <span class="ml-3 px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded">
                                        -{{ $rec['potential_saving_percent'] }}%
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-12 text-gray-400">
                            <i class="fas fa-thumbs-up text-6xl mb-3"></i>
                            <p>No hay recomendaciones por ahora</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Gráfico de Correlación
        @if($correlation['success'] && count($correlation['data_points']) > 0)
        const correlationCtx = document.getElementById('correlationChart').getContext('2d');
        new Chart(correlationCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Consumo vs Temperatura',
                    data: @json(array_map(fn($p) => ['x' => $p['avg_temp'], 'y' => $p['kwh_per_day']], $correlation['data_points'])),
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (context) => `${context.parsed.x}°C → ${context.parsed.y.toFixed(2)} kWh/día`
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: 'Temperatura (°C)' } },
                    y: { title: { display: true, text: 'Consumo (kWh/día)' } }
                }
            }
        });
        @endif

        // Gráfico de Predicción
        @if($prediction['success'])
        const predictionCtx = document.getElementById('predictionChart').getContext('2d');
        new Chart(predictionCtx, {
            type: 'line',
            data: {
                labels: @json(array_column($prediction['predictions'], 'date')),
                datasets: [{
                    label: 'kWh Predicho',
                    data: @json(array_column($prediction['predictions'], 'predicted_kwh')),
                    borderColor: 'rgba(147, 51, 234, 1)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { title: { display: true, text: 'kWh/día' } }
                }
            }
        });
        @endif

        // Gráfico de Comparativa
        @if($comparison['success'])
        const comparisonCtx = document.getElementById('comparisonChart').getContext('2d');
        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: @json(array_column($comparison['last_12_months'], 'period')),
                datasets: [{
                    label: 'kWh',
                    data: @json(array_column($comparison['last_12_months'], 'kwh')),
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { title: { display: true, text: 'kWh' } }
                }
            }
        });
        @endif

        // Función para descartar alertas
        function dismissAlert(alertId) {
            if (!confirm('¿Descartar esta alerta?')) return;
            
            fetch(`/api/alerts/${alertId}/dismiss`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(() => location.reload());
        }
    </script>
</x-app-layout>
