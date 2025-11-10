<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-calendar-alt mr-2"></i> Gestión de Uso (Días/Semana)
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="mb-4 p-3 bg-yellow-100 text-yellow-700 rounded">{{ session('warning') }}</div>
            @endif

            <div class="bg-white shadow rounded-lg p-6">
                @if($invoice)
                    <div class="mb-5 border border-indigo-200 bg-indigo-50 rounded-lg p-4 flex flex-col gap-1">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-file-invoice text-indigo-600 text-xl"></i>
                            <h3 class="text-sm font-semibold text-indigo-700 m-0">Ajustando Período Seleccionado</h3>
                        </div>
                        <p class="text-xs text-indigo-700 m-0">
                            Factura #{{ $invoice->id }} • {{ $invoice->start_date->format('d/m/Y') }} – {{ $invoice->end_date->format('d/m/Y') }}
                            ({{ $invoice->start_date->diffInDays($invoice->end_date) + 1 }} días)
                        </p>
                        @if(!$confirmedAt)
                            <p class="text-[11px] text-yellow-700 mt-1 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                Primero confirmá la gestión de uso para habilitar el ajuste del período.
                            </p>
                        @else
                            <p class="text-[11px] text-green-700 mt-1 flex items-center gap-1">
                                <i class="fas fa-check-circle"></i>
                                Frecuencia confirmada: ya podés volver y ajustar minutos en este período.
                                <a href="{{ route('snapshots.create', $invoice) }}" class="underline text-green-800 hover:text-green-900 ml-2" title="Ir a Ajustar">Ir a Ajustar →</a>
                            </p>
                        @endif
                    </div>
                @endif
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-sliders-h text-blue-600"></i>
                        Frecuencia de Uso por Equipo
                    </h3>
                    @if(!$confirmedAt)
                        <form method="POST" action="{{ route('usage.confirm') }}">
                            @csrf
                            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded shadow">
                                <i class="fas fa-check mr-1"></i> Confirmar Gestión de Uso
                            </button>
                        </form>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-medium">
                            <i class="fas fa-lock mr-1"></i> Confirmado
                        </span>
                    @endif
                </div>

                <p class="text-sm text-gray-600 mb-4">Definí cuántos días por semana se usa cada equipo. Esto mejora el cálculo real del período. Tras confirmar, podrás aplicar recomendaciones. Esta configuración impacta en todos los períodos futuros y en el seleccionado si aún no está ajustado.</p>
                <div class="mb-6 p-5 bg-blue-100 border-2 border-blue-400 rounded-xl text-base text-blue-900 shadow-md flex items-start gap-3">
                    <div class="pt-1"><i class="fas fa-info-circle text-2xl"></i></div>
                    <div>
                        <strong>Climatización y Calefón:</strong><br>
                        El sistema aplica automáticamente un margen del <strong>25%</strong> menos de días para compensar la sobreestimación típica en verano/invierno.<br>
                        Además, se usan <strong>datos reales de temperatura</strong> de la API Open-Meteo para ajustar el cálculo según los días realmente calurosos o fríos del período.<br>
                        <span class="text-sm text-blue-700">Esto permite estimaciones más precisas aunque no recuerdes los minutos exactos de uso.</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('usage.equipment.bulk') }}" x-data="usageManager()">
                    @csrf
                    @if($invoice)
                        <input type="hidden" name="invoice" value="{{ $invoice->id }}">
                    @endif
                    @php
                        $byEntity = $equipments->groupBy(fn($e) => $e->entity?->name ?? 'Entidad');
                    @endphp
                    @foreach($byEntity as $entityName => $listByEntity)
                        <div class="mb-5">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-building text-gray-500"></i>
                                <h4 class="text-sm font-semibold text-gray-700 m-0">{{ $entityName }}</h4>
                            </div>
                            @php $byRoom = $listByEntity->groupBy(fn($e) => $e->location ?? 'Sin ubicación'); @endphp
                            @foreach($byRoom as $roomName => $listByRoom)
                                <div class="mb-4 border rounded-lg overflow-hidden">
                                    <div class="px-3 py-2 bg-gray-50 border-b flex items-center gap-2">
                                        <i class="fas fa-door-open text-gray-500"></i>
                                        <span class="text-xs font-medium text-gray-700">Ambiente: {{ $roomName }}</span>
                                        <span class="ml-auto text-[11px] text-gray-500">{{ $listByRoom->count() }} equipos</span>
                                    </div>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-xs">
                                            <thead class="bg-white text-gray-600 uppercase">
                                                <tr>
                                                    <th class="px-3 py-2 text-left">Equipo</th>
                                                    <th class="px-3 py-2 text-left">Categoría</th>
                                                    <th class="px-3 py-2 text-center">Uso Diario</th>
                                                    <th class="px-3 py-2 text-center">Días/Semana</th>
                                                    <th class="px-3 py-2 text-left">Motivo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php $byCat = $listByRoom->groupBy(fn($e) => $e->equipmentType?->equipmentCategory?->name ?? 'Sin categoría'); @endphp
                                                @foreach($byCat as $catName => $items)
                                                    <tr class="bg-gray-100">
                                                        <td colspan="5" class="px-3 py-1 text-[11px] font-semibold text-gray-700">
                                                            <i class="fas fa-tags mr-1 text-gray-500"></i> {{ $catName }}
                                                        </td>
                                                    </tr>
                                                    @foreach($items as $eq)
                                                        @php
                                                            $type = $eq->equipmentType;
                                                            $defaultMinutes = $eq->avg_daily_use_minutes_override ?? (($type?->default_avg_daily_use_hours ?? 0) * 60);
                                                        @endphp
                                                        <tr class="border-t" x-data="rowLogic({ id: {{ $eq->id }}, initialDaily: {{ $eq->is_daily_use ? 'true':'false' }}, initialDays: {{ $eq->usage_days_per_week ?? 'null' }}, minutes: {{ (int)$defaultMinutes }}, category: '{{ addslashes($catName) }}' })">
                                                            <td class="px-3 py-2">
                                                                <div class="font-medium text-gray-800">{{ $eq->custom_name ?? $type?->name }}</div>
                                                                <div class="text-[11px] text-gray-500">Pot: {{ $eq->power_watts_override ?? $type?->default_power_watts ?? 0 }} W · Min/día default: {{ $defaultMinutes }}</div>
                                                                <input type="hidden" name="items[{{ $eq->id }}][id]" value="{{ $eq->id }}">
                                                            </td>
                                                            <td class="px-3 py-2 text-gray-700">{{ $catName }}</td>
                                                            <td class="px-3 py-2 text-center">
                                                                <input type="checkbox" x-model="isDaily" @change="syncDays()" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                                <input type="hidden" :value="isDaily?1:0" name="items[{{ $eq->id }}][is_daily_use]">
                                                            </td>
                                                            <td class="px-3 py-2 text-center">
                                                                <template x-if="!isDaily">
                                                                    <select x-model.number="daysPerWeek" name="items[{{ $eq->id }}][usage_days_per_week]" class="border-gray-300 rounded text-xs">
                                                                        <option :value="null">-</option>
                                                                        <template x-for="n in [1,2,3,4,5,6,7]" :key="n">
                                                                            <option :value="n" x-text="n"></option>
                                                                        </template>
                                                                    </select>
                                                                </template>
                                                                <template x-if="isDaily">
                                                                    <span class="text-[11px] text-gray-500">7</span>
                                                                </template>
                                                            </td>
                                                            <td class="px-3 py-2 text-[11px] text-gray-600" x-text="reason"></td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between">
                        <div class="text-[11px] text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Si marcás uso diario se asume todos los días del período. Caso contrario se prorratea según días/semana.
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs rounded shadow hover:bg-blue-700">
                                <i class="fas fa-save mr-1"></i> Guardar Frecuencia
                            </button>
                            @if($invoice)
                                @if($confirmedAt)
                                    <a href="{{ route('snapshots.create', $invoice) }}" class="px-4 py-2 bg-indigo-600 text-white text-xs rounded shadow hover:bg-indigo-700" title="Ir a Ajustar Período">
                                        <i class="fas fa-edit mr-1"></i> Ir a Ajustar
                                    </a>
                                @else
                                    <button type="button" disabled class="px-4 py-2 bg-indigo-300 text-white text-xs rounded shadow cursor-not-allowed" title="Confirmá primero">
                                        <i class="fas fa-edit mr-1"></i> Ir a Ajustar
                                    </button>
                                @endif
                            @endif
                            @if(!$confirmedAt)
                                <button type="button" disabled class="px-4 py-2 bg-purple-300 text-white text-xs rounded shadow cursor-not-allowed" title="Confirmá primero">
                                    <i class="fas fa-magic mr-1"></i> Aplicar Recomendaciones
                                </button>
                            @else
                                <!-- Botón separado para aplicar recomendaciones fuera del form principal -->
                                <button form="applyRecsForm" type="submit" class="px-4 py-2 bg-purple-600 text-white text-xs rounded shadow hover:bg-purple-700">
                                    <i class="fas fa-magic mr-1"></i> Aplicar Recomendaciones
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

                @if($confirmedAt)
                    <!-- Form separado para Aplicar Recomendaciones (evitar formularios anidados) -->
                    <form id="applyRecsForm" method="POST" action="{{ route('usage.apply-recommendations') }}">
                        @csrf
                    </form>
                @endif

                <div class="mt-8" x-data="usageRecs()">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
                            <i class="fas fa-lightbulb text-yellow-500"></i> Recomendaciones Heurísticas
                        </h4>
                        <div class="flex gap-2">
                            <button @click="fetch()" class="px-3 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-xs rounded" x-show="state==='idle'">Calcular</button>
                            <span x-show="state==='loading'" class="text-xs text-gray-600"><i class="fas fa-spinner animate-spin mr-1"></i>Calculando...</span>
                        </div>
                    </div>
                    <template x-if="state==='ready'">
                        <div class="overflow-x-auto border rounded">
                            <table class="min-w-full text-[11px]">
                                <thead class="bg-yellow-50 text-yellow-900">
                                    <tr>
                                        <th class="px-2 py-1 text-left">Equipo</th>
                                        <th class="px-2 py-1">Cat.</th>
                                        <th class="px-2 py-1">Actual</th>
                                        <th class="px-2 py-1">Sugerido</th>
                                        <th class="px-2 py-1">Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="r in recs" :key="r.id">
                                        <tr class="border-t" :class="(r.current.is_daily_use && !r.suggested.is_daily_use) || (!r.current.is_daily_use && r.suggested.is_daily_use) ? 'bg-red-50' : ''">
                                            <td class="px-2 py-1" x-text="r.name"></td>
                                            <td class="px-2 py-1" x-text="r.category || '-' "></td>
                                            <td class="px-2 py-1 text-center" x-text="r.current.is_daily_use ? 'Diario' : (r.current.usage_days_per_week ? r.current.usage_days_per_week + '/sem' : '-')"></td>
                                            <td class="px-2 py-1 text-center" x-text="r.suggested.is_daily_use ? 'Diario' : (r.suggested.usage_days_per_week ? r.suggested.usage_days_per_week + '/sem' : '-')"></td>
                                            <td class="px-2 py-1" x-text="r.reason"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function usageManager(){
            return { };
        }
        function rowLogic({id, initialDaily, initialDays, minutes, category}){
            return {
                isDaily: initialDaily,
                daysPerWeek: initialDays,
                reason: computeReason(initialDaily, initialDays, minutes, category),
                syncDays(){
                    if(this.isDaily){ this.daysPerWeek = null; }
                    this.reason = computeReason(this.isDaily, this.daysPerWeek, minutes, category);
                }
            };
        }
        function computeReason(isDaily, daysPerWeek, minutes, category){
            if(isDaily) return 'Uso diario';
            if(daysPerWeek) return daysPerWeek + '/sem estimado';
            return 'Sin definir';
        }
        function usageRecs(){
            return {
                state:'idle', recs:[],
                fetch(){
                    this.state='loading';
                    fetch('{{ route('usage.recommendations') }}')
                        .then(r=>r.json())
                        .then(json=>{ this.recs = json.equipments || []; this.state='ready'; })
                        .catch(()=>{ this.state='idle'; alert('Error obteniendo recomendaciones'); });
                }
            };
        }
    </script>
</x-app-layout>
