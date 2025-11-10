<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-clock text-blue-600 mr-3"></i>
                Optimización de Horarios: Lavarropas
            </h1>
            <a href="{{ route('recommendations.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Centro
            </a>
        </div>

        <!-- Grid de módulos de optimización -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Módulo Lavarropas (activo) -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-blue-200 {{ ($availability['laundry'] ?? false) ? '' : 'opacity-70' }}">
                <div class="px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-700 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white flex items-center"><i class="fas fa-soap mr-2"></i> Lavarropas</h2>
                    <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded">{{ ($availability['laundry'] ?? false) ? 'Activo' : 'No detectado' }}</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-600 mb-3">Calcula frecuencia óptima consolidando cargas y desplaza ciclos a horarios de menor tarifa.</p>
                    <form method="GET" action="{{ route('schedule-optimization.index') }}" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Seleccionar Equipo (Lavarropas)</label>
                        <select name="equipment_id" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" {{ ($availability['laundry'] ?? false) ? '' : 'disabled' }}>
                            <option value="">-- Elegir --</option>
                            @foreach($entities as $entity)
                                @foreach($entity->equipments as $eq)
                                    @php($name = $eq->custom_name ?? $eq->equipmentType->name)
                                    @php($lname = strtolower($name))
                                    @if(strpos($lname, 'lava') !== false)
                                        <option value="{{ $eq->id }}" {{ request('equipment_id') == $eq->id ? 'selected' : '' }}>
                                            {{ $entity->name }} - {{ $name }} (ID {{ $eq->id }})
                                        </option>
                                    @endif
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Número de personas en el hogar</label>
                            <input type="number" name="numero_personas" min="1" max="20" value="{{ request('numero_personas', ($savedProfile['numero_personas'] ?? 4)) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad del lavarropas (kg)</label>
                            <input type="number" step="0.1" name="capacidad_lavarropa_kg" min="1" max="25" value="{{ request('capacidad_lavarropa_kg', ($savedProfile['capacidad_lavarropa_kg'] ?? 8)) }}" required class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500" />
                        </div>
                        </div>
                        <div class="pt-1 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-md transition-all" {{ ($availability['laundry'] ?? false) ? '' : 'disabled' }}>
                                <i class="fas fa-calculator mr-2"></i> Calcular
                            </button>
                        </div>
                    </form>
                    @if(!($availability['laundry'] ?? false))
                        <div class="mt-3 text-[11px] text-gray-600 bg-gray-50 rounded p-2 border">
                            No encontramos un lavarropas en tu inventario. Agrega uno desde la ficha de tu entidad para habilitar esta optimización.
                        </div>
                    @endif
                    @if(isset($savedProfile) && $savedProfile && !$result)
                        <div class="mt-4 text-[11px] text-blue-700 bg-blue-50 rounded p-2">
                            Último perfil aplicado el {{ $savedProfile['applied_at'] ?? 'N/D' }} ({{ $savedProfile['frecuencia_sugerida'] }} lavados/semana).
                        </div>
                    @endif
                </div>
            </div>
            <!-- Módulo Planchado (placeholder) -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 {{ ($availability['ironing'] ?? false) ? '' : 'opacity-70' }}">
                <div class="px-5 py-3 bg-gradient-to-r from-gray-500 to-gray-600 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white flex items-center"><i class="fas fa-temperature-high mr-2"></i> Planchado</h2>
                    <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded">{{ ($availability['ironing'] ?? false) ? 'Detectado' : 'No detectado' }} · Próximamente</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-600 mb-2">Optimización del uso del plancha para concentrar sesiones y evitar sobrecalentamientos.</p>
                    <ul class="text-[11px] text-gray-500 space-y-1 list-disc pl-4">
                        <li>Consolidar prendas por lote</li>
                        <li>Evitar encendidos múltiples</li>
                        <li>Horarios fuera de pico</li>
                    </ul>
                    @if(!($availability['ironing'] ?? false))
                        <p class="text-[11px] text-gray-500 mt-2">No encontramos plancha en tu inventario.</p>
                    @endif
                </div>
            </div>
            <!-- Módulo Lavado de Autos (placeholder) -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 {{ ($availability['carwash'] ?? false) ? '' : 'opacity-70' }}">
                <div class="px-5 py-3 bg-gradient-to-r from-teal-600 to-teal-700 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white flex items-center"><i class="fas fa-car mr-2"></i> Lavado de Autos</h2>
                    <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded">{{ ($availability['carwash'] ?? false) ? 'Detectado' : 'No detectado' }} · Próximamente</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-600 mb-2">Optimiza el uso del equipo de lavado evitando horas de mayor demanda eléctrica.</p>
                    <ul class="text-[11px] text-gray-500 space-y-1 list-disc pl-4">
                        <li>Fin de semana prioridad</li>
                        <li>Programar en bloque (varios vehículos)</li>
                        <li>Evitar horario cercano a picos domésticos</li>
                    </ul>
                    @if(!($availability['carwash'] ?? false))
                        <p class="text-[11px] text-gray-500 mt-2">No encontramos hidrolavadora u equipo de lavado.</p>
                    @endif
                </div>
            </div>
            <!-- Módulo Ducha Eléctrica (placeholder) -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200 {{ ($availability['shower'] ?? false) ? '' : 'opacity-70' }} md:col-span-2 lg:col-span-1">
                <div class="px-5 py-3 bg-gradient-to-r from-orange-500 to-orange-600 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-white flex items-center"><i class="fas fa-shower mr-2"></i> Ducha Eléctrica</h2>
                    <span class="text-xs bg-white/20 text-white px-2 py-0.5 rounded">{{ ($availability['shower'] ?? false) ? 'Detectado' : 'No detectado' }} · Próximamente</span>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-600 mb-2">Reducción de picos por escalonamiento y recomendaciones de duración óptima.</p>
                    <ul class="text-[11px] text-gray-500 space-y-1 list-disc pl-4">
                        <li>Escalonar mañanas</li>
                        <li>Priorizar nocturno en invierno</li>
                        <li>Reducir minutos por sesión</li>
                    </ul>
                    @if(!($availability['shower'] ?? false))
                        <p class="text-[11px] text-gray-500 mt-2">No encontramos ducha eléctrica en tu inventario.</p>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($savedProfile) && $savedProfile && !$result)
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    Mostrando valores pre-cargados del último perfil aplicado ({{ $savedProfile['applied_at'] ?? 'N/D' }}). Puedes recalcular cambiando los campos arriba.
                </p>
            </div>
        @endif

        @if($result)
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-green-600 to-green-700">
                            <h3 class="text-lg font-semibold text-white flex items-center"><i class="fas fa-list-ol mr-2"></i> Resultado</h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-blue-50 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-600">Carga óptima</p>
                                    <p class="text-xl font-bold text-blue-600">{{ $result['carga_optima_kg'] }} kg</p>
                                </div>
                                <div class="bg-indigo-50 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-600">Ropa semanal</p>
                                    <p class="text-xl font-bold text-indigo-600">{{ $result['generacion_total_semanal_kg'] }} kg</p>
                                </div>
                                <div class="bg-purple-50 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-600">Lavados calculados</p>
                                    <p class="text-xl font-bold text-purple-600">{{ $result['lavados_optimos_calculados'] }}</p>
                                </div>
                                <div class="bg-green-50 rounded-lg p-3 text-center">
                                    <p class="text-xs text-gray-600">Frecuencia sugerida</p>
                                    <p class="text-xl font-bold text-green-600">{{ $result['frecuencia_sugerida'] }} / semana</p>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 border space-y-3">
                                <p class="text-sm text-gray-700 leading-relaxed">{{ $result['mensaje'] }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    <div class="bg-red-50 rounded p-3 text-center">
                                        <div class="text-xs text-gray-600">Costo semanal (pico)</div>
                                        <div class="text-lg font-bold text-red-600">${{ $result['ahorro']['costo_pico_semanal'] }}</div>
                                    </div>
                                    <div class="bg-green-50 rounded p-3 text-center">
                                        <div class="text-xs text-gray-600">Costo semanal (reducido)</div>
                                        <div class="text-lg font-bold text-green-600">${{ $result['ahorro']['costo_reducido_semanal'] }}</div>
                                    </div>
                                    <div class="bg-emerald-50 rounded p-3 text-center">
                                        <div class="text-xs text-gray-600">Ahorro mensual</div>
                                        <div class="text-lg font-bold text-emerald-600">${{ $result['ahorro']['mensual'] }}</div>
                                    </div>
                                    <div class="bg-emerald-50 rounded p-3 text-center">
                                        <div class="text-xs text-gray-600">Ahorro anual</div>
                                        <div class="text-lg font-bold text-emerald-600">${{ $result['ahorro']['anual'] }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h4 class="text-sm font-semibold text-gray-700">Prioridades de horario:</h4>
                                <ul class="space-y-2 text-sm">
                                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-green-600 mt-0.5"></i> <span>{{ $result['horarios']['prioridad_1'] }}</span></li>
                                    <li class="flex items-start gap-2"><i class="fas fa-check-circle text-green-600 mt-0.5"></i> <span>{{ $result['horarios']['prioridad_2'] }}</span></li>
                                    <li class="flex items-start gap-2"><i class="fas fa-times-circle text-red-500 mt-0.5"></i> <span>{{ $result['horarios']['evitar'] }}</span></li>
                                </ul>
                            </div>

                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Días recomendados (prioriza fin de semana; si no, semana post {{ \App\Services\ScheduleOptimizationService::HORA_INICIO_TARIFA_REDUCIDA_SEMANA }}hs):</h4>
                                <div class="flex flex-wrap gap-2">
                                    @php($map = [1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb',7=>'Dom'])
                            @if($equipment)
                            <div class="mt-6">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center"><i class="fas fa-exchange-alt text-blue-600 mr-2"></i> Comparación patrón actual vs recomendado</h4>
                                @php($currentDays = $equipment->usage_weekdays ?? [])
                                <div class="flex flex-wrap gap-2">
                                    @foreach([1,2,3,4,5,6,7] as $d)
                                        @php($inCurrent = in_array($d, $currentDays))
                                        @php($inRecommended = in_array($d, $result['weekdays_recomendados']))
                                        <div class="px-2 py-1 rounded text-xs flex items-center gap-1
                                            @if($result['frecuencia_sugerida'] == 1)
                                                <div class="text-green-700 font-semibold text-sm mb-2">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    ¡Felicitaciones! Usás el lavarropas solo una vez por semana, lo que es eficiente y ayuda a ahorrar energía y dinero.
                                                </div>
                                            @elseif($result['frecuencia_sugerida'] > 1)
                                                <div class="text-yellow-700 font-semibold text-sm mb-2">
                                                    <i class="fas fa-lightbulb mr-1"></i>
                                                    Te recomendamos consolidar cargas y seguir la frecuencia sugerida para maximizar el ahorro y la eficiencia.
                                                </div>
                                            @endif
                                            <ul class="text-xs text-gray-600 mb-2">
                                            <span>{{ $map[$d] }}</span>
                                            @if($inRecommended && !$inCurrent)
                                                <i class="fas fa-plus-circle"></i>
                                            @elseif($inCurrent && !$inRecommended)
                                                <i class="fas fa-exclamation-circle"></i>
                                            @elseif($inCurrent && $inRecommended)
                                                <i class="fas fa-check-circle"></i>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <p class="text-[11px] text-gray-500 mt-2">Verde: día sugerido que no usas aún. Azul: coincide. Amarillo: día que usas pero no óptimo. Gris: no usado ni sugerido.</p>
                            </div>
                            @endif
                                    @foreach([1,2,3,4,5,6,7] as $d)
                                        @if(in_array($d, $result['weekdays_recomendados']))
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-green-600 text-white text-xs font-semibold shadow">{{ $map[$d] }}</span>
                                        @else
                                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full bg-gray-200 text-gray-400 text-xs font-semibold">{{ $map[$d] }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-yellow-500 to-yellow-600">
                            <h3 class="text-lg font-semibold text-white flex items-center"><i class="fas fa-save mr-2"></i> Aplicar al Equipo</h3>
                        </div>
                        <div class="p-6">
                            <form method="POST" action="{{ route('schedule-optimization.apply') }}" class="space-y-4">
                                @csrf
                                <input type="hidden" name="equipment_id" value="{{ request('equipment_id') }}" />
                                <input type="hidden" name="numero_personas" value="{{ request('numero_personas') }}" />
                                <input type="hidden" name="capacidad_lavarropa_kg" value="{{ request('capacidad_lavarropa_kg') }}" />
                                @foreach($result['weekdays_recomendados'] as $wd)
                                    <input type="hidden" name="weekdays[]" value="{{ $wd }}" />
                                @endforeach
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Minutos por sesión (ciclo)</label>
                                    <input type="number" name="minutes_per_session" min="0" max="1440" value="{{ old('minutes_per_session', 120) }}" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-yellow-500" />
                                    <p class="mt-1 text-xs text-gray-500">Ej: lavado completo estándar (puedes ajustarlo luego en edición).</p>
                                </div>
                                <button type="submit" class="w-full px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold rounded-lg shadow-md transition-all">
                                    <i class="fas fa-check mr-2"></i> Aplicar patrón recomendado
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-600 to-gray-700">
                            <h3 class="text-lg font-semibold text-white flex items-center"><i class="fas fa-info-circle mr-2"></i> Cómo funciona</h3>
                        </div>
                        <div class="p-6 text-sm leading-relaxed text-gray-700 space-y-3">
                            <p>Consolidamos cargas para evitar lavados con poca ropa (mediocargas) y te damos días recomendados priorizando <strong>fin de semana y horario nocturno (post {{ \App\Services\ScheduleOptimizationService::HORA_INICIO_TARIFA_REDUCIDA_SEMANA }} hs)</strong>.</p>
                            <p>La frecuencia sugerida se calcula con:<br><code>frecuencia = ceil((personas * {{ \App\Services\ScheduleOptimizationService::KILOS_ROPA_PER_CAPITA_SEMANAL }}kg) / (capacidad * {{ \App\Services\ScheduleOptimizationService::FACTOR_CARGA_OPTIMA }}))</code></p>
                            <p>Si el resultado supera 6 lavados/semana, se considera prácticamente uso diario y puede evaluarse eficiencia del modelo de lavarropas.</p>
                            <p>Próximamente: algoritmos para planchado, ducha eléctrica, lavado de autos y más hábitos.</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
