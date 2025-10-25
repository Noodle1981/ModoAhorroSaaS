<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Detalles de la Factura') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Factura #') }}{{ $invoice->invoice_number }} ({{ $invoice->invoice_date->format('d/m/Y') }})
                </p>
            </div>
            <a href="{{ route('contracts.show', $invoice->contract) }}" class="text-blue-500 hover:underline">
                &larr; {{ __('Volver al Contrato') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- RESUMEN PRINCIPAL -->
                    <div style="padding: 20px; background-color: #f9f9f9; border-radius: 8px; margin-bottom: 2rem;">
                        <h3 class="text-lg font-bold mb-4">{{ __('Resumen del Período') }}: {{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }}</h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; text-align: center;">
                            <div style="background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <span class="text-sm text-gray-600">{{ __('Consumo Total') }}</span>
                                <p class="text-2xl font-bold text-blue-600">{{ number_format($invoice->total_energy_consumed_kwh, 2, ',', '.') }} kWh</p>
                            </div>
                            <div style="background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <span class="text-sm text-gray-600">{{ __('Importe Total') }}</span>
                                <p class="text-2xl font-bold text-green-600">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                            </div>
                            <div style="background-color: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                <span class="text-sm text-gray-600">{{ __('Huella de Carbono') }}</span>
                                <p class="text-2xl font-bold text-gray-600">{{ number_format($invoice->co2_footprint_kg, 2, ',', '.') }} kg CO₂</p>
                            </div>
                        </div>
                    </div>

                    <!-- DETALLES DE LA FACTURA -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                        <!-- Columna de Energía -->
                        <div>
                            <h4 class="font-bold mb-2">{{ __('Detalles de Energía') }}</h4>
                            <div class="border-t pt-2">
                                <p><strong>{{ __('Energía Consumida (P1):') }}</strong> {{ number_format($invoice->energy_consumed_p1_kwh, 2, ',', '.') }} kWh</p>
                                <p><strong>{{ __('Energía Consumida (P2):') }}</strong> {{ number_format($invoice->energy_consumed_p2_kwh, 2, ',', '.') }} kWh</p>
                                <p><strong>{{ __('Energía Consumida (P3):') }}</strong> {{ number_format($invoice->energy_consumed_p3_kwh, 2, ',', '.') }} kWh</p>
                                <p class="font-bold mt-2"><strong>{{ __('Total Consumido:') }}</strong> {{ number_format($invoice->total_energy_consumed_kwh, 2, ',', '.') }} kWh</p>
                                @if($invoice->total_energy_injected_kwh > 0)
                                <p class="mt-4"><strong>{{ __('Energía Inyectada (Excedentes):') }}</strong> {{ number_format($invoice->total_energy_injected_kwh, 2, ',', '.') }} kWh</p>
                                @endif
                            </div>
                        </div>

                        <!-- Columna de Costes -->
                        <div>
                            <h4 class="font-bold mb-2">{{ __('Detalles de Costes') }}</h4>
                            <div class="border-t pt-2">
                                <p><strong>{{ __('Coste de Energía:') }}</strong> {{ number_format($invoice->cost_for_energy, 2, ',', '.') }} €</p>
                                <p><strong>{{ __('Coste de Potencia:') }}</strong> {{ number_format($invoice->cost_for_power, 2, ',', '.') }} €</p>
                                <p><strong>{{ __('Impuestos:') }}</strong> {{ number_format($invoice->taxes, 2, ',', '.') }} €</p>
                                <p><strong>{{ __('Otros Cargos:') }}</strong> {{ number_format($invoice->other_charges, 2, ',', '.') }} €</p>
                                @if($invoice->surplus_compensation_amount > 0)
                                <p class="text-green-700"><strong>{{ __('Compensación Excedentes:') }}</strong> -{{ number_format($invoice->surplus_compensation_amount, 2, ',', '.') }} €</p>
                                @endif
                                <p class="font-bold mt-2"><strong>{{ __('Importe Total:') }}</strong> {{ number_format($invoice->total_amount, 2, ',', '.') }} €</p>
                            </div>
                        </div>
                    </div>

                    <!-- ACCIONES -->
                    <div class="mt-8 flex justify-end gap-4">
                        @if($invoice->file_path)
                            <a href="{{ Storage::url($invoice->file_path) }}" target="_blank" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">{{ __('Ver Fichero Original') }}</a>
                        @endif
                        <a href="{{ route('invoices.edit', $invoice) }}" class="bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600">{{ __('Editar') }}</a>
                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta factura?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">{{ __('Eliminar') }}</button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
