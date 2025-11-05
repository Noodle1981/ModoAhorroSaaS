<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-chart-line mr-2 text-blue-500"></i> Dashboard General
            </h2>
            @php $plan = $user->subscription?->plan; @endphp
            @if ($plan && (is_null($plan->max_entities) || $globalSummary->entity_count < $plan->max_entities))
                <a href="{{ route('entities.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md transition">
                    <i class="fas fa-plus mr-2"></i> Nueva Entidad
                </a>
            @endif
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        
        {{-- MÉTRICAS PRINCIPALES --}}
        <div class="grid grid-cols-4 gap-3">
            {{-- Entidades --}}
            <div class="bg-blue-100 rounded-lg p-4 shadow-md border border-blue-200 hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-blue-500 text-white rounded-lg p-2">
                        <i class="fas fa-building text-lg"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-800">{{ $globalSummary->entity_count }}</p>
                </div>
                <p class="text-gray-700 text-xs font-semibold uppercase tracking-wide">Entidades</p>
            </div>

            {{-- Consumo Total --}}
            <div class="bg-orange-100 rounded-lg p-4 shadow-md border border-orange-200 hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-orange-500 text-white rounded-lg p-2">
                        <i class="fas fa-bolt text-lg"></i>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($globalSummary->total_consumption, 0) }}</p>
                        <p class="text-[10px] text-gray-600">kWh</p>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-gray-700 text-xs font-semibold uppercase tracking-wide">Consumo</p>
                    @if($globalSummary->consumption_trend != 0)
                        <span class="text-[10px] bg-orange-200 text-gray-700 px-1.5 py-0.5 rounded font-medium">
                            <i class="fas fa-{{ $globalSummary->consumption_trend > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs(round($globalSummary->consumption_trend, 1)) }}%
                        </span>
                    @endif
                </div>
            </div>

            {{-- Costo Total --}}
            <div class="bg-green-100 rounded-lg p-4 shadow-md border border-green-200 hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-green-500 text-white rounded-lg p-2">
                        <i class="fas fa-euro-sign text-lg"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-800">€{{ number_format($globalSummary->total_cost, 0) }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <p class="text-gray-700 text-xs font-semibold uppercase tracking-wide">Gasto</p>
                    @if($globalSummary->cost_trend != 0)
                        <span class="text-[10px] bg-green-200 text-gray-700 px-1.5 py-0.5 rounded font-medium">
                            <i class="fas fa-{{ $globalSummary->cost_trend > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                            {{ abs(round($globalSummary->cost_trend, 1)) }}%
                        </span>
                    @endif
                </div>
            </div>

            {{-- Equipos --}}
            <div class="bg-purple-100 rounded-lg p-4 shadow-md border border-purple-200 hover:shadow-lg transition">
                <div class="flex items-center justify-between mb-2">
                    <div class="bg-purple-500 text-white rounded-lg p-2">
                        <i class="fas fa-plug text-lg"></i>
                    </div>
                    <p class="text-3xl font-bold text-gray-800">{{ $globalSummary->equipment_count }}</p>
                </div>
                <p class="text-gray-700 text-xs font-semibold uppercase tracking-wide">Equipos</p>
            </div>
        </div>

        {{-- GRÁFICOS Y ANÁLISIS --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- Evolución Consumo --}}
            @if($recentInvoices->count() > 0)
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-line mr-2 text-blue-500"></i> Evolución del Consumo
                </h3>
                <div class="space-y-3">
                    @php 
                        $maxConsumption = $recentInvoices->max('consumption');
                    @endphp
                    @foreach($recentInvoices as $monthData)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ $monthData['month'] }}</span>
                                <span class="text-gray-600">{{ number_format($monthData['consumption'], 0) }} kWh</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all" 
                                     style="width: {{ $maxConsumption > 0 ? ($monthData['consumption'] / $maxConsumption * 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Distribución por Entidad --}}
            @if($consumptionByEntity->count() > 0)
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-building mr-2 text-purple-500"></i> Consumo por Entidad
                </h3>
                <div class="space-y-3">
                    @php 
                        $maxEntityConsumption = $consumptionByEntity->max('consumption');
                        $colors = ['from-purple-500 to-purple-600', 'from-blue-500 to-blue-600', 'from-green-500 to-green-600', 'from-yellow-500 to-yellow-600', 'from-red-500 to-red-600'];
                    @endphp
                    @foreach($consumptionByEntity as $index => $entityData)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 truncate max-w-[200px]">{{ $entityData['name'] }}</span>
                                <div class="text-right">
                                    <span class="text-gray-600">{{ number_format($entityData['consumption'], 0) }} kWh</span>
                                    <span class="text-gray-500 text-xs ml-2">€{{ number_format($entityData['cost'], 2) }}</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r {{ $colors[$index % count($colors)] }} h-3 rounded-full transition-all" 
                                     style="width: {{ $maxEntityConsumption > 0 ? ($entityData['consumption'] / $maxEntityConsumption * 100) : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- TOP EQUIPOS CONSUMIDORES --}}
        @if($topEquipment->count() > 0)
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-trophy mr-2 text-yellow-500"></i> Top 10 Equipos Consumidores (Estimado Mensual)
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Equipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden md:table-cell">Entidad</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden sm:table-cell">Ubicación</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hidden lg:table-cell">Potencia</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">kWh/mes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topEquipment as $index => $equipment)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    @if($index < 3)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold
                                            {{ $index === 0 ? 'bg-yellow-500' : ($index === 1 ? 'bg-gray-400' : 'bg-orange-600') }}">
                                            {{ $index + 1 }}
                                        </span>
                                    @else
                                        <span class="text-gray-600 font-medium">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <i class="fas fa-bolt text-yellow-500 mr-1"></i> {{ $equipment['name'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell truncate max-w-[150px]">
                                    {{ $equipment['entity'] }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 hidden sm:table-cell">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-xs">
                                        <i class="fas fa-map-marker-alt mr-1"></i> {{ $equipment['location'] ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell">
                                    {{ number_format($equipment['power']) }} W
                                </td>
                                <td class="px-4 py-3 text-sm font-bold text-right">
                                    <span class="text-orange-600">{{ number_format($equipment['monthly_kwh'], 1) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- RECOMENDACIONES ACTIVAS --}}
        @if($activeRecommendations->count() > 0)
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-200 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-lightbulb mr-2 text-green-600"></i> Recomendaciones de Ahorro Pendientes
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($activeRecommendations as $rec)
                    <div class="bg-white rounded-lg p-4 border border-green-200 hover:shadow-md transition">
                        <div class="flex items-start justify-between">
                            <h4 class="font-semibold text-gray-800 text-sm">{{ $rec->title }}</h4>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">{{ ucfirst($rec->category) }}</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-2">{{ Str::limit($rec->description, 80) }}</p>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-sm font-bold text-green-600">
                                <i class="fas fa-leaf mr-1"></i> {{ number_format($rec->estimated_annual_savings_kwh) }} kWh/año
                            </span>
                            <button class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                Ver <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- LISTA DE ENTIDADES --}}
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-building mr-2 text-blue-500"></i> Mis Entidades
                </h3>
            </div>

            @if($entities->isEmpty())
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center bg-gray-50">
                    <i class="fas fa-building text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-600 text-lg mb-4">Aún no has añadido ninguna entidad</p>
                    <a href="{{ route('entities.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition">
                        <i class="fas fa-plus mr-2"></i> Crear Mi Primera Entidad
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($entities as $entity)
                        @php
                            $supplyIds = $entity->supplies()->pluck('id');
                            $lastInvoice = \App\Models\Invoice::whereHas('contract', function($query) use ($supplyIds) {
                                    $query->whereIn('supply_id', $supplyIds);
                                })
                                ->orderBy('end_date', 'desc')
                                ->first();
                        @endphp
                        <div class="border border-gray-200 rounded-lg p-5 hover:shadow-lg hover:border-blue-300 transition group">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h4 class="font-bold text-gray-900 text-lg group-hover:text-blue-600 transition">{{ $entity->name }}</h4>
                                    <p class="text-sm text-gray-500 capitalize mt-1">
                                        <i class="fas fa-tag mr-1"></i> {{ $entity->type }}
                                    </p>
                                </div>
                                <div class="bg-blue-100 text-blue-600 rounded-full p-2">
                                    <i class="fas fa-{{ $entity->type === 'hogar' ? 'home' : 'industry' }} text-lg"></i>
                                </div>
                            </div>

                            @if($lastInvoice)
                                <div class="grid grid-cols-2 gap-3 mt-4 mb-4 text-sm">
                                    <div class="bg-yellow-50 rounded-lg p-2">
                                        <p class="text-yellow-600 text-xs font-medium">Consumo</p>
                                        <p class="text-gray-900 font-bold">{{ number_format($lastInvoice->total_energy_consumed_kwh, 0) }} kWh</p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-2">
                                        <p class="text-green-600 text-xs font-medium">Coste</p>
                                        <p class="text-gray-900 font-bold">€{{ number_format($lastInvoice->total_amount, 2) }}</p>
                                    </div>
                                </div>
                            @endif

                            <a href="{{ route('entities.show', $entity) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                Ver Dashboard <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- LÍMITE DE PLAN --}}
        @if ($plan && !is_null($plan->max_entities) && $globalSummary->entity_count >= $plan->max_entities)
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-xl p-6 text-white text-center shadow-lg">
                <i class="fas fa-exclamation-circle text-4xl mb-3 text-yellow-400"></i>
                <h3 class="text-xl font-bold mb-2">Límite de Entidades Alcanzado</h3>
                <p class="mb-4">Has alcanzado el límite de {{ $plan->max_entities }} entidades para tu plan <strong>{{ $plan->name }}</strong>.</p>
                <a href="#" class="inline-flex items-center px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold rounded-lg transition">
                    <i class="fas fa-rocket mr-2"></i> Mejorar Mi Plan
                </a>
            </div>
        @endif

    </div>
</x-app-layout>
