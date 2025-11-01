<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <span class="text-gray-500">Entidad:</span> {{ $entity->name }}
            </h2>
            <a href="{{ route('entities.show', $entity) }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                &larr; Volver a la Entidad
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Botón para añadir equipo -->
            <div class="flex justify-end mb-6">
                <a href="{{ route('entities.equipment.create', $entity) }}" class="inline-flex items-center px-6 py-3 bg-green-500 border border-transparent rounded-md font-semibold text-white uppercase tracking-widest hover:bg-green-600 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                    &#43; Añadir Equipo
                </a>
            </div>

            <!-- Contenedor principal -->
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 sm:px-20 bg- border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800">Inventario de Equipamiento</h3>
                    <p class="mt-2 text-gray-600">Aquí puedes ver y gestionar todos los equipos asociados a esta entidad.</p>
                </div>

                @if ($equipments->isEmpty())
                    <div class="text-center py-16 px-6">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l.707-.707M6.343 17.657l-.707.707m12.728 0l.707.707M12 21v-1m0-16a8 8 0 100 16 8 8 0 000-16z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No hay equipos todavía</h3>
                        <p class="mt-1 text-sm text-gray-500">Empieza añadiendo tu primer equipo para analizar su consumo.</p>
                    </div>
                @else
                    <div class="border border-gray-200 rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead class="bg-indigo-600">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tipo / Categoría</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Ubicación</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Potencia</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($equipments as $equipment)
                                    <tr class="even:bg-gray-50 hover:bg-indigo-100">
                                        <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200">
                                            <div class="text-sm font-medium text-gray-900">{{ $equipment->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200">
                                            <div class="text-sm text-gray-800">{{ $equipment->equipmentType->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $equipment->equipmentType->equipmentCategory->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200 text-sm text-gray-500">
                                            {{ $equipment->location ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap border-r border-gray-200 text-sm text-gray-500">
                                            {{ $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts }} W
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <a href="{{ route('equipment.show', $equipment) }}" class="text-green-600 hover:text-green-900 font-semibold">Detalles</a>
                                            <a href="{{ route('equipment.edit', $equipment) }}" class="inline-flex items-center px-3 py-1 bg-blue-500 text-white text-xs font-bold rounded-md hover:bg-blue-600 ml-4">Editar</a>
                                            <form action="{{ route('equipment.destroy', $equipment) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este equipo?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-500 text-white text-xs font-bold rounded-md hover:bg-red-600">Eliminar</button>
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