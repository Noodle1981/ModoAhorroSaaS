<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        <i class="fas fa-{{ $equipment->equipmentType->equipmentCategory->icon ?? 'plug' }} text-blue-600 mr-2"></i>
                        {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $equipment->equipmentType->equipmentCategory->name }}
                        </span>
                        @if($equipment->location)
                            <span class="ml-2 text-gray-500">
                                <i class="fas fa-map-marker-alt"></i> {{ $equipment->location }}
                            </span>
                        @endif
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('equipment.edit', $equipment) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all">
                        <i class="fas fa-edit mr-2"></i>
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Columna Principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Información Técnica -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-cog mr-2"></i>
                            Información Técnica
                        </h2>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    <i class="fas fa-tag text-gray-400 mr-1"></i>
                                    Tipo de Equipo
                                </dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $equipment->equipmentType->name }}</dd>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                                    Potencia
                                </dt>
                                <dd class="text-lg font-semibold text-gray-900">
                                    {{ $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts }} W
                                    @if($equipment->power_watts_override)
                                        <span class="text-xs text-blue-600">(personalizado)</span>
                                    @endif
                                </dd>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    <i class="fas fa-boxes text-gray-400 mr-1"></i>
                                    Cantidad
                                </dt>
                                <dd class="text-lg font-semibold text-gray-900">{{ $equipment->quantity }} unidad(es)</dd>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4">
                                <dt class="text-sm font-medium text-gray-500 mb-1">
                                    <i class="fas fa-calendar-alt text-gray-400 mr-1"></i>
                                    Fecha de Alta
                                </dt>
                                <dd class="text-lg font-semibold text-gray-900">
                                    {{ $equipment->activated_at ? \Carbon\Carbon::parse($equipment->activated_at)->format('d/m/Y') : 'No especificada' }}
                                </dd>
                            </div>

                            @if($equipment->equipmentType->is_portable)
                                <div class="bg-green-50 rounded-lg p-4 sm:col-span-2">
                                    <dt class="text-sm font-medium text-green-700 mb-1">
                                        <i class="fas fa-suitcase-rolling text-green-600 mr-1"></i>
                                        Equipo Portátil
                                    </dt>
                                    <dd class="text-sm text-green-600">Este equipo se puede mover entre ubicaciones</dd>
                                </div>
                            @endif

                            @if($equipment->has_standby_mode)
                                <div class="bg-orange-50 rounded-lg p-4 sm:col-span-2">
                                    <dt class="text-sm font-medium text-orange-700 mb-1">
                                        <i class="fas fa-moon text-orange-600 mr-1"></i>
                                        Modo Standby Activo
                                    </dt>
                                    <dd class="text-sm text-orange-600">Este equipo consume energía en modo de espera</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                <!-- Frecuencia de Uso -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-calendar-check mr-2"></i>
                            Patrón de Uso
                        </h2>
                    </div>
                    <div class="p-6">
                        @if($equipment->is_daily_use ?? true)
                            <div class="text-center py-8">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Uso Diario</h3>
                                <p class="text-gray-600">Este equipo se utiliza todos los días de la semana</p>
                                <div class="mt-4 flex justify-center gap-2">
                                    @foreach(['L','M','X','J','V','S','D'] as $day)
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-green-500 text-white font-bold text-sm">
                                            {{ $day }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-blue-50 rounded-lg p-4 text-center">
                                        <div class="text-3xl font-bold text-blue-600">{{ $equipment->usage_days_per_week ?? 0 }}</div>
                                        <div class="text-sm text-gray-600 mt-1">días por semana</div>
                                    </div>
                                    <div class="bg-purple-50 rounded-lg p-4 text-center">
                                        <div class="text-3xl font-bold text-purple-600">{{ $equipment->minutes_per_session ?? 0 }}</div>
                                        <div class="text-sm text-gray-600 mt-1">minutos por sesión</div>
                                    </div>
                                </div>

                                @if($equipment->usage_weekdays && is_array($equipment->usage_weekdays))
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 mb-3">Días específicos de uso:</p>
                                        <div class="flex justify-center gap-2">
                                            @php
                                                $weekMap = [1=>'L',2=>'M',3=>'X',4=>'J',5=>'V',6=>'S',7=>'D'];
                                                $weekNames = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];
                                            @endphp
                                            @foreach([1,2,3,4,5,6,7] as $dayNum)
                                                @if(in_array($dayNum, $equipment->usage_weekdays))
                                                    <div class="inline-flex flex-col items-center">
                                                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-500 text-white font-bold shadow-lg" title="{{ $weekNames[$dayNum] }}">
                                                            {{ $weekMap[$dayNum] }}
                                                        </span>
                                                        <i class="fas fa-check text-green-600 text-xs mt-1"></i>
                                                    </div>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 text-gray-400 font-bold" title="{{ $weekNames[$dayNum] }}">
                                                        {{ $weekMap[$dayNum] }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @php
                                    $avgDaily = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;
                                    $weeklyMinutes = ($equipment->minutes_per_session ?? 0) * ($equipment->usage_days_per_week ?? 0);
                                @endphp
                                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg p-4 border-l-4 border-blue-500">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-gray-600">Promedio diario derivado:</p>
                                            <p class="text-2xl font-bold text-blue-600">{{ $avgDaily }} min/día</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">Uso semanal total:</p>
                                            <p class="text-xl font-bold text-purple-600">{{ $weeklyMinutes }} min/semana</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Columna Lateral -->
            <div class="space-y-6">
                
                <!-- Consumo Estimado -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                        <h2 class="text-lg font-bold text-white">
                            <i class="fas fa-chart-line mr-2"></i>
                            Consumo Estimado
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        @php
                            $power = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts ?? 0;
                            $dailyMinutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;
                            $dailyHours = $dailyMinutes / 60;
                            $dailyKwh = ($power * $dailyHours) / 1000;
                            $monthlyKwh = $dailyKwh * 30;
                            $yearlyKwh = $dailyKwh * 365;
                        @endphp

                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Diario</div>
                                <div class="text-2xl font-bold text-blue-600">{{ number_format($dailyKwh, 2) }}</div>
                                <div class="text-xs text-gray-500">kWh/día</div>
                            </div>
                        </div>

                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Mensual</div>
                                <div class="text-2xl font-bold text-purple-600">{{ number_format($monthlyKwh, 1) }}</div>
                                <div class="text-xs text-gray-500">kWh/mes</div>
                            </div>
                        </div>

                        <div class="bg-indigo-50 rounded-lg p-4">
                            <div class="text-center">
                                <div class="text-sm text-gray-600 mb-1">Anual</div>
                                <div class="text-2xl font-bold text-indigo-600">{{ number_format($yearlyKwh, 0) }}</div>
                                <div class="text-xs text-gray-500">kWh/año</div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-500 text-center">
                                <i class="fas fa-info-circle mr-1"></i>
                                Estimación basada en {{ $dailyMinutes }} min/día
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Estado y Acciones -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-600 to-gray-700 px-6 py-4">
                        <h2 class="text-lg font-bold text-white">
                            <i class="fas fa-tools mr-2"></i>
                            Acciones
                        </h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <a href="{{ route('equipment.edit', $equipment) }}" 
                           class="block w-full text-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all shadow-sm">
                            <i class="fas fa-edit mr-2"></i>
                            Editar Equipo
                        </a>
                        
                        <a href="{{ route('entities.equipment.index', $equipment->entity) }}" 
                           class="block w-full text-center px-4 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg transition-all shadow-sm">
                            <i class="fas fa-list mr-2"></i>
                            Ver Inventario
                        </a>

                        <a href="{{ route('entities.show', $equipment->entity) }}" 
                           class="block w-full text-center px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg transition-all shadow-sm">
                            <i class="fas fa-home mr-2"></i>
                            Volver a Entidad
                        </a>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-database text-gray-500 mr-1"></i>
                        Información del Sistema
                    </h3>
                    <dl class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <dt class="text-gray-600">ID:</dt>
                            <dd class="font-mono text-gray-900">{{ $equipment->id }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-600">Creado:</dt>
                            <dd class="text-gray-900">{{ $equipment->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if($equipment->updated_at != $equipment->created_at)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Actualizado:</dt>
                                <dd class="text-gray-900">{{ $equipment->updated_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
