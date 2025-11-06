<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-clipboard-check mr-2"></i>
                Resumen de Ajustes - {{ $entity->name }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('entities.show', $entity) }}" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
                </a>
                <a href="{{ route('snapshots.create', $invoice) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700">
                    <i class="fas fa-edit mr-1"></i> Ajustar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white shadow-sm rounded-lg p-5 border">
                    <div class="text-sm text-gray-500">Consumo real (período)</div>
                    <div class="text-2xl font-bold text-gray-900">
                        {{ number_format($invoice->total_energy_consumed_kwh ?? 0, 2) }} kWh
                    </div>
                    <div class="text-xs text-gray-400">{{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border">
                    <div class="text-sm text-gray-500">Estimado por ajustes</div>
                    <div class="text-2xl font-bold {{ ($percent !== null && $percent >= 80 && $percent <= 110) ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ number_format($totalEstimated, 2) }} kWh
                    </div>
                    <div class="text-xs text-gray-500">{{ $percent ? number_format($percent, 1) . '% del real' : '—' }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-5 border">
                    <div class="text-sm text-gray-500">Equipos ajustados</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $snapshots->count() }}</div>
                    <div class="text-xs text-gray-500">Guardados para este período</div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-5 border mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribución por Ambiente</h3>
                <div class="space-y-3">
                    @foreach($byRoom as $room => $data)
                        @php $pct = $totalEstimated > 0 ? ($data['kwh'] / $totalEstimated) * 100 : 0; @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <div class="text-gray-700"><i class="fas fa-door-open mr-2 text-gray-400"></i>{{ $room }}</div>
                                <div class="flex items-center gap-3">
                                    <span class="font-semibold text-gray-900">{{ number_format($data['kwh'], 2) }} kWh</span>
                                    <span class="text-gray-500">({{ number_format($pct, 1) }}%)</span>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                <div class="h-3 rounded-full bg-gradient-to-r from-green-400 to-green-600" style="width: {{ max(2, $pct) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-5 border">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Detalle de Equipos Ajustados</h3>
                    <a href="{{ route('snapshots.create', $invoice) }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        Volver a Ajustar
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipo</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ambiente</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Min/día</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Potencia</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Standby</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">kWh período</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($snapshots as $snap)
                                @php
                                    $eq = $snap->entityEquipment;
                                    $loc = $eq->location ?? 'Sin ubicación';
                                    $decoded = json_decode($loc, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        if (is_array($decoded)) {
                                            $loc = $decoded['name'] ?? ($decoded['rooms'][0]['name'] ?? 'Sin ubicación');
                                        }
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        {{ $eq->custom_name ?? $eq->equipmentType->name }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ $loc }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700">{{ optional($eq->equipmentType->equipmentCategory)->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700 text-right">{{ $snap->avg_daily_use_minutes }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-700 text-right">{{ number_format($snap->power_watts) }} W</td>
                                    <td class="px-4 py-2 text-sm text-gray-700 text-center">{{ $snap->has_standby_mode ? 'Sí' : 'No' }}</td>
                                    <td class="px-4 py-2 text-sm font-semibold text-gray-900 text-right">{{ number_format($snap->calculated_kwh_period, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
