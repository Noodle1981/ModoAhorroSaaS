<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
            <i class="fas fa-chart-line text-green-600"></i>
            Centro Económico (Costos & Ahorros)
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-gray-600 mb-6">
                    Visión consolidada de tu gasto energético, ahorros logrados y oportunidades de inversión con retorno.
                    Esta es una versión inicial: pronto verás cifras reales y desagregación por entidad y ambiente.
                </p>

                <!-- Sección: Métricas Clave (placeholders) -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
                    <div class="p-4 border rounded bg-gradient-to-br from-gray-50 to-gray-100">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase">Gasto Mensual Estimado</p>
                        <p class="mt-2 text-lg font-bold text-gray-700">
                            @if($metrics['monthly_cost_estimate']) ${{ number_format($metrics['monthly_cost_estimate'],2) }} @else — @endif
                        </p>
                        <p class="text-[10px] text-gray-500">Promedio últimas facturas (@if($metrics['avg_tariff']) Tarifa prom: ${{ number_format($metrics['avg_tariff'],3) }}/kWh @endif)</p>
                    </div>
                    <div class="p-4 border rounded bg-gradient-to-br from-green-50 to-green-100">
                        <p class="text-[11px] font-semibold text-green-600 uppercase">Potencial Standby / Mes</p>
                        <p class="mt-2 text-lg font-bold text-green-700">
                            @if(isset($metrics['standby_cost'])) ${{ number_format($metrics['standby_cost'],2) }} @else — @endif
                        </p>
                        <p class="text-[10px] text-green-600">Si todos los equipos con standby se optimizan</p>
                    </div>
                    <div class="p-4 border rounded bg-gradient-to-br from-indigo-50 to-indigo-100">
                        <p class="text-[11px] font-semibold text-indigo-600 uppercase">Ahorro por Frecuencia</p>
                        <p class="mt-2 text-lg font-bold text-indigo-700">—</p>
                        <p class="text-[10px] text-indigo-600">Optimización días/semana</p>
                    </div>
                    <div class="p-4 border rounded bg-gradient-to-br from-yellow-50 to-yellow-100">
                        <p class="text-[11px] font-semibold text-yellow-600 uppercase">Inversión Potencial</p>
                        <p class="mt-2 text-lg font-bold text-yellow-700">—</p>
                        <p class="text-[10px] text-yellow-600">Reemplazos eficientes</p>
                    </div>
                    <div class="p-4 border rounded bg-gradient-to-br from-purple-50 to-purple-100">
                        <p class="text-[11px] font-semibold text-purple-600 uppercase">ROI Anual Estimado</p>
                        <p class="mt-2 text-lg font-bold text-purple-700">—</p>
                        <p class="text-[10px] text-purple-600">Basado en potencial</p>
                    </div>
                </div>

                <!-- Sección: Próximas Acciones / Roadmap -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                        <i class="fas fa-route text-gray-500"></i>
                        Próximas mejoras previstas
                    </h3>
                    <ul class="text-sm text-gray-600 list-disc pl-5 space-y-1">
                        <li>Cálculo real de gasto mensual usando tu mix tarifario y períodos cargados.</li>
                        <li>Estimación de ahorro acumulado post cambios de Standby y Frecuencia.</li>
                        <li>Ranking de entidades y ambientes por costo mensual.</li>
                        <li>Simulación de reemplazo con ROI y payback.</li>
                        <li>Histórico de ahorros mes a mes.</li>
                    </ul>
                </div>

                <!-- Sección: Facturas recientes (contexto base) -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-file-invoice-dollar text-gray-600"></i>
                        Últimas facturas cargadas
                    </h3>
                    @if($invoices->isEmpty())
                        <p class="text-sm text-gray-500">No hay facturas aún. Cargá al menos una para ver baseline de gasto.</p>
                    @else
                        <div class="overflow-x-auto border rounded">
                            <table class="min-w-full text-xs">
                                <thead class="bg-gray-50 text-gray-600 uppercase">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Período</th>
                                        <th class="px-3 py-2 text-left">Entidad</th>
                                        <th class="px-3 py-2 text-center">Días</th>
                                        <th class="px-3 py-2 text-right">Consumo kWh</th>
                                        <th class="px-3 py-2 text-right">Costo Total ($)</th>
                                        <th class="px-3 py-2 text-right">$/kWh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $inv)
                                        @php
                                            $days = $inv->start_date->diffInDays($inv->end_date) + 1;
                                            $kwh = $inv->total_energy_consumed_kwh ?? 0;
                                            $cost = $inv->total_amount ?? 0;
                                            $tariff = ($kwh > 0 && $cost > 0) ? ($cost / $kwh) : null;
                                        @endphp
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="px-3 py-2 text-gray-700">{{ $inv->start_date->format('d/m') }} - {{ $inv->end_date->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $inv->contract->supply->entity->name ?? '-' }}</td>
                                            <td class="px-3 py-2 text-center text-gray-600">{{ $days }}</td>
                                            <td class="px-3 py-2 text-right font-medium text-gray-800">
                                                @if($kwh > 0)
                                                    {{ number_format($kwh, 1) }}
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right font-bold text-gray-800">
                                                @if($cost > 0)
                                                    ${{ number_format($cost, 2) }}
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-600 text-[11px]">
                                                @if($tariff)
                                                    ${{ number_format($tariff, 3) }}
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Tabla de costos por categoría -->
                <div class="mb-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fas fa-list text-gray-600"></i>
                        Costo mensual estimado por categoría
                    </h3>
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-50 text-gray-600 uppercase">
                                <tr>
                                    <th class="px-3 py-2 text-left">Categoría</th>
                                    <th class="px-3 py-2 text-right">Costo mensual ($)</th>
                                    <th class="px-3 py-2 text-right">Consumo mensual (kWh)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $catCosts = [];
                                    foreach($equipments as $eq) {
                                        $cat = $eq->equipmentType && $eq->equipmentType->equipmentCategory ? $eq->equipmentType->equipmentCategory->name : 'Sin categoría';
                                        $detail = collect($equipmentDetails)->firstWhere('equipment_id', $eq->id);
                                        if(!$detail) continue;
                                        if(!isset($catCosts[$cat])) $catCosts[$cat] = ['cost'=>0,'kwh'=>0];
                                        $catCosts[$cat]['cost'] += $detail['calculation']['costo'] ?? 0;
                                        $catCosts[$cat]['kwh'] += $detail['calculation']['kwh_total'] ?? 0;
                                    }
                                @endphp
                                @foreach($catCosts as $cat => $vals)
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-3 py-2 text-gray-700">{{ $cat }}</td>
                                        <td class="px-3 py-2 text-right font-medium text-gray-800">${{ number_format($vals['cost'],2) }}</td>
                                        <td class="px-3 py-2 text-right text-gray-600">{{ number_format($vals['kwh'],1) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-10 p-4 bg-green-50 border-l-4 border-green-400 text-green-700 text-sm flex gap-2">
                    <i class="fas fa-lightbulb text-green-500 mt-0.5"></i>
                    <div>
                        Este módulo evolucionará para convertirse en tu panel central de decisiones económicas: priorización de inversiones, seguimiento de ahorros y visualización de impacto.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
