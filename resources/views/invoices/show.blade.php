<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalle de Factura #{{ $invoice->invoice_number ?? $invoice->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información General</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Entidad:</strong> {{ $invoice->contract->supply->entity->name }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Contrato:</strong> {{ $invoice->contract->utility_company_name }} - {{ $invoice->contract->contract_number }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Número de Factura:</strong> {{ $invoice->invoice_number ?? 'N/A' }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Fecha de Factura:</strong> {{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Período:</strong> {{ $invoice->start_date->format('d/m/Y') }} al {{ $invoice->end_date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Importes</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Coste Energía:</strong> {{ number_format($invoice->cost_for_energy, 2, ',', '.') }} $</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Coste Potencia:</strong> {{ number_format($invoice->cost_for_power, 2, ',', '.') }} $</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Impuestos:</strong> {{ number_format($invoice->taxes, 2, ',', '.') }} $</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Otros Cargos:</strong> {{ number_format($invoice->other_charges, 2, ',', '.') }} $</p>
                            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100"><strong>Importe Total:</strong> {{ number_format($invoice->total_amount, 2, ',', '.') }} $</p>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Consumos</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Consumo P1 (Punta):</strong> {{ number_format($invoice->energy_consumed_p1_kwh, 2, ',', '.') }} kWh</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Consumo P2 (Llano):</strong> {{ number_format($invoice->energy_consumed_p2_kwh, 2, ',', '.') }} kWh</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Consumo P3 (Valle):</strong> {{ number_format($invoice->energy_consumed_p3_kwh, 2, ',', '.') }} kWh</p>
                            <p class="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100"><strong>Consumo Total:</strong> {{ number_format($invoice->total_energy_consumed_kwh, 2, ',', '.') }} kWh</p>
                        </div>
                        @if($invoice->total_energy_injected_kwh > 0)
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Autoconsumo</h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Energía Inyectada:</strong> {{ number_format($invoice->total_energy_injected_kwh, 2, ',', '.') }} kWh</p>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Compensación Excedentes:</strong> {{ number_format($invoice->surplus_compensation_amount, 2, ',', '.') }} $</p>
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end">
                        <a href="{{ route('entities.show', $invoice->contract->supply->entity_id) }}" class="text-blue-600 hover:underline">
                            Volver a la Entidad
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
