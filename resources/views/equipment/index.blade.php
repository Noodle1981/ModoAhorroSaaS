<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    ⚡ Inventario de Equipos
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="font-medium text-blue-600">{{ $entity->name }}</span> • {{ $equipments->count() }} equipos registrados
                </p>
            </div>
            <a href="{{ route('entities.show', $entity) }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-600 to-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:from-gray-700 hover:to-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150 shadow-md">
                <svg class="w-4 h-4 mr-2" style="height: 16px; width: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8" style="background: linear-gradient(to bottom, #f0f9ff 0%, #ffffff 100%);">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Estadísticas Rápidas --}}
            @if ($equipments->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-5 text-white transform hover:scale-105 transition-transform duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-lg p-3 backdrop-blur-sm">
                            <svg class="h-6 w-6 text-white" style="height: 24px; width: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-blue-100">Total Equipos</p>
                            <p class="text-3xl font-bold">{{ $equipments->count() }}</p>
                        </div>
                    </div>
                </div>                    <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg shadow-lg p-5 text-white transform hover:scale-105 transition-transform duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-lg p-3 backdrop-blur-sm">
                                <svg class="h-6 w-6 text-white" style="height: 24px; width: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-amber-100">Potencia Total</p>
                                <p class="text-3xl font-bold">
                                    {{ number_format($equipments->sum(fn($eq) => ($eq->power_watts_override ?? $eq->equipmentType->default_power_watts) * ($eq->quantity ?? 1))) }} W
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg shadow-lg p-5 text-white transform hover:scale-105 transition-transform duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-lg p-3 backdrop-blur-sm">
                                <svg class="h-6 w-6 text-white" style="height: 24px; width: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-emerald-100">Categorías</p>
                                <p class="text-3xl font-bold">
                                    {{ $equipments->pluck('equipmentType.equipmentCategory.name')->unique()->count() }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg shadow-lg p-5 text-white transform hover:scale-105 transition-transform duration-200">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-white bg-opacity-30 rounded-lg p-3 backdrop-blur-sm">
                                <svg class="h-6 w-6 text-white" style="height: 24px; width: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-purple-100">Consumo Est. Diario</p>
                                <p class="text-3xl font-bold">
                                    @php
                                        $dailyKwh = $equipments->sum(function($eq) {
                                            $power = ($eq->power_watts_override ?? $eq->equipmentType->default_power_watts);
                                            $minutes = ($eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes ?? 0);
                                            $qty = $eq->quantity ?? 1;
                                            return ($power / 1000) * ($minutes / 60) * $qty;
                                        });
                                    @endphp
                                    {{ number_format($dailyKwh, 1) }} kWh
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Botón para añadir equipo --}}
            <div class="flex justify-between items-center mb-6 bg-white rounded-lg shadow-md p-4 border-l-4 border-blue-500">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <span class="mr-2">📋</span> Equipos Registrados
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">Gestiona tu inventario de equipamiento eléctrico</p>
                </div>
                <a href="{{ route('entities.equipment.create', $entity) }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-green-700 hover:to-green-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150 shadow-lg transform hover:scale-105">
                    <svg class="w-5 h-5 mr-2" style="height: 20px; width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Añadir Equipo
                </a>
            </div>

            {{-- Contenedor principal --}}
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-lg border border-gray-200">
                @if ($equipments->isEmpty())
                    <div class="text-center py-20 px-6 bg-gradient-to-b from-blue-50 to-white">
                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 mb-6 shadow-inner">
                            <svg class="h-10 w-10 text-blue-600" style="height: 40px; width: 40px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l.707-.707M6.343 17.657l-.707.707m12.728 0l.707.707M12 21v-1m0-16a8 8 0 100 16 8 8 0 000-16z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">No hay equipos en tu inventario</h3>
                        <p class="text-sm text-gray-600 mb-8 max-w-sm mx-auto">
                            Comienza añadiendo los equipos eléctricos de tu entidad para analizar su consumo y optimizar tu eficiencia energética.
                        </p>
                        <a href="{{ route('entities.equipment.create', $entity) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-semibold text-sm text-white hover:from-green-700 hover:to-green-800 transition shadow-lg transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" style="height: 20px; width: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Añadir Primer Equipo
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gradient-to-r from-blue-600 to-blue-700">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Equipo
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Categoría
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Ubicación
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Potencia
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Uso Diario
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        Consumo/Día
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($equipments as $equipment)
                                    @php
                                        $power = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
                                        $minutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0;
                                        $qty = $equipment->quantity ?? 1;
                                        $dailyKwh = ($power / 1000) * ($minutes / 60) * $qty;
                                        $equipmentName = $equipment->custom_name ?? $equipment->equipmentType->name;
                                    @endphp
                                    <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent transition-all duration-200 {{ $loop->even ? 'bg-gray-50' : 'bg-white' }}">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-br from-blue-100 to-blue-200 rounded-lg flex items-center justify-center shadow-sm">
                                                    <svg class="h-6 w-6 text-blue-600" style="height: 24px; width: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $equipmentName }}
                                                    </div>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $equipment->equipmentType->name }}
                                                        @if($qty > 1)
                                                            <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800">
                                                                × {{ $qty }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 shadow-sm">
                                                {{ $equipment->equipmentType->equipmentCategory->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700">
                                            @if($equipment->location)
                                                <div class="flex items-center">
                                                    @if($equipment->location === 'Portátiles')
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-700 border border-blue-300 shadow-sm">
                                                            📱 Portátil
                                                        </span>
                                                    @else
                                                        <svg class="w-4 h-4 mr-1 text-green-500" style="height: 16px; width: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        <span class="font-medium">{{ $equipment->location }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-xs">Sin ubicación</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-amber-700">{{ number_format($power) }} W</div>
                                            @if($equipment->has_standby_mode)
                                                <div class="text-xs text-orange-600 flex items-center mt-1 font-medium">
                                                    <svg class="w-3 h-3 mr-1" style="height: 12px; width: 12px;" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Standby
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm font-semibold text-indigo-700">
                                            {{ number_format($minutes / 60, 1) }} h/día
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-bold text-green-700 bg-green-50 px-3 py-1 rounded-lg inline-block">
                                                {{ number_format($dailyKwh, 2) }} kWh
                                            </div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                ~{{ number_format($dailyKwh * 30, 1) }} kWh/mes
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <div class="flex items-center justify-center space-x-2">
                                                <a href="{{ route('equipment.show', $equipment) }}" 
                                                   class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-semibold rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-150 shadow-md transform hover:scale-105"
                                                   title="Ver detalles">
                                                    <svg class="w-4 h-4" style="height: 16px; width: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('equipment.edit', $equipment) }}" 
                                                   class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-xs font-semibold rounded-lg hover:from-amber-600 hover:to-orange-700 transition-all duration-150 shadow-md transform hover:scale-105"
                                                   title="Editar">
                                                    <svg class="w-4 h-4" style="height: 16px; width: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                                <form action="{{ route('equipment.destroy', $equipment) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este equipo?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-xs font-semibold rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-150 shadow-md transform hover:scale-105"
                                                            title="Eliminar">
                                                        <svg class="w-4 h-4" style="height: 16px; width: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Resumen Final --}}
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-between items-center text-sm">
                            <span class="font-medium text-gray-700">
                                Total de {{ $equipments->count() }} {{ Str::plural('equipo', $equipments->count()) }}
                            </span>
                            <span class="text-gray-600">
                                Consumo total estimado: <strong class="text-green-600">{{ number_format($equipments->sum(fn($eq) => (($eq->power_watts_override ?? $eq->equipmentType->default_power_watts) / 1000) * (($eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes ?? 0) / 60) * ($eq->quantity ?? 1)), 2) }} kWh/día</strong>
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>