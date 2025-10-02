<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Calibrar Consumo para la Factura del {{ $invoice->start_date->format('d/m/Y') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Panel de Control de Calibración -->
            <div id="calibration-panel" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg sticky top-0 z-10">
                @php
                    $periodDays = $invoice->start_date->diffInDays($invoice->end_date) + 1;
                    $realConsumption = $invoice->total_energy_consumed_kwh;
                    $initialEstimatedConsumption = $invoice->snapshots->sum('calculated_kwh_period');
                    $difference = $realConsumption - $initialEstimatedConsumption;
                    $percentageExplained = $realConsumption > 0 ? ($initialEstimatedConsumption / $realConsumption) * 100 : 0;
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Consumo Real (Factura)</span>
                        <p id="real-consumption" class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($realConsumption, 2, ',', '.') }} kWh</p>
                    </div>
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Consumo Explicado (Calculado)</span>
                        <p id="estimated-consumption" class="text-2xl font-bold text-teal-600 dark:text-teal-400">{{ number_format($initialEstimatedConsumption, 2, ',', '.') }} kWh</p>
                    </div>
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Diferencia</span>
                        <p id="difference" class="text-2xl font-bold {{ $difference < 0 ? 'text-red-500' : 'text-green-500' }}">{{ number_format($difference, 2, ',', '.') }} kWh</p>
                    </div>
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">% Explicado</span>
                        <p id="percentage-explained" class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($percentageExplained, 1, ',', '.') }}%</p>
                    </div>
                </div>
                <div id="warning-10-percent" class="text-center mt-4 text-sm text-red-500 font-semibold hidden">La diferencia supera el 10% del consumo real.</div>
            </div>

            <!-- Formulario de Ajuste -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <form id="calibration-form" action="{{ route('snapshots.update', $invoice) }}" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Equipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ubicación</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase" style="width: 250px;">Uso Diario Promedio</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($allEquipments as $index => $equipment)
                                    @php
                                        $snapshot = $snapshotsByEquipmentId->get($equipment->id);
                                        $usageMinutes = $snapshot ? $snapshot->avg_daily_use_minutes : ($equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes ?? 0);
                                        $showHours = $usageMinutes >= 60;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $equipment->custom_name ?? $equipment->equipmentType->name }}</div>
                                            @if ($equipment->deleted_at && $equipment->deleted_at < $invoice->end_date)
                                                <div class="text-xs italic text-red-500 dark:text-red-400">Eliminado el {{ $equipment->deleted_at->format('d/m/Y') }}</div>
                                            @endif
                                            <input type="hidden" name="snapshots[{{ $index }}][entity_equipment_id]" value="{{ $equipment->id }}">
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $equipment->location ?? 'Portátil' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <input type="number" 
                                                       name="snapshots[{{ $index }}][{{ $showHours ? 'avg_daily_use_hours' : 'avg_daily_use_minutes' }}]"
                                                       value="{{ $showHours ? number_format($usageMinutes / 60, 2, '.', '') : $usageMinutes }}"
                                                       min="0"
                                                       step="{{ $showHours ? '0.01' : '1' }}"
                                                       class="usage-input block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300"
                                                       required>
                                                <span>{{ $showHours ? 'horas' : 'minutos' }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('entities.show', $invoice->contract->supply->entity) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-800 focus:outline-none focus:border-green-800 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Guardar Ajustes de Consumo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('calibration-form');
            const usageInputs = form.querySelectorAll('.usage-input');
            const estimatedConsumptionEl = document.getElementById('estimated-consumption');
            const differenceEl = document.getElementById('difference');
            const percentageExplainedEl = document.getElementById('percentage-explained');
            const warningEl = document.getElementById('warning-10-percent');
            const realConsumption = parseFloat({{ $realConsumption }});
            const periodDays = parseInt({{ $periodDays }});

            // Función para formatear números como en Blade
            const numberFormat = (number, decimals, dec_point, thousands_sep) => {
                number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number,
                    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                    sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
                    dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
                    s = '',
                    toFixedFix = function (n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || '').length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1).join('0');
                }
                return s.join(dec);
            }

            // Función para recalcular
            const recalculate = async () => {
                const formData = new FormData(form);
                const data = {
                    snapshots: [],
                    period_days: periodDays,
                    _token: '{{ csrf_token() }}'
                };

                // Llenar los datos de snapshots
                const elements = form.elements;
                for (let i = 0; i < elements.length; i++) {
                    const element = elements[i];
                    if (element.name.startsWith('snapshots')) {
                        // Extraer índice y campo (ej: 0, entity_equipment_id)
                        const match = element.name.match(/snapshots\[(\d+)\]\[(.+)\]/);
                        if (match) {
                            const index = parseInt(match[1]);
                            const field = match[2];
                            if (!data.snapshots[index]) {
                                data.snapshots[index] = {};
                            }
                            data.snapshots[index][field] = element.value;
                        }
                    }
                }
                // Filtrar elementos vacíos que puedan haberse creado
                data.snapshots = data.snapshots.filter(el => el != null);

                try {
                    const response = await fetch('{{ route("api.snapshots.recalculate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const result = await response.json();
                    updatePanel(result.estimated_consumption);

                } catch (error) {
                    console.error('Error during recalculation:', error);
                }
            };

            // Función para actualizar el panel superior
            const updatePanel = (estimated) => {
                const difference = realConsumption - estimated;
                const percentage = realConsumption > 0 ? (estimated / realConsumption) * 100 : 0;

                estimatedConsumptionEl.textContent = `${numberFormat(estimated, 2, ',', '.')} kWh`;
                differenceEl.textContent = `${numberFormat(difference, 2, ',', '.')} kWh`;
                percentageExplainedEl.textContent = `${numberFormat(percentage, 1, ',', '.')}%`;

                // Clases de color para la diferencia
                differenceEl.classList.toggle('text-red-500', difference > 0);
                differenceEl.classList.toggle('text-green-500', difference <= 0);

                // Advertencia del 10%
                if (Math.abs(difference) > realConsumption * 0.1) {
                    warningEl.classList.remove('hidden');
                } else {
                    warningEl.classList.add('hidden');
                }
            };

            // Throttling para no llamar a la API en cada pulsación de tecla
            let timeout;
            usageInputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(recalculate, 500); // Espera 500ms después de la última entrada
                });
            });
            
            // Comprobación inicial de la advertencia del 10%
            if (Math.abs({{ $difference }}) > realConsumption * 0.1) {
                warningEl.classList.remove('hidden');
            }
        });
    </script>
</x-app-layout>
