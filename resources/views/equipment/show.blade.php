<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles del Equipo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">{{ $equipment->name }}</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Tipo de Equipo</p>
                            <p class="mt-1 text-lg text-gray-900">{{ $equipment->equipmentType->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Categoría</p>
                            <p class="mt-1 text-lg text-gray-900">{{ $equipment->equipmentType->equipmentCategory->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Ubicación</p>
                            <p class="mt-1 text-lg text-gray-900">{{ $equipment->location ?? 'Sin especificar' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Potencia Nominal</p>
                            <p class="mt-1 text-lg text-gray-900">{{ $equipment->equipmentType->power_consumption_w }} W</p>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-between items-center">
                        <a href="{{ route('entities.show', $equipment->entity) }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white font-semibold text-xs uppercase rounded-md hover:bg-gray-600">
                            &larr; Volver a la Entidad
                        </a>
                        <div class="space-x-4">
                            <a href="{{ route('entities.equipment.index', $equipment->entity) }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 active:bg-gray-500 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Volver al Inventario
                            </a>
                            <a href="{{ route('equipment.edit', $equipment) }}" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
