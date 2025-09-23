<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Confirmar Uso del Inventario
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-6 text-sm text-gray-600">
                        <p class="text-lg font-medium"><strong>Período de la factura:</strong> {{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }}</p>
                        <p class="mt-2">Ajusta el uso diario promedio de tus equipos para este período. Hemos pre-rellenado los campos con el último valor conocido o un promedio. Si un equipo no se usó, simplemente pon un '0'.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                            <!-- ... (código de errores) ... -->
                        </div>
                    @endif

                    <form action="{{ route('snapshots.store', $invoice) }}" method="POST">
                        @csrf

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ubicación</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" style="width: 250px;">Uso Diario Promedio</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($equipments as $index => $equipment)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $equipment->custom_name ?? $equipment->equipmentType->name }}</div>
                                                <input type="hidden" name="snapshots[{{ $index }}][entity_equipment_id]" value="{{ $equipment->id }}">
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $equipment->location ?? 'Portátil' }}</td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $defaultMinutes = $equipment->previous_usage_minutes;
                                                    $showHours = $defaultMinutes >= 60;
                                                @endphp

                                                <div class="flex items-center space-x-2">
                                                    <input type="number" 
                                                           name="{{ $showHours ? 'snapshots['.$index.'][avg_daily_use_hours]' : 'snapshots['.$index.'][avg_daily_use_minutes]' }}"
                                                           value="{{ $showHours ? round($defaultMinutes / 60, 2) : $defaultMinutes }}"
                                                           min="0"
                                                           step="{{ $showHours ? '0.01' : '1' }}"
                                                           class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-300"
                                                           required>
                                                    <span>{{ $showHours ? 'horas' : 'minutos' }}</span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                                No tienes equipos registrados para esta entidad.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                                Guardar Uso y Finalizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>