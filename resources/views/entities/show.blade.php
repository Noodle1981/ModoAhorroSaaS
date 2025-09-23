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

                    <div class="mt-6 flex space-x-4">
                        <a href="{{ route('entities.edit', $entity) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Editar Entidad
                        </a>
                        <form action="{{ route('entities.destroy', $entity) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta entidad? Todos los suministros y equipos asociados también serán eliminados.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Eliminar Entidad
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sección de Análisis del Período Activo -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Análisis del Período Activo</h3>
                
                @if($periodSummary->real_consumption === null)
                    @php
                        $firstSupply = $entity->supplies->first();
                        $firstContract = $firstSupply?->contracts->where('is_active', true)->first() ?? $firstSupply?->contracts->first();
                    @endphp
                    <p class="text-sm text-gray-600 dark:text-gray-400">Aún no has cargado ninguna factura.
                        @if ($firstContract)
                            <a href="{{ route('contracts.invoices.create', $firstContract) }}" class="text-blue-600 hover:underline">Carga tu primera factura</a>
                        @elseif ($firstSupply)
                            <a href="{{ route('supplies.contracts.create', $firstSupply) }}" class="text-blue-600 hover:underline">Primero, crea un contrato para tu suministro</a>
                        @else
                            <a href="{{ route('entities.supplies.create', $entity) }}" class="text-blue-600 hover:underline">Primero, añade un punto de suministro</a>
                        @endif
                        para activar el análisis.
                    </p>
                @else
                    <p class="text-center font-bold text-gray-700 dark:text-gray-300 mb-4">
                        Período analizado: {{ $periodSummary->period_label }} ({{ $periodSummary->period_days }} días)
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

            <!-- Sección de Equipos de la Entidad -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Equipos de la Entidad</h3>
                    <a href="{{ route('entities.equipment.create', $entity) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                        Agregar Nuevo Equipo
                    </a>
                </div>

                @if($entity->entityEquipments->isEmpty())
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($entity->entityEquipments as $equipment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $equipment->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $equipment->equipmentType->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $equipment->location ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('equipment.edit', $equipment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600 mr-3">Editar</a>
                                            <form action="{{ route('equipment.destroy', $equipment) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este equipo?');" class="inline">
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

        </div>
    </div>
</x-app-layout>