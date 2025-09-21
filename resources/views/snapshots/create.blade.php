<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Confirmar Uso del Inventario para el Período
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <p class="mb-4 text-gray-600">
                        Has cargado la factura para el período del <strong>{{ $invoice->start_date->format('d/m/Y') }}</strong> al <strong>{{ $invoice->end_date->format('d/m/Y') }}</strong>.
                        <br>
                        Por favor, ajusta los minutos de uso diario de tus equipos para que reflejen cómo los usaste durante **este período específico**.
                    </p>

                    <form action="{{ route('snapshots.store', $invoice) }}" method="POST">
                        @csrf
                        
                        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Equipo</th>
                                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Ubicación</th>
                                    <th style="width: 200px; padding: 12px; border: 1px solid #ddd; text-align: center;">Uso Diario (minutos)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($equipments as $index => $equipment)
                                    <tr>
                                        <td style="padding: 12px; border: 1px solid #ddd;">
                                            {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
                                            <input type="hidden" name="snapshots[{{ $index }}][entity_equipment_id]" value="{{ $equipment->id }}">
                                        </td>
                                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $equipment->location ?? 'Portátil' }}</td>
                                        <td style="padding: 12px; border: 1px solid #ddd;">
                                            <input type="number" name="snapshots[{{ $index }}][avg_daily_use_minutes]" 
                                                   value="{{ old('snapshots.'.$index.'.avg_daily_use_minutes', $equipment->previous_usage_minutes) }}"
                                                   min="0" max="1440" required
                                                   style="width: 100%; padding: 8px; text-align: center;">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="flex items-center justify-end mt-6">
                            <button type="submit" class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700">
                                Guardar Uso del Período
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>