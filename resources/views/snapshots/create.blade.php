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
            {{-- Información del período de facturación --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Período de Facturación:</strong> 
                            {{ $invoice->start_date->format('d/m/Y') }} al {{ $invoice->end_date->format('d/m/Y') }} 
                            ({{ $periodDays }} días)
                        </p>
                        </div>
            
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
                            <div class="mb-4">
                                <h3 class="text-lg font-medium text-gray-900">
                                    Inventario de Equipos ({{ $equipments->count() }})
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Agrupado por ambiente, categoría y tipo de uso. Ajusta minutos/día; el total se actualiza en tiempo real.
                                </p>
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
                                    $hasStandby = (bool)($equipment->has_standby_mode ?? false);
                                    $continuous = $defaultMinutes >= 720; // 12h+ lo consideramos continuo
                                    $usageType = $hasStandby || $continuous ? 'Continuo / Standby' : ($defaultMinutes <= 30 ? 'Esporádico' : 'Regular');
                                    return compact('index','equipment','power','defaultMinutes','qty','room','category','usageType','hasStandby');
                                });
                                $byRoom = $normalized->groupBy('room');
                            @endphp

                            @foreach($byRoom as $room => $listByRoom)
                                @php $roomIndices = $listByRoom->pluck('index')->values()->all(); @endphp
                                <div class="mt-8 bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                                    <!-- Encabezado de la ubicación -->
                                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                                        <div class="flex items-center justify-between text-white">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-door-open text-2xl"></i>
                                                <h3 class="text-xl font-bold">{{ $room }}</h3>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm opacity-90">Consumo total del ambiente</div>
                                                <div class="text-2xl font-bold" x-text="$root.roomKwh({{ json_encode($roomIndices) }}).toFixed(2) + ' kWh'"></div>
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
                                                            <tr class="hover:bg-gray-50 transition"
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
                                                                            <span x-text="$root.computeUsageType(minutes[{{ $index }}], {{ $equipment->has_standby_mode ? 'true' : 'false' }})"></span>
                                                                        </span>
                                                                    </div>
                                                                </td>

                                                                <!-- Potencia -->
                                                                <td class="px-6 py-4 whitespace-nowrap">
                                                                    <div class="text-sm text-gray-900">
                                                                        <i class="fas fa-plug text-gray-400 mr-1"></i>{{ number_format($power) }} W
                                                                    </div>
                                                                </td>

                                                                <!-- Minutos/día -->
                                                                <td class="px-6 py-4">
                                                                    <div class="flex items-center gap-2">
                                                                        <input
                                                                            type="number"
                                                                            name="equipments[{{ $index }}][avg_daily_use_minutes]"
                                                                            x-model.number="minutes[{{ $index }}]"
                                                                            min="0" max="1440" step="1"
                                                                            class="w-24 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                                            :class="minutes[{{ $index }}] > 0 ? 'bg-white' : 'bg-gray-50'"
                                                                            required
                                                                        >
                                                                        <span class="text-xs text-gray-500">min</span>
                                                                    </div>
                                                                    <div class="text-xs text-gray-400 mt-1" x-show="!compact">
                                                                        (<span x-text="(minutes[{{ $index }}] / 60).toFixed(1)"></span> hs)
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
                                                                <input type="hidden" x-init="setInitial({{ $index }}, {{ $power }}, {{ $qty }}, {{ $defaultMinutes }}, { name: '{{ addslashes($equipment->custom_name ?? $equipment->equipmentType->name) }}', room: '{{ addslashes($room) }}', category: '{{ addslashes($categoryName) }}', hasStandby: {{ $equipment->has_standby_mode ? 'true' : 'false' }} })">
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
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <i class="fas fa-door-open text-gray-400"></i>
                                                        <span class="text-sm font-medium text-gray-700">{{ $room }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <span class="text-sm font-semibold text-gray-900" x-text="$root.roomKwh({{ json_encode($roomIndices) }}).toFixed(2) + ' kWh'"></span>
                                                        <span class="text-xs text-gray-500" x-text="'(' + (($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100).toFixed(1) + '%)'"></span>
                                                    </div>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-6 overflow-hidden">
                                                    <div 
                                                        class="h-full rounded-full transition-all duration-500 ease-in-out flex items-center justify-end pr-2"
                                                        :class="{
                                                            'bg-gradient-to-r from-red-400 to-red-600': ($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100 >= 30,
                                                            'bg-gradient-to-r from-yellow-400 to-yellow-600': ($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100 >= 15 && ($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100 < 30,
                                                            'bg-gradient-to-r from-green-400 to-green-600': ($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100 >= 5 && ($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100 < 15,
                                                            'bg-gradient-to-r from-blue-400 to-blue-600': ($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100 < 5
                                                        }"
                                                        :style="'width: ' + Math.max(2, (($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100)) + '%'">
                                                        <span class="text-xs font-semibold text-white" x-show="($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100 >= 5" x-text="(($root.roomKwh({{ json_encode($roomIndices) }}) / (totalKwh || 1)) * 100).toFixed(1) + '%'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
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
                                        Un buen ajuste está entre 80-110% del consumo real.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Ayuda adicional --}}
            <div class="mt-6 bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">
                    <i class="fas fa-lightbulb text-yellow-500 mr-2"></i> Consejos para un mejor ajuste:
                </h4>
                <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside ml-4">
                    <li>Revisa tus facturas anteriores para ver patrones de consumo estacionales</li>
                    <li>Considera el uso de equipos de calefacción/refrigeración según la temporada</li>
                    <li>Equipos en standby consumen menos, pero de forma continua (24 horas)</li>
                    <li>Un ajuste entre 80-110% indica que tu inventario está completo y bien calibrado</li>
                    <li>Si no llegas al consumo real, puede que falten equipos en tu inventario</li>
                </ul>
            </div>
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
                items: [], // {index, name, room, category, power, qty}

                init() {
                    // Recolectar items desde el DOM (cada input hidden x-init setInitial)
                    this.recomputeTotal();
                },

                setInitial(index, power, qty, mins, meta = {}) {
                    this.$nextTick(() => {
                        this.minutes[index] = Number(mins || 0);
                        this.items.push({ index, power: Number(power || 0), qty: Number(qty || 1), ...meta });
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

                // Utilidades extra
                getItem(index) {
                    return this.items.find(it => it.index === index);
                },
                computeUsageType(mins, hasStandby) {
                    const m = Number(mins||0);
                    if (hasStandby) return 'Continuo / Standby';
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
            }
        }
    </script>
</x-app-layout>