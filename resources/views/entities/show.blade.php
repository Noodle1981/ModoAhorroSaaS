<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalles de la Entidad: {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Sección de Detalles de la Entidad -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Información General</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Nombre:</strong> {{ $entity->name }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Tipo:</strong> {{ $entity->type }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Dirección:</strong> {{ $entity->address_street }}, {{ $entity->address_postal_code }}</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><strong>Localidad:</strong> {{ $entity->locality->name }} ({{ $entity->locality->province->name }})</p>
                    
                    @if($entity->details)
                        <h4 class="text-md font-medium text-gray-900 dark:text-gray-100 mt-4">Detalles Adicionales:</h4>
                        <ul class="list-disc list-inside mt-2 text-sm text-gray-600 dark:text-gray-400">
                            @foreach($entity->details as $key => $value)
                                <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> 
                                    @if($key === 'rooms')
                                        {{ collect($value)->pluck('name')->implode(', ') }}
                                    @else
                                        {{ is_array($value) ? (is_string(reset($value)) ? implode(', ', $value) : json_encode($value)) : $value }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    
                </div>
            </div>

            <!-- Sección de Análisis del Período Activo -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Análisis del Período Activo</h3>
                    @if ($activeContract)
                        <a href="{{ route('contracts.invoices.create', $activeContract) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Cargar Nueva Factura
                        </a>
                    @endif
                </div>

                @if($allInvoices->isEmpty())
                     <p class="text-sm text-gray-600 dark:text-gray-400">Aún no has cargado ninguna factura.
                        @if ($activeContract)
                            <a href="{{ route('contracts.invoices.create', $activeContract) }}" class="text-blue-600 hover:underline">Carga tu primera factura</a>
                        @else
                            @php
                                $firstSupply = $entity->supplies->first();
                            @endphp
                            @if ($firstSupply)
                                <a href="{{ route('supplies.contracts.create', $firstSupply) }}" class="text-blue-600 hover:underline">Primero, crea un contrato para tu suministro</a>
                            @else
                                <a href="{{ route('entities.supplies.create', $entity) }}" class="text-blue-600 hover:underline">Primero, añade un punto de suministro</a>
                            @endif
                        @endif
                        para activar el análisis.
                    </p>
                @else
                    <p class="text-center font-bold text-gray-700 dark:text-gray-300 mb-4">
                        Período analizado (última factura): {{ $periodSummary->period_label }} ({{ $periodSummary->period_days }} días)
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Consumo Real (Según Factura)</span>
                            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                                {{ number_format($periodSummary->real_consumption, 0, ',', '.') }} kWh
                            </p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg shadow-sm">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Consumo Explicado (Según Inventario)</span>
                            <p class="text-3xl font-bold text-teal-600 dark:text-teal-400 mt-1">
                                {{ number_format($periodSummary->estimated_consumption, 0, ',', '.') }} kWh
                            </p>
                        </div>
                    </div>
                    @php
                        $difference = $periodSummary->real_consumption - $periodSummary->estimated_consumption;
                        $percentageExplained = ($periodSummary->real_consumption > 0) ? ($periodSummary->estimated_consumption / $periodSummary->real_consumption * 100) : 0;
                    @endphp
                    <p class="mt-4 text-center text-gray-700 dark:text-gray-300">
                        Tu inventario actual explica el <strong class="text-indigo-600 dark:text-indigo-400">{{ number_format($percentageExplained, 0) }}%</strong> de tu consumo real.
                        <br>
                        <small class="text-gray-500 dark:text-gray-400">Hay una diferencia de <strong class="text-red-600 dark:text-red-400">{{ number_format($difference, 0, ',', '.') }} kWh</strong>. Esto puede deberse a equipos no inventariados, imprecisiones en el uso, o la "Carga Electrónica Agregada".</small>
                    </p>
                @endif
            </div>

            <!-- Historial de Facturas -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Historial de Facturas</h3>
                @if($allInvoices->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">No hay facturas registradas para esta entidad.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Período</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Consumo Real</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Consumo Ajustado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">% Aprox.</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Importe Total</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($allInvoices as $invoice)
                                @php
                                    $real = $invoice->total_energy_consumed_kwh;
                                    $adjusted = $invoice->adjusted_consumption;
                                    $accuracy = $real > 0 ? ($adjusted / $real) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-bold">{{ number_format($real, 0, ',', '.') }} kWh</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($adjusted, 0, ',', '.') }} kWh</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ abs(100 - $accuracy) > 10 ? 'text-red-500' : 'text-green-600' }}">
                                        {{ number_format($accuracy, 1, ',', '.') }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ number_format($invoice->total_amount, 2, ',', '.') }} $</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('snapshots.edit', $invoice) }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-600 mr-3 font-bold">Ajustar Consumo</a>
                                        <a href="{{ route('invoices.show', $invoice) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 mr-3">Ver</a>
                                        <a href="{{ route('invoices.edit', $invoice) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600 mr-3">Editar</a>
                                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta factura?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            <!-- Resumen de Potencia Instalada por Ubicación -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Resumen de Potencia Instalada por Ubicación</h3>
                
                @php
                    $equipmentsByLocation = $entity->entityEquipments->groupBy('location');
                @endphp

                @if($entity->entityEquipments->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">No hay equipos registrados para generar un resumen.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ubicación</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo de Equipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Potencia Nominal Total (W)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($equipmentsByLocation as $location => $equipments)
                                    @php
                                        $equipmentsByType = $equipments->groupBy('equipmentType.name');
                                    @endphp
                                    @foreach($equipmentsByType as $typeName => $items)
                                        @php
                                            $firstItem = $items->first();
                                            $quantity = $items->sum('quantity');
                                            $power = $items->sum(function($item) {
                                                return ($item->power_watts_override ?? $item->equipmentType->default_power_watts) * $item->quantity;
                                            });
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $location ?? 'Portátil / Sin Ubicación' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $typeName }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $quantity }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($power, 0, ',', '.') }} W</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Sección de Equipos de la Entidad -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 inline-block mr-4">Equipos de la Entidad</h3>
                        <a href="{{ route('entities.reports.full-replacement', $entity) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Analizar Inventario Completo
                        </a>
                    </div>
                    <div class="relative inline-block text-left">
                        <button type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-green-500" id="menu-button" aria-expanded="true" aria-haspopup="true">
                            Agregar Nuevo Equipo
                            <!-- Heroicon name: solid/chevron-down -->
                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1" id="equipment-add-dropdown">
                            <div class="py-1" role="none">
                                <a href="{{ route('entities.equipment.create', ['entity' => $entity, 'type' => 'fixed']) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-0">Equipo Fijo</a>
                                <a href="{{ route('entities.equipment.create', ['entity' => $entity, 'type' => 'portable']) }}" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100" role="menuitem" tabindex="-1" id="menu-item-1">Equipo Portátil</a>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const menuButton = document.getElementById('menu-button');
                            const dropdown = document.getElementById('equipment-add-dropdown');

                            menuButton.addEventListener('click', function() {
                                dropdown.classList.toggle('hidden');
                            });

                            // Close the dropdown if the user clicks outside of it
                            window.addEventListener('click', function(event) {
                                if (!menuButton.contains(event.target) && !dropdown.contains(event.target)) {
                                    dropdown.classList.add('hidden');
                                }
                            });
                        });
                    </script>
                </div>

                @if($allEntityEquipments->isEmpty())
                    <p class="text-sm text-gray-600 dark:text-gray-400">Esta entidad aún no tiene equipos registrados.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ubicación</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Potencia (W)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Uso Configurado (h/día)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Uso Promedio Real (h/día)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Portátil</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($allEntityEquipments as $equipment)
                                    <tr class="{{ $equipment->trashed() ? 'bg-red-50 dark:bg-red-900/20 opacity-60' : '' }}">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $equipment->trashed() ? 'text-gray-500 line-through' : 'text-gray-900 dark:text-gray-100' }}">{{ $equipment->custom_name ?? $equipment->equipmentType->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $equipment->trashed() ? 'text-gray-400 line-through' : 'text-gray-500 dark:text-gray-400' }}">{{ $equipment->equipmentType->equipmentCategory->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $equipment->trashed() ? 'text-gray-400 line-through' : 'text-gray-500 dark:text-gray-400' }}">{{ $equipment->location ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $equipment->trashed() ? 'text-gray-400 line-through' : 'text-gray-500 dark:text-gray-400' }}">{{ $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $equipment->trashed() ? 'text-gray-400 line-through' : 'text-gray-500 dark:text-gray-400' }}">
                                            @php
                                                $minutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes;
                                                $hours = $minutes > 0 ? $minutes / 60 : 0;
                                            @endphp
                                            {{ number_format($hours, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $equipment->trashed() ? 'text-gray-400 line-through' : 'text-blue-600 dark:text-blue-400' }}">
                                            @php
                                                $avgMinutes = $equipment->usage_snapshots_avg_avg_daily_use_minutes;
                                                $avgHours = $avgMinutes > 0 ? $avgMinutes / 60 : 0;
                                            @endphp
                                            
                                            @if($avgMinutes === null)
                                                <span class="text-xs italic text-gray-400">N/A</span>
                                            @else
                                                {{ number_format($avgHours, 2, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $equipment->trashed() ? 'text-gray-400 line-through' : 'text-gray-500 dark:text-gray-400' }}">{{ $equipment->equipmentType->is_portable ? 'Sí' : 'No' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            @if($equipment->trashed())
                                                @if($equipment->replaced_by_equipment_id)
                                                    <a href="{{ route('reports.replacement', $equipment) }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-600 font-bold">Analizar ROI</a>
                                                @else
                                                    <span class="text-gray-400 italic">Eliminado</span>
                                                @endif
                                            @else
                                                <a href="{{ route('equipment.edit', $equipment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600 mr-3">Editar</a>
                                                <a href="{{ route('equipment.pre-destroy', $equipment) }}" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">Eliminar</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>