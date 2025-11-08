<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-sliders-h mr-2"></i> Ajustar Uso de Equipos - {{ $entity->name }}
            </h2>
            <a href="{{ route('entities.show', $entity) }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $rooms = $equipments->pluck('location')->filter()->unique()->values();
                $categories = $equipments->map(fn($e) => optional($e->equipmentType->equipmentCategory)->name)
                    ->filter()->unique()->values();
            @endphp
            {{-- Eliminado panel lateral; se reemplaza por tarjetas arriba --}}
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-0">
                    <form method="POST" action="{{ route('snapshots.store', $invoice) }}"
                          x-data="snapshotsAdjust({ periodDays: {{ $periodDays }}, targetKwh: {{ $invoice->total_energy_consumed_kwh ?? 0 }} })" x-init="init()">
                        @csrf

                        <!-- Barra superior pegajosa con resumen y controles -->
                        <div class="sticky top-0 z-20 bg-white/95 backdrop-blur border-b px-6 pt-4 pb-3">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div>
                                    <div class="text-sm text-gray-700">
                                        <span class="font-medium">Período:</span>
                                        {{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }} ({{ $periodDays }} días)
                                    </div>
                                    <div class="text-sm text-gray-700">
                                        <span class="font-medium">Consumo real:</span> {{ number_format($invoice->total_energy_consumed_kwh, 2) }} kWh
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500">Total estimado</div>
                                    <div class="text-2xl font-bold" :class="percentInRange ? 'text-green-600' : (percent < 50 ? 'text-red-600' : 'text-yellow-600')">
                                        <span x-text="totalKwh.toFixed(2)"></span> kWh
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        <span x-text="percent.toFixed(1)"></span>% del real
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 grid grid-cols-1 md:grid-cols-12 gap-2">
                                <div class="md:col-span-4">
                                    <input type="text" placeholder="Buscar equipo o ambiente" x-model="search"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm" />
                                </div>
                                <div class="md:col-span-3">
                                    <select x-model="filterRoom" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <option value="">Todos los ambientes</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room }}">{{ $room }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-3">
                                    <select x-model="filterCategory" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <option value="">Todas las categorías</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2 flex items-center gap-2 justify-end">
                                    <button type="button" @click="toggleCompact()" class="px-2 py-1 text-xs rounded border hover:bg-gray-50">
                                        <i :class="compact ? 'fas fa-compress-alt' : 'fas fa-expand-alt'"></i>
                                        <span class="ml-1" x-text="compact ? 'Compacta' : 'Detallada'"></span>
                                    </button>
                                    <button type="button" @click="toggleHideZero()" class="px-2 py-1 text-xs rounded border hover:bg-gray-50">
                                        <i class="far fa-eye-slash"></i>
                                        <span class="ml-1">Ocultar 0 min</span>
                                    </button>
                                    <button type="button" @click="toggleSort()" class="px-2 py-1 text-xs rounded border hover:bg-gray-50">
                                        <i class="fas fa-sort-amount-down"></i>
                                        <span class="ml-1" x-text="sortByImpact ? 'Orden: Impacto' : 'Orden: Original'"></span>
                                    </button>
                                    <button type="button" @click="autoBalance()" class="px-2 py-1 text-xs rounded bg-blue-600 text-white hover:bg-blue-700">
                                        <i class="fas fa-balance-scale mr-1"></i> Autobalancear
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <!-- Panel de Recomendaciones de Standby -->
                            <div x-data="standbyRecs({ invoiceId: {{ $invoice->id }} })" class="mb-8 border border-yellow-300 rounded-lg bg-yellow-50 p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                                        <i class="fas fa-plug text-yellow-600"></i>
                                        Recomendaciones de Standby
                                    </h3>
                                    <template x-if="state === 'ready'">
                                        <button @click="apply()" class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded shadow">
                                            Aplicar Sugerencias
                                        </button>
                                    </template>
                                </div>
                                <template x-if="state === 'idle'">
                                    <div class="mt-3">
                                        <p class="text-sm text-gray-700 mb-3">Podés calcular sugerencias para desactivar standby de equipos con impacto marginal en este período.</p>
                                        <button @click="fetchRecs()" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded shadow text-sm">
                                            Calcular Recomendaciones
                                        </button>
                                    </div>
                                </template>
                                <template x-if="state === 'loading'">
                                    <div class="mt-3 text-sm text-gray-600 flex items-center gap-2">
                                        <i class="fas fa-spinner animate-spin"></i> Calculando...
                                    </div>
                                </template>
                                <template x-if="state === 'already'">
                                    <div class="mt-3 text-sm text-gray-700 bg-green-100 border border-green-300 p-3 rounded">
                                        Este período ya fue ajustado. No se generan recomendaciones.
                                    </div>
                                </template>
                                <template x-if="state === 'ready'">
                                    <div class="mt-4 overflow-x-auto">
                                        <table class="min-w-full text-xs">
                                            <thead>
                                                <tr class="bg-yellow-200 text-yellow-900">
                                                    <th class="px-2 py-1 text-left">Equipo</th>
                                                    <th class="px-2 py-1">Cat.</th>
                                                    <th class="px-2 py-1">Standby kWh</th>
                                                    <th class="px-2 py-1">Activo kWh</th>
                                                    <th class="px-2 py-1">Actual</th>
                                                    <th class="px-2 py-1">Sugerido</th>
                                                    <th class="px-2 py-1">Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <template x-for="row in recs" :key="row.id">
                                                    <tr :class="row.current_has_standby && !row.suggested_has_standby ? 'bg-red-50' : 'bg-white'" class="border-b">
                                                        <td class="px-2 py-1" x-text="row.name"></td>
                                                        <td class="px-2 py-1" x-text="row.category || '-' "></td>
                                                        <td class="px-2 py-1 text-right" x-text="row.standby_kwh_period.toFixed(3)"></td>
                                                        <td class="px-2 py-1 text-right" x-text="row.active_kwh_period.toFixed(2)"></td>
                                                        <td class="px-2 py-1 text-center">
                                                            <span :class="row.current_has_standby ? 'text-green-700' : 'text-gray-500'" x-text="row.current_has_standby ? 'Sí' : 'No'"></span>
                                                        </td>
                                                        <td class="px-2 py-1 text-center">
                                                            <span :class="row.suggested_has_standby ? 'text-green-700 font-medium' : 'text-red-700 font-semibold'" x-text="row.suggested_has_standby ? 'Sí' : 'No'"></span>
                                                        </td>
                                                        <td class="px-2 py-1" x-text="row.reason"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                        <p class="text-[11px] text-gray-500 mt-2">Las sugerencias solo modifican los checkboxes antes de guardar este período; no cambian tus equipos globalmente.</p>
                                    </div>
                                </template>
                                <template x-if="state === 'applied'">
                                    <div class="mt-3 text-sm text-blue-700 bg-blue-100 border border-blue-300 p-3 rounded">
                                        Sugerencias aplicadas. Revisá y guardá los ajustes del período.
                                    </div>
                                </template>
                            </div>
                            {{-- Tarjetas informativas responsivas --}}
                            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8">
                                {{-- Tarjeta período de facturación --}}
                                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-5 flex flex-col">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <h3 class="text-base font-semibold text-gray-800">Período de Facturación</h3>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">
                                        <span class="font-medium">Desde:</span> {{ $invoice->start_date->format('d/m/Y') }}<br>
                                        <span class="font-medium">Hasta:</span> {{ $invoice->end_date->format('d/m/Y') }}<br>
                                        <span class="font-medium">Duración:</span> {{ $periodDays }} días
                                    </p>
                                    <div class="mt-auto pt-3 border-t">
                                        <p class="text-xs text-gray-500">Esta información guía el cálculo del consumo estimado total para comparar contra el real.</p>
                                    </div>
                                </div>

                                {{-- Tarjeta de clima del período --}}
                                @if($climateSnapshot)
                                <div class="bg-white rounded-xl shadow-md border-2 border-{{ $climateSnapshot->getClimateCategoryColor() }}-200 p-5 flex flex-col">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-lg bg-{{ $climateSnapshot->getClimateCategoryColor() }}-100 flex items-center justify-center text-{{ $climateSnapshot->getClimateCategoryColor() }}-600">
                                            <i class="fas fa-cloud-sun"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-800">Clima del Período</h3>
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $climateSnapshot->getClimateCategoryColor() }}-100 text-{{ $climateSnapshot->getClimateCategoryColor() }}-700 font-medium">
                                                {{ $climateSnapshot->getClimateCategoryLabel() }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="space-y-2 text-sm text-gray-700">
                                        <div class="flex justify-between items-center">
                                            <span class="text-gray-600">Temp. promedio:</span>
                                            <span class="font-semibold text-lg">{{ $climateSnapshot->avg_temperature_c }}°C</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-500">Máxima:</span>
                                            <span class="text-red-600 font-medium">{{ $climateSnapshot->max_temperature_c }}°C</span>
                                        </div>
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-gray-500">Mínima:</span>
                                            <span class="text-blue-600 font-medium">{{ $climateSnapshot->min_temperature_c }}°C</span>
                                        </div>
                                        
                                        @if($climateSnapshot->days_above_30c > 0)
                                        <div class="mt-2 pt-2 border-t">
                                            <div class="flex items-start gap-2 bg-orange-50 rounded-lg p-2">
                                                <i class="fas fa-fire text-orange-500 text-xs mt-0.5"></i>
                                                <p class="text-xs text-orange-700">
                                                    <strong>{{ $climateSnapshot->days_above_30c }} días</strong> con >30°C. 
                                                    CDD: {{ number_format($climateSnapshot->total_cooling_degree_days, 0) }}
                                                </p>
                                            </div>
                                        </div>
                                        @endif
                                        
                                        @if($climateSnapshot->days_below_15c > 0)
                                        <div class="mt-2 pt-2 border-t">
                                            <div class="flex items-start gap-2 bg-blue-50 rounded-lg p-2">
                                                <i class="fas fa-snowflake text-blue-500 text-xs mt-0.5"></i>
                                                <p class="text-xs text-blue-700">
                                                    <strong>{{ $climateSnapshot->days_below_15c }} días</strong> con <15°C. 
                                                    HDD: {{ number_format($climateSnapshot->total_heating_degree_days, 0) }}
                                                </p>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="mt-auto pt-3 border-t">
                                        <p class="text-xs text-gray-500">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Datos de Open-Meteo para {{ $entity->locality->name }}
                                        </p>
                                    </div>
                                </div>
                                @endif

                                {{-- Tarjeta períodos similares --}}
                                @if($climateSnapshot && $similarPeriods->isNotEmpty())
                                <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-xl shadow-md border-2 border-purple-200 p-5 flex flex-col">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                                            <i class="fas fa-history"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-800">Períodos Similares</h3>
                                            <span class="text-xs text-purple-700">Clima parecido a este mes</span>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($similarPeriods->take(3) as $similar)
                                            @php
                                                $similarInvoice = $similar->invoices->first();
                                            @endphp
                                            @if($similarInvoice)
                                            <div class="bg-white/60 rounded-lg p-2 text-xs">
                                                <div class="flex justify-between items-center">
                                                    <div>
                                                        <span class="font-medium text-gray-800">
                                                            {{ $similar->period_start->format('M Y') }}
                                                        </span>
                                                        <span class="text-gray-500 ml-1">({{ $similar->avg_temperature_c }}°C)</span>
                                                    </div>
                                                    <span class="font-semibold text-purple-600">
                                                        {{ number_format($similarInvoice->total_energy_consumed_kwh, 0) }} kWh
                                                    </span>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="mt-auto pt-3 border-t border-purple-200">
                                        <p class="text-xs text-gray-600">
                                            <i class="fas fa-lightbulb mr-1 text-yellow-500"></i>
                                            Usa estos valores como referencia para ajustar el uso de equipos
                                        </p>
                                    </div>
                                </div>
                                @endif

                                {{-- Tarjeta consejos de ajuste --}}
                                <div class="bg-white rounded-xl shadow-md border border-gray-200 p-5 flex flex-col">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center text-yellow-600">
                                            <i class="fas fa-lightbulb"></i>
                                        </div>
                                        <h3 class="text-base font-semibold text-gray-800">Consejos para un Mejor Ajuste</h3>
                                    </div>
                                    <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                                        <li>Revisa facturas anteriores en meses similares.</li>
                                        <li>Considera calefacción o refrigeración según temporada.</li>
                                        <li>Standby: menor potencia pero 24h continuas.</li>
                                        <li>Objetivo ideal: 80–110% del consumo real.</li>
                                        <li>Si estás por debajo: puede faltar inventario.</li>
                                    </ul>
                                    <div class="mt-auto pt-3 border-t">
                                        <p class="text-xs text-gray-500">Ajusta minutos de uso hasta acercarte al rango óptimo.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Inventario de Equipos ({{ $equipments->count() }})
                                </h3>
                                <p class="text-sm text-gray-600">Agrupado por ambiente, categoría y tipo de uso. Ajusta minutos/día; el total se actualiza en tiempo real.</p>
                            </div>

                            @php
                                // Normalizar lista con índices para poder agrupar sin perder el index
                                $normalized = $equipments->values()->map(function($equipment, $index) use ($existingSnapshots) {
                                    $power = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
                                    $defaultMinutes = $existingSnapshots->has($equipment->id)
                                        ? round($existingSnapshots[$equipment->id]->avg_daily_use_minutes)
                                        : round($equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0);
                                    $qty = max(1, (int)($equipment->quantity ?? 1));
                                    $room = $equipment->location ?: 'Sin ubicación';
                                    $category = optional($equipment->equipmentType->equipmentCategory)->name ?: 'Otros';
                                    $hasStandby = $existingSnapshots->has($equipment->id)
                                        ? (bool)$existingSnapshots[$equipment->id]->has_standby_mode
                                        : (bool)($equipment->has_standby_mode ?? false);
                                    $continuous = $defaultMinutes >= 720; // 12h+ lo consideramos continuo
                                    $usageType = $hasStandby || $continuous ? 'Continuo / Standby' : ($defaultMinutes <= 30 ? 'Esporádico' : 'Regular');
                                    return compact('index','equipment','power','defaultMinutes','qty','room','category','usageType','hasStandby');
                                });
                                $byRoom = $normalized->groupBy('room');
                            @endphp

                            @foreach($byRoom as $room => $listByRoom)
                                @php $roomIndices = $listByRoom->pluck('index')->values()->all(); @endphp
                                <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200" x-show="roomHasVisible({{ json_encode($roomIndices) }})" x-cloak>
                                    <!-- Encabezado de la ubicación -->
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                                        <div class="flex items-center justify-between text-white">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-door-open text-2xl"></i>
                                                <h3 class="text-xl font-bold">{{ $room }}</h3>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm opacity-90">Consumo total del ambiente</div>
                                                <div class="text-2xl font-bold" x-text="roomKwhFiltered({{ json_encode($roomIndices) }}).toFixed(2) + ' kWh'"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabla de equipos -->
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Equipo
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Categoría / Tipo de uso
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Potencia
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Minutos/día
                                                    </th>
                                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Consumo período
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @php $byCat = $listByRoom->groupBy('category'); @endphp
                                                @foreach($byCat as $category => $listByCat)
                                                    @php $byUse = $listByCat->groupBy('usageType'); @endphp
                                                    @foreach($byUse as $useType => $items)
                                                        @foreach($items as $item)
                                                            @php
                                                                $equipment = $item['equipment'];
                                                                $index = $item['index'];
                                                                $power = $item['power'];
                                                                $defaultMinutes = $item['defaultMinutes'];
                                                                $qty = $item['qty'];
                                                                $categoryName = $item['category'];
                                                            @endphp
                                                            <tr class="hover:bg-gray-50 transition" data-equipment-id="{{ $equipment->id }}"
                                                                x-show="showEquipment({ name: '{{ addslashes($equipment->custom_name ?? $equipment->equipmentType->name) }}', room: '{{ addslashes($room) }}', category: '{{ addslashes($categoryName) }}', minutes: minutes[{{ $index }}] })"
                                                                :class="sortByImpact ? '' : ''"
                                                                :style="sortByImpact ? 'order:' + (-1 * Math.round(itemKwh({ power: {{ $power }}, minutes: minutes[{{ $index }}], qty: {{ $qty }} })*1000)) : ''">
                                                                
                                                                <input type="hidden" name="equipments[{{ $index }}][entity_equipment_id]" value="{{ $equipment->id }}">
                                                                
                                                                <!-- Equipo -->
                                                                <td class="px-6 py-4">
                                                                    <div class="flex items-center">
                                                                        <i class="fas fa-bolt text-yellow-500 mr-3"></i>
                                                                        <div>
                                                                            <div class="text-sm font-medium text-gray-900">
                                                                                {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
                                                                            </div>
                                                                            @if($equipment->custom_name)
                                                                                <div class="text-xs text-gray-500">{{ $equipment->equipmentType->name }}</div>
                                                                            @endif
                                                                            @if($equipment->quantity > 1)
                                                                                <div class="text-xs text-gray-500 mt-1">
                                                                                    <i class="fas fa-layer-group mr-1"></i>Cantidad: {{ $equipment->quantity }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <!-- Categoría / Tipo -->
                                                                <td class="px-6 py-4">
                                                                    <div class="flex flex-col gap-1">
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                                            <i class="fas fa-tags mr-1"></i>{{ $categoryName }}
                                                                        </span>
                                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                            <i class="far fa-clock mr-1"></i>
                                                                            <span x-text="$root.computeUsageType(minutes[{{ $index }}], hasStandby[{{ $index }}])"></span>
                                                                        </span>
                                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 text-gray-700">
                                                                            <i class="fas fa-calendar-week mr-1"></i>
                                                                            {{ $item['hasStandby'] ? '' : '' }}
                                                                            @php
                                                                                $isDaily = (bool)($equipment->is_daily_use ?? false);
                                                                                $dpw = $equipment->usage_days_per_week;
                                                                            @endphp
                                                                            {{ $isDaily ? 'Uso: diario' : ('Uso: '.($dpw ? $dpw.'/sem' : 's/def.')) }}
                                                                        </span>
                                                                    </div>
                                                                </td>

                                                                <!-- Potencia -->
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div class="text-sm text-gray-900">
                                                                        <i class="fas fa-plug text-gray-400 mr-1"></i>{{ number_format($power) }} W
                                                                    </div>
                                                                </td>

                                                                <!-- Minutos/día con slider y +/- -->
                                                                <td class="px-6 py-4">
                                                                    <div class="space-y-2">
                                                                        <div class="flex flex-wrap items-center gap-2">
                                                                            <button type="button"
                                                                                    @click="minutes[{{ $index }}] = Math.max(0, Number(minutes[{{ $index }}]||0) - 5); $dispatch('input')"
                                                                                    class="px-2 py-1 text-xs rounded border hover:bg-gray-50">
                                                                                –5
                                                                            </button>
                                                                            <div class="flex-1 min-w-[180px]">
                                                                                <input type="range"
                                                                                       min="0" max="1440" step="5"
                                                                                       x-model.number="minutes[{{ $index }}]"
                                                                                       class="w-full accent-blue-600">
                                                                            </div>
                                                                            <button type="button"
                                                                                    @click="minutes[{{ $index }}] = Math.min(1440, Number(minutes[{{ $index }}]||0) + 5); $dispatch('input')"
                                                                                    class="px-2 py-1 text-xs rounded border hover:bg-gray-50">
                                                                                +5
                                                                            </button>
                                                                            <div class="sm:ml-2 sm:basis-auto basis-full flex items-center gap-1 sm:justify-start justify-end">
                                                                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs font-medium tabular-nums min-w-[100px] justify-center" x-text="formatMinutes(minutes[{{ $index }}]||0)"></span>
                                                                            </div>
                                                                        </div>
                                                                        <!-- Campo real para backend -->
                                                                        <input type="hidden" name="equipments[{{ $index }}][avg_daily_use_minutes]" :value="minutes[{{ $index }}]">
                                                                        <!-- Checkbox Standby por período -->
                                                                        <div class="pt-1 flex flex-col gap-1" x-data>
                                                                            <input type="checkbox" id="standby_{{ $index }}" data-standby-checkbox
                                                                                   name="equipments[{{ $index }}][has_standby_mode]"
                                                                                   x-model="hasStandby[{{ $index }}]"
                                                                                   :value="1"
                                                                                   disabled
                                                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 opacity-60 cursor-not-allowed">
                                                                            <label for="standby_{{ $index }}" class="text-xs text-gray-600 flex items-center gap-1">
                                                                                <span>Standby global</span>
                                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px] font-medium" title="Se modifica solo en Centro de Gestión de Standby">
                                                                                    <i class="fas fa-lock mr-1"></i> Solo lectura
                                                                                </span>
                                                                            </label>
                                                                            @php
                                                                                $isDaily = (bool)($equipment->is_daily_use ?? false);
                                                                                $dpw = $equipment->usage_days_per_week;
                                                                                $weekdays = is_array($equipment->usage_weekdays ?? null) ? $equipment->usage_weekdays : [];
                                                                                $wdMap = [1=>'Lu',2=>'Ma',3=>'Mi',4=>'Ju',5=>'Vi',6=>'Sa',7=>'Do'];
                                                                                $wdList = collect($weekdays)->map(fn($n) => $wdMap[$n] ?? $n)->implode(', ');
                                                                                $freqText = $isDaily ? 'Diario (7/7)' : ($dpw ? ($dpw.' días/sem') : ($wdList ? ('Días: '.$wdList) : 'Sin definir'));
                                                                            @endphp
                                                                            <div class="text-[11px] text-gray-600 flex items-center gap-1">
                                                                                <i class="fas fa-calendar-week text-gray-400"></i>
                                                                                <span class="font-medium">Frecuencia:</span>
                                                                                <span>{{ $freqText }}</span>
                                                                                <span class="inline-flex items-center px-1 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px] font-medium ml-1">
                                                                                    <i class="fas fa-lock mr-1"></i> Solo lectura
                                                                                </span>
                                                                                <a href="{{ route('usage.index') }}" class="ml-2 text-[10px] text-blue-700 hover:underline" title="Editar en Gestión de Uso" target="_blank">gestionar uso</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </td>

                                                                <!-- Consumo -->
                                                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                                                    <div class="text-sm font-semibold text-gray-900">
                                                                        <span x-text="itemKwh({ power: {{ $power }}, minutes: minutes[{{ $index }}], qty: {{ $qty }} }).toFixed(2)"></span> kWh
                                                                    </div>
                                                                    <div class="text-xs text-gray-500" x-text="((itemKwh({ power: {{ $power }}, minutes: minutes[{{ $index }}], qty: {{ $qty }} }) / (totalKwh || 1)) * 100).toFixed(1) + '%'"></div>
                                                                </td>

                                                                <!-- Estado inicial de minutos -->
                                                                <input type="hidden" x-init="setInitial({{ $index }}, {{ $power }}, {{ $qty }}, {{ $defaultMinutes }}, { name: '{{ addslashes($equipment->custom_name ?? $equipment->equipmentType->name) }}', room: '{{ addslashes($room) }}', category: '{{ addslashes($categoryName) }}', hasStandby: {{ $item['hasStandby'] ? 'true' : 'false' }} })">
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Resumen visual por ambiente --}}
                            <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                                    <div class="flex items-center gap-3 text-white">
                                        <i class="fas fa-chart-bar text-2xl"></i>
                                        <h3 class="text-xl font-bold">Distribución de Consumo por Ambiente</h3>
                                    </div>
                                </div>

                                <div class="p-6">
                                    <p class="text-sm text-gray-600 mb-4">
                                        Visualiza qué ambientes representan mayor porcentaje del consumo total.
                                    </p>

                                    <div class="space-y-4">
                                        @foreach($byRoom as $room => $listByRoom)
                                            @php $roomIndices = $listByRoom->pluck('index')->values()->all(); @endphp
                                            <div x-show="roomHasVisible({{ json_encode($roomIndices) }})" x-cloak>
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-door-open text-gray-400"></i>
                                                        <span class="text-sm font-medium text-gray-700">{{ $room }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-sm font-semibold text-gray-900" x-text="roomKwhFiltered({{ json_encode($roomIndices) }}).toFixed(2) + ' kWh'"></span>
                                                        <span class="text-xs text-gray-500" x-text="'(' + ((roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100).toFixed(1) + '%)'"></span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                                                        <div
                                                            class="h-full rounded-full transition-all duration-500 ease-in-out"
                                                            :class="{
                                                                'bg-gradient-to-r from-red-400 to-red-600': (roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100 >= 30,
                                                                'bg-gradient-to-r from-yellow-400 to-yellow-600': (roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100 >= 15 && (roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100 < 30,
                                                                'bg-gradient-to-r from-green-400 to-green-600': (roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100 >= 5 && (roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100 < 15,
                                                                'bg-gradient-to-r from-blue-400 to-blue-600': (roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100 < 5
                                                            }"
                                                            :style="'width: ' + Math.max(2, ((roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100)) + '%'"
                                                        ></div>
                                                    </div>

                                                    <span class="text-xs font-semibold text-gray-700 tabular-nums" x-text="((roomKwhFiltered({{ json_encode($roomIndices) }}) / (totalKwhFiltered || 1)) * 100).toFixed(1) + '%'"></span>
                                                </div>
                                            </div>
                                        @endforeach

                                        <div class="flex items-start gap-3">
                                            <i class="fas fa-lightbulb text-blue-500 text-lg mt-0.5"></i>
                                            <div class="text-sm text-blue-700">
                                                <strong>Interpretación:</strong>
                                                <ul class="list-disc list-inside mt-2 space-y-1">
                                                    <li><span class="font-semibold text-red-600">Rojo (≥30%)</span>: Ambiente de mayor consumo - revisa equipos de alta potencia.</li>
                                                    <li><span class="font-semibold text-yellow-600">Amarillo (15-29%)</span>: Consumo medio-alto - busca oportunidades de optimización.</li>
                                                    <li><span class="font-semibold text-green-600">Verde (5-14%)</span>: Consumo moderado - mantén hábitos eficientes.</li>
                                                    <li><span class="font-semibold text-blue-600">Azul (&lt;5%)</span>: Bajo consumo - uso eficiente de energía.</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Botones --}}
                            <div class="mt-6 flex items-center justify-end gap-3">
                                <a href="{{ route('entities.show', $entity) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                                    <i class="fas fa-times mr-2"></i> Cancelar
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                    <i class="fas fa-save mr-2"></i> Guardar Ajustes
                                </button>
                            </div>
                        </div>

                        {{-- Mensaje informativo --}}
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-400 text-lg"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700">
                                        <strong>Objetivo:</strong> Ajusta las horas para que el total estimado se acerque al consumo real ({{ number_format($invoice->total_energy_consumed_kwh, 2) }} kWh).
                                        Un buen ajuste está entre 80-110% del consumo real. El estado de <span class="font-semibold">standby</span> es informativo aquí y se gestiona de forma centralizada.
                                        Para cambiarlo, visitá el <a href="{{ route('standby.index') }}" class="underline font-medium text-yellow-800 hover:text-yellow-900">Centro de Gestión de Standby</a>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Panel de consejos movido arriba en tarjeta --}}
        </div>
    </div>

    <script>
        function snapshotsAdjust({ periodDays, targetKwh }) {
            return {
                // Estado UI
                search: '',
                filterRoom: '',
                filterCategory: '',
                sortByImpact: true,
                compact: true,
                hideZero: false,

                // Datos dinámicos
                periodDays: periodDays || 30,
                targetKwh: targetKwh || 0,
                minutes: {},
                hasStandby: {},
                items: [], // {index, name, room, category, power, qty}

                init() {
                    // Recolectar items desde el DOM (cada input hidden x-init setInitial)
                    this.recomputeTotal();
                },

                setInitial(index, power, qty, mins, meta = {}) {
                    this.$nextTick(() => {
                        this.minutes[index] = Number(mins || 0);
                        this.items.push({ index, power: Number(power || 0), qty: Number(qty || 1), ...meta });
                        this.hasStandby[index] = !!meta.hasStandby;
                        this.recomputeTotal();
                    });
                },

                itemKwh({ power, minutes, qty }) {
                    const kwhDay = (power / 1000) * ((Number(minutes)||0) / 60);
                    return (kwhDay * this.periodDays) * (qty || 1);
                },

                get totalKwh() {
                    return this.items.reduce((sum, it) => sum + this.itemKwh({ power: it.power, minutes: this.minutes[it.index], qty: it.qty }), 0);
                },
                get totalKwhFiltered() {
                    // Suma sólo los ítems visibles según los filtros activos
                    return this.items.reduce((sum, it) => {
                        const visible = this.showEquipment({ name: it.name || '', room: it.room || '', category: it.category || '', minutes: this.minutes[it.index] });
                        if (!visible) return sum;
                        return sum + this.itemKwh({ power: it.power, minutes: this.minutes[it.index], qty: it.qty });
                    }, 0);
                },
                get percent() {
                    if (!this.targetKwh || this.targetKwh <= 0) return 0;
                    return (this.totalKwh / this.targetKwh) * 100;
                },
                get percentInRange() {
                    return this.percent >= 80 && this.percent <= 110;
                },

                toggleCompact() { this.compact = !this.compact; },
                toggleHideZero() { this.hideZero = !this.hideZero; },
                toggleSort() { this.sortByImpact = !this.sortByImpact; },

                recomputeTotal() {
                    // getter totalKwh recalcula, aquí no necesitamos más
                },

                formatMinutes(mins) {
                    const m = Number(mins || 0);
                    if (m < 60) return m + ' min';
                    const hrs = Math.floor(m / 60);
                    const restMins = m % 60;
                    if (restMins === 0) return hrs + ' hrs';
                    return hrs + ' hrs ' + restMins + ' min';
                },

                autoBalance() {
                    if (!this.targetKwh || this.targetKwh <= 0) return;
                    const current = this.totalKwh;
                    if (current <= 0) return;
                    const factor = this.targetKwh / current;
                    Object.keys(this.minutes).forEach(k => {
                        const v = Math.max(0, Math.min(1440, Math.round((this.minutes[k] || 0) * factor)));
                        this.minutes[k] = v;
                        // También refleja en el input real para enviar al backend
                        const input = this.$el.querySelector(`input[name="equipments[${k}][avg_daily_use_minutes]"]`);
                        if (input) input.value = v;
                    });
                },

                showEquipment({ name, room, category, minutes }) {
                    const text = (name + ' ' + (room||'') + ' ' + (category||'')).toLowerCase();
                    const match = !this.search || text.includes(this.search.toLowerCase());
                    const roomOk = !this.filterRoom || (room === this.filterRoom);
                    const catOk = !this.filterCategory || (category === this.filterCategory);
                    const zeroOk = !this.hideZero || (Number(minutes||0) > 0);
                    return match && roomOk && catOk && zeroOk;
                },

                // Mostrar/ocultar por ambiente segun filtros activos
                roomHasVisible(indices) {
                    const ids = indices || [];
                    if (ids.length === 0) return false;
                    // Mientras inicializa (antes de setInitial), no esconder ambientes
                    if (this.items.length === 0) return true;
                    return ids.some(idx => {
                        const it = this.getItem(idx);
                        if (!it) return true; // si aún no está inicializado el item, mantener visible
                        return this.showEquipment({ name: it.name || '', room: it.room || '', category: it.category || '', minutes: this.minutes[idx] });
                    });
                },

                // Utilidades extra
                getItem(index) {
                    return this.items.find(it => it.index === index);
                },
                computeUsageType(mins, standbyFlag) {
                    const m = Number(mins||0);
                    if (standbyFlag) return 'Continuo / Standby';
                    if (m >= 720) return 'Continuo';
                    if (m <= 30) return 'Esporádico';
                    return 'Regular';
                },
                roomKwh(indices) {
                    return (indices||[]).reduce((sum, idx) => {
                        const it = this.getItem(idx);
                        if (!it) return sum;
                        return sum + this.itemKwh({ power: it.power, minutes: this.minutes[idx], qty: it.qty });
                    }, 0);
                },
                roomKwhFiltered(indices) {
                    return (indices||[]).reduce((sum, idx) => {
                        const it = this.getItem(idx);
                        if (!it) return sum;
                        const visible = this.showEquipment({ name: it.name || '', room: it.room || '', category: it.category || '', minutes: this.minutes[idx] });
                        if (!visible) return sum;
                        return sum + this.itemKwh({ power: it.power, minutes: this.minutes[idx], qty: it.qty });
                    }, 0);
                },
            }
        }

        // Componente para recomendaciones de standby per-invoice
        function standbyRecs({ invoiceId }) {
            return {
                state: 'idle', // idle | loading | ready | applied | already
                recs: [],
                fetchRecs() {
                    this.state = 'loading';
                    fetch(`/standby/recommendations/${invoiceId}`)
                        .then(r => r.json())
                        .then(json => {
                            if (json.status === 'already_adjusted') {
                                this.state = 'already';
                                return;
                            }
                            this.recs = json.equipments || [];
                            this.state = 'ready';
                        })
                        .catch(() => {
                            this.state = 'idle';
                            alert('Error al obtener recomendaciones de standby');
                        });
                },
                apply() {
                    // Marca/desmarca checkboxes en función de sugerencias
                    this.recs.forEach(r => {
                        const cb = document.querySelector(`input[type=checkbox][name=\"equipments[${r.id}]\"]`);
                    });
                    // Los checkboxes reales están dentro del formulario con name equipments[index][has_standby_mode]
                    // Necesitamos mapear por data-id (agregamos atributo en cada bloque de equipo)
                    this.recs.forEach(r => {
                        const wrapper = document.querySelector(`[data-equipment-id='${r.id}']`);
                        if (!wrapper) return;
                        const standbyInput = wrapper.querySelector("input[type='checkbox'][data-standby-checkbox]");
                        if (!standbyInput) return;
                        // Si la sugerencia dice false y actualmente está marcado → desmarcar
                        if (!r.suggested_has_standby && standbyInput.checked) {
                            standbyInput.checked = false;
                            standbyInput.dispatchEvent(new Event('change'));
                        }
                        // Si la sugerencia dice true y no está marcado → marcar
                        if (r.suggested_has_standby && !standbyInput.checked) {
                            standbyInput.checked = true;
                            standbyInput.dispatchEvent(new Event('change'));
                        }
                    });
                    this.state = 'applied';
                }
            }
        }
    </script>
</x-app-layout>