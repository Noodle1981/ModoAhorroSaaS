<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-chart-line mr-2 text-blue-500"></i> Panel de Entidades
            </h2>
            @php $plan = $user->subscription?->plan; @endphp
            @if ($plan && (is_null($plan->max_entities) || $entitiesData->count() < $plan->max_entities))
                @if (\Illuminate\Support\Facades\Route::has('entities.create'))
                    <a href="{{ route('entities.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md transition">
                        <i class="fas fa-plus mr-2"></i> Nueva Entidad
                    </a>
                @endif
            @endif
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-10">
        {{-- Estado vacío --}}
        @if($entitiesData->isEmpty())
            <div class="bg-white rounded-xl shadow-md p-12">
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center bg-gray-50">
                    <i class="fas fa-building text-6xl text-gray-300 mb-6"></i>
                    <p class="text-gray-700 text-lg mb-6">Aún no has añadido ninguna entidad. Crea tu primera y comienza el análisis automático.</p>
                    @if (\Illuminate\Support\Facades\Route::has('entities.create'))
                        <a href="{{ route('entities.create') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition">
                            <i class="fas fa-plus mr-2"></i> Crear Mi Primera Entidad
                        </a>
                    @endif
                </div>
            </div>
        @else
            {{-- Entidades activas --}}
            <div class="grid gap-8">
                @foreach($entitiesData as $data)
                    @php 
                        $entity = $data['entity'];
                        $iconMap = [
                            'hogar' => 'fa-home',
                            'comercio' => 'fa-store',
                            'oficina' => 'fa-building',
                            'industria' => 'fa-industry',
                        ];
                        $icon = $iconMap[$entity->type] ?? 'fa-building';
                        $monthlyEvolution = $data['monthly_evolution'];
                        $maxConsumption = ($monthlyEvolution->max('consumption') ?? 0) > 0 ? $monthlyEvolution->max('consumption') : 1;
                        $currentConsumption = $data['consumption'];
                        $meterPercent = min(100, round(($currentConsumption / $maxConsumption) * 100));
                        $trend = round($data['trend'], 1);
                        $trendPositive = $trend > 5; // >5% subida se marca rojo
                        $trendNegative = $trend < -5; // <-5% bajada se marca verde
                    @endphp
                    <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
                        <div class="px-6 pt-6 flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600">
                                    <i class="fas {{ $icon }} text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-800">{{ $entity->name }}</h3>
                                    <p class="text-xs text-gray-500 uppercase tracking-wide">Tipo: {{ ucfirst($entity->type ?? 'N/D') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    <i class="fas fa-bolt mr-1"></i> Monitorizado
                                </span>
                                @if($trendPositive)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700"><i class="fas fa-arrow-up mr-1"></i>{{ $trend }}%</span>
                                @elseif($trendNegative)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700"><i class="fas fa-arrow-down mr-1"></i>{{ abs($trend) }}%</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700"><i class="fas fa-minus mr-1"></i>{{ $trend }}%</span>
                                @endif
                            </div>
                        </div>

                        {{-- Métricas principales --}}
                        <div class="px-6 mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100">
                                <p class="text-xs font-medium text-blue-600 mb-1">Consumo Mes</p>
                                <p class="text-lg font-bold text-blue-700">{{ number_format($data['consumption'],0,'','.') }} kWh</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100">
                                <p class="text-xs font-medium text-indigo-600 mb-1">Costo Estimado</p>
                                <p class="text-lg font-bold text-indigo-700">$ {{ number_format($data['cost'],0,'','.') }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-teal-50 to-teal-100">
                                <p class="text-xs font-medium text-teal-600 mb-1">Equipos</p>
                                <p class="text-lg font-bold text-teal-700">{{ $data['equipment_count'] }}</p>
                            </div>
                            <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100">
                                <p class="text-xs font-medium text-amber-600 mb-1">Categorías</p>
                                <p class="text-lg font-bold text-amber-700">{{ $data['consumption_by_category']->count() }}</p>
                            </div>
                        </div>

                        {{-- Grillas secundarias --}}
                        <div class="px-6 py-8 grid md:grid-cols-3 gap-8">
                            {{-- Evolución 6 meses --}}
                            <div>
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 flex items-center"><i class="fas fa-chart-bar text-blue-500 mr-2"></i>Evolución 6 meses</h4>
                                <div class="flex items-end space-x-2 h-40">
                                    @foreach($monthlyEvolution as $m)
                                        @php $height = $maxConsumption > 0 ? round(($m['consumption'] / $maxConsumption) * 100) : 0; @endphp
                                        <div class="flex flex-col items-center justify-end w-8">
                                            <div class="w-6 bg-gradient-to-t from-blue-600 to-blue-400 rounded-t-md" style="height: {{ $height }}%;"></div>
                                            <span class="mt-1 text-[10px] text-gray-500">{{ $m['month'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Medidor circular --}}
                            <div class="flex flex-col items-center">
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 flex items-center"><i class="fas fa-circle-notch text-indigo-500 mr-2"></i>Nivel de Consumo</h4>
                                <div class="relative w-40 h-40">
                                    @php $radius = 64; $circumference = 2 * pi() * $radius; $offset = $circumference - ($meterPercent/100) * $circumference; @endphp
                                    <svg viewBox="0 0 160 160" class="transform -rotate-90">
                                        <circle cx="80" cy="80" r="{{ $radius }}" stroke="#E5E7EB" stroke-width="14" fill="none" />
                                        <circle cx="80" cy="80" r="{{ $radius }}" stroke="#2563EB" stroke-width="14" fill="none"
                                                stroke-linecap="round" stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}" />
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <p class="text-2xl font-bold text-gray-800">{{ $meterPercent }}%</p>
                                        <p class="text-xs text-gray-500">vs máx 6m</p>
                                    </div>
                                </div>
                                <p class="mt-3 text-xs text-gray-500 text-center">Consumo actual comparado con el máximo de los últimos 6 meses.</p>
                            </div>

                            {{-- Consumo por categoría --}}
                            <div>
                                <h4 class="text-sm font-semibold text-gray-600 mb-3 flex items-center"><i class="fas fa-layer-group text-teal-500 mr-2"></i>Consumo por categoría</h4>
                                <div class="space-y-3">
                                    @foreach($data['consumption_by_category']->take(6) as $cat)
                                        @php $percent = $data['consumption'] > 0 ? round(($cat['consumption'] / $data['consumption']) * 100) : 0; @endphp
                                        <div>
                                            <div class="flex justify-between text-xs font-medium text-gray-600 mb-1">
                                                <span>{{ $cat['category'] }}</span>
                                                <span>{{ number_format($cat['consumption'],0,'','.') }} kWh</span>
                                            </div>
                                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full bg-teal-500" style="width: {{ $percent }}%;"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($data['consumption_by_category']->isEmpty())
                                        <p class="text-xs text-gray-500">Sin datos de equipos aún.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="px-6 pb-6 flex justify-end">
                            @if (\Illuminate\Support\Facades\Route::has('entities.show'))
                                <a href="{{ route('entities.show',$entity) }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-lg bg-gray-800 hover:bg-gray-900 text-white transition">
                                    <i class="fas fa-arrow-right mr-2"></i> Ver Detalle
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Entidades bloqueadas (freemium / upsell) --}}
        @if(!empty($blockedEntities))
            <div class="mt-4">
                <h3 class="text-sm font-semibold text-gray-500 mb-3 uppercase tracking-wide">Más tipos de entidades (Premium)</h3>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($blockedEntities as $be)
                        <div class="relative bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl p-6 border border-gray-300 shadow-inner overflow-hidden group">
                            <div class="absolute inset-0 backdrop-blur-[2px] bg-white/40"></div>
                            <div class="relative flex items-center space-x-3">
                                <div class="w-12 h-12 rounded-xl bg-gray-300 flex items-center justify-center text-gray-600">
                                    <i class="fas {{ $be['icon'] }} text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-700">{{ $be['name'] }}</h4>
                                    <p class="text-xs text-gray-500">Disponible en Plan Premium</p>
                                </div>
                            </div>
                            <div class="mt-5 space-y-2 text-xs text-gray-500">
                                <p><i class="fas fa-lock mr-1"></i>Métricas avanzadas</p>
                                <p><i class="fas fa-lock mr-1"></i>Perfiles de uso</p>
                                <p><i class="fas fa-lock mr-1"></i>Optimización inteligente</p>
                            </div>
                            <div class="mt-5">
                                <button class="w-full inline-flex items-center justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold cursor-not-allowed opacity-70">
                                    <i class="fas fa-crown mr-2"></i> Próximamente
                                </button>
                            </div>
                            <div class="absolute top-3 right-3 bg-gray-700 text-white text-[10px] font-semibold px-2 py-1 rounded">LOCKED</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
