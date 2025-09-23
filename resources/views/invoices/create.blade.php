<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cargar Nueva Factura
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="mb-4">
                        Para el contrato: <strong>{{ $contract->rate_name }}</strong> (Suministro: {{ $contract->supply->supply_point_identifier }})
                    </p>
                    <p class="mb-6">
                        <a href="{{ route('contracts.show', $contract) }}" class="text-blue-600 hover:underline">&larr; Volver al Contrato</a>
                    </p>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            <strong>¡Ups! Hubo algunos problemas con los datos.</strong>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contracts.invoices.store', $contract) }}" method="POST">
                        @csrf
                        
                        <div class="p-4 border rounded-md">
                            <h3 class="text-lg font-semibold mb-4">Datos Principales (Obligatorios)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="start_date" class="block font-medium text-sm text-gray-700">Período de Consumo - Inicio</label>
                                    <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="end_date" class="block font-medium text-sm text-gray-700">Período de Consumo - Fin</label>
                                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="total_energy_consumed_kwh" class="block font-medium text-sm text-gray-700">Consumo Total (kWh)</label>
                                    <input type="number" step="0.01" id="total_energy_consumed_kwh" name="total_energy_consumed_kwh" value="{{ old('total_energy_consumed_kwh') }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="total_amount" class="block font-medium text-sm text-gray-700">Importe Total de la Factura ($)</label>
                                    <input type="number" step="0.01" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 p-4 border rounded-md">
                            <h3 class="text-lg font-semibold mb-4">Datos Detallados (Opcionales)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="invoice_number" class="block font-medium text-sm text-gray-700">Número de Factura</label>
                                    <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="invoice_date" class="block font-medium text-sm text-gray-700">Fecha de Emisión</label>
                                    <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="cost_for_energy" class="block font-medium text-sm text-gray-700">Costo por Energía ($)</label>
                                    <input type="number" step="0.01" id="cost_for_energy" name="cost_for_energy" value="{{ old('cost_for_energy') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="taxes" class="block font-medium text-sm text-gray-700">Impuestos ($)</label>
                                    <input type="number" step="0.01" id="taxes" name="taxes" value="{{ old('taxes') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="other_charges" class="block font-medium text-sm text-gray-700">Otros Cargos ($)</label>
                                    <input type="number" step="0.01" id="other_charges" name="other_charges" value="{{ old('other_charges') }}" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Guardar Factura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>