<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Corregir Datos de la Factura
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="mb-4">
                        Editando factura del período: <strong>{{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }}</strong>
                    </p>
                    <p class="mb-6">
                        <a href="{{ route('contracts.show', $invoice->contract) }}" class="text-blue-600 hover:underline">&larr; Volver al Contrato</a>
                    </p>

                    @if ($errors->any())
                        <!-- ... (código de errores) ... -->
                    @endif

                    <form action="{{ route('invoices.update', $invoice) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="p-4 border rounded-md">
                            <h3 class="text-lg font-semibold mb-4">Datos Principales (Obligatorios)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="start_date">Período - Inicio</label>
                                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $invoice->start_date->format('Y-m-d')) }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="end_date">Período - Fin</label>
                                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $invoice->end_date->format('Y-m-d')) }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="total_energy_consumed_kwh">Consumo Total (kWh)</label>
                                    <input type="number" step="0.01" id="total_energy_consumed_kwh" name="total_energy_consumed_kwh" value="{{ old('total_energy_consumed_kwh', $invoice->total_energy_consumed_kwh) }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="total_amount">Importe Total ($)</label>
                                    <input type="number" step="0.01" id="total_amount" name="total_amount" value="{{ old('total_amount', $invoice->total_amount) }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 p-4 border rounded-md">
                            <h3 class="text-lg font-semibold mb-4">Datos Detallados (Opcionales)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                               <div>
                                    <label for="invoice_number">Número de Factura</label>
                                    <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="invoice_date">Fecha de Emisión</label>
                                    <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="cost_for_energy">Costo por Energía ($)</label>
                                    <input type="number" step="0.01" id="cost_for_energy" name="cost_for_energy" value="{{ old('cost_for_energy', $invoice->cost_for_energy) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="taxes">Impuestos ($)</label>
                                    <input type="number" step="0.01" id="taxes" name="taxes" value="{{ old('taxes', $invoice->taxes) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="other_charges">Otros Cargos ($)</label>
                                    <input type="number" step="0.01" id="other_charges" name="other_charges" value="{{ old('other_charges', $invoice->other_charges) }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                Actualizar Factura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>