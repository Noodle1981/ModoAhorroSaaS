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

                <p class="text-sm text-gray-600 mb-4">Definí cuántos días por semana se usa cada equipo. Esto mejora el cálculo real del período. Tras confirmar, podrás aplicar recomendaciones.</p>

                <form method="POST" action="{{ route('usage.equipment.bulk') }}" x-data="usageManager()">
                    @csrf
                    <div class="overflow-x-auto border rounded mb-4">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 text-gray-600 uppercase">
                                <tr>
                                    <th class="px-3 py-2 text-left">Equipo</th>
                                    <th class="px-3 py-2 text-left">Categoría</th>
                                    <th class="px-3 py-2 text-center">Uso Diario</th>
                                    <th class="px-3 py-2 text-center">Días/Semana</th>
                                    <th class="px-3 py-2 text-left">Motivo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($equipments as $eq)
                                    @php
                                        $type = $eq->equipmentType;
                                        $cat = $type?->equipmentCategory?->name;
                                        $defaultMinutes = $eq->avg_daily_use_minutes_override ?? (($type?->default_avg_daily_use_hours ?? 0) * 60);
                                    @endphp
                                    <tr class="border-t" x-data="rowLogic({ id: {{ $eq->id }}, initialDaily: {{ $eq->is_daily_use ? 'true':'false' }}, initialDays: {{ $eq->usage_days_per_week ?? 'null' }}, minutes: {{ (int)$defaultMinutes }}, category: '{{ addslashes($cat) }}' })">
                                        <td class="px-3 py-2">
                                            <div class="font-medium text-gray-800">{{ $eq->custom_name ?? $type?->name }}</div>
                                            <div class="text-[11px] text-gray-500">Pot: {{ $eq->power_watts_override ?? $type?->default_power_watts ?? 0 }} W · Min/día default: {{ $defaultMinutes }}</div>
                                            <input type="hidden" name="items[{{ $eq->id }}][id]" value="{{ $eq->id }}">
                                        </td>
                                        <td class="px-3 py-2 text-gray-700">{{ $cat ?? 'Sin categoría' }}</td>
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
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="text-[11px] text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Si marcás uso diario se asume todos los días del período. Caso contrario se prorratea según días/semana.
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-xs rounded shadow hover:bg-blue-700">
                                <i class="fas fa-save mr-1"></i> Guardar Frecuencia
                            </button>
                            @if($confirmedAt)
                                <form method="POST" action="{{ route('usage.apply-recommendations') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-purple-600 text-white text-xs rounded shadow hover:bg-purple-700">
                                        <i class="fas fa-magic mr-1"></i> Aplicar Recomendaciones
                                    </button>
                                </form>
                            @else
                                <button type="button" disabled class="px-4 py-2 bg-purple-300 text-white text-xs rounded shadow cursor-not-allowed" title="Confirmá primero">
                                    <i class="fas fa-magic mr-1"></i> Aplicar Recomendaciones
                                </button>
                            @endif
                        </div>
                    </div>
                </form>

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
