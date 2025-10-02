<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Análisis de Retorno de Inversión (ROI)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Resumen del Reemplazo -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Reemplazo de Equipo</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Análisis comparativo entre el equipo anterior y su reemplazo.
                </p>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Equipo Antiguo -->
                    <div class="p-4 border border-red-300 dark:border-red-700 rounded-lg">
                        <h4 class="font-semibold text-red-800 dark:text-red-300">Equipo Anterior</h4>
                        <p class="text-xl font-bold">{{ $oldEquipment->custom_name ?? $oldEquipment->equipmentType->name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Potencia: {{ $oldEquipment->power_watts_override ?? $oldEquipment->equipmentType->default_power_watts }} W</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Uso Diario (defecto): {{ round(($oldEquipment->avg_daily_use_minutes_override ?? $oldEquipment->equipmentType->default_avg_daily_use_minutes ?? 0) / 60, 2) }} horas</p>
                        <p class="mt-2 text-lg font-bold text-red-600 dark:text-red-400">Consumo Anual: {{ number_format($oldConsumption['consumo_kwh_total_periodo'], 0, ',', '.') }} kWh</p>
                    </div>
                    <!-- Equipo Nuevo -->
                    <div class="p-4 border border-green-300 dark:border-green-700 rounded-lg">
                        <h4 class="font-semibold text-green-800 dark:text-green-300">Equipo Nuevo</h4>
                        <p class="text-xl font-bold">{{ $newEquipment->custom_name ?? $newEquipment->equipmentType->name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Potencia: {{ $newEquipment->power_watts_override ?? $newEquipment->equipmentType->default_power_watts }} W</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Uso Diario (defecto): {{ round(($newEquipment->avg_daily_use_minutes_override ?? $newEquipment->equipmentType->default_avg_daily_use_minutes ?? 0) / 60, 2) }} horas</p>
                        <p class="mt-2 text-lg font-bold text-green-600 dark:text-green-400">Consumo Anual: {{ number_format($newConsumption['consumo_kwh_total_periodo'], 0, ',', '.') }} kWh</p>
                    </div>
                </div>
            </div>

            <!-- Calculadora de ROI -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Calculadora de Ahorro y ROI</h3>
                <div class="mt-4 md:flex md:items-center md:space-x-6">
                    <div class="w-full md:w-1/3">
                        <label for="avg_kwh_price" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Tu costo promedio por kWh ($)</label>
                        <input type="number" id="avg_kwh_price" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600" placeholder="Ej: 15.50">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Puedes encontrar este valor en tu factura de energía.</p>
                    </div>
                    <div class="w-full md:w-2/3 mt-4 md:mt-0">
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Ahorro Anual Estimado (kWh): <strong class="text-lg">{{ number_format($oldConsumption['consumo_kwh_total_periodo'] - $newConsumption['consumo_kwh_total_periodo'], 0, ',', '.') }} kWh</strong></p>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Ahorro Anual Estimado ($): <strong id="annual-savings-amount" class="text-lg text-green-600 dark:text-green-400">-</strong></p>
                            @if($newEquipment->acquisition_cost > 0)
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Costo del nuevo equipo: <strong>${{ number_format($newEquipment->acquisition_cost, 2, ',', '.') }}</strong></p>
                                <p class="mt-2 text-lg font-bold">Retorno de la Inversión (ROI): <strong id="roi-period" class="text-xl text-blue-600 dark:text-blue-400">-</strong></p>
                            @else
                                <p class="mt-2 text-sm text-yellow-600 dark:text-yellow-400">No se ingresó el costo de adquisición para el nuevo equipo, por lo que no se puede calcular el ROI.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

             <div class="mt-6 text-center">
                <a href="{{ route('entities.show', $oldEquipment->entity) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                    &larr; Volver a la Entidad
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avgKwhPriceInput = document.getElementById('avg_kwh_price');
            const annualSavingsAmountEl = document.getElementById('annual-savings-amount');
            const roiPeriodEl = document.getElementById('roi-period');

            const annualKwhSavings = {{ $oldConsumption['consumo_kwh_total_periodo'] - $newConsumption['consumo_kwh_total_periodo'] }};
            const acquisitionCost = {{ $newEquipment->acquisition_cost ?? 0 }};

            const numberFormat = (number, decimals, dec_point, thousands_sep) => {
                // ... (función de formato)
                number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+number) ? 0 : +number, prec = !isFinite(+decimals) ? 0 : Math.abs(decimals), sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep, dec = (typeof dec_point === 'undefined') ? ',' : dec_point, s = '', toFixedFix = function (n, prec) { var k = Math.pow(10, prec); return '' + Math.round(n * k) / k; };
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) { s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep); }
                if ((s[1] || '').length < prec) { s[1] = s[1] || ''; s[1] += new Array(prec - s[1].length + 1).join('0'); }
                return s.join(dec);
            }

            avgKwhPriceInput.addEventListener('input', function() {
                const avgPrice = parseFloat(this.value);

                if (isNaN(avgPrice) || avgPrice <= 0) {
                    annualSavingsAmountEl.textContent = '-';
                    if(roiPeriodEl) roiPeriodEl.textContent = '-';
                    return;
                }

                const annualSavingsInCurrency = annualKwhSavings * avgPrice;
                annualSavingsAmountEl.textContent = `$${numberFormat(annualSavingsInCurrency, 2, ',', '.')}`;

                if (roiPeriodEl && acquisitionCost > 0 && annualSavingsInCurrency > 0) {
                    const paybackYears = acquisitionCost / annualSavingsInCurrency;
                    if (paybackYears < 1) {
                        const months = paybackYears * 12;
                        roiPeriodEl.textContent = `${numberFormat(months, 1, ',', '.')} meses`;
                    } else {
                        roiPeriodEl.textContent = `${numberFormat(paybackYears, 1, ',', '.')} años`;
                    }
                } else if (roiPeriodEl) {
                    roiPeriodEl.textContent = 'No se puede calcular';
                }
            });
        });
    </script>
</x-app-layout>
