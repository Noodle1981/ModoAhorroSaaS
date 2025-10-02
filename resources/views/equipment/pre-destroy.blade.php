<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Eliminar Equipo: {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

                    <h3 class="text-lg font-medium text-center">¿Cuál es el motivo para eliminar este equipo?</h3>
                    <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Seleccionar la opción correcta nos ayudará a mantener la precisión de tu historial energético.
                    </p>

                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Opción 1: Solo Eliminar -->
                        <div class="p-6 border border-gray-200 dark:border-gray-700 rounded-lg flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-lg">Solo quitar el equipo</h4>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Selecciona esta opción si el equipo se rompió, lo vendiste, o simplemente ya no lo usarás más y no lo has reemplazado por uno nuevo.
                                </p>
                            </div>
                            <div class="mt-4">
                                <form action="{{ route('equipment.destroy', $equipment) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este equipo de forma permanente de tu inventario?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Confirmar Eliminación
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Opción 2: Reemplazar -->
                        <div class="p-6 border-2 border-green-500 rounded-lg flex flex-col justify-between bg-green-50 dark:bg-green-900/20">
                            <div>
                                <h4 class="font-semibold text-lg text-green-800 dark:text-green-300">Lo reemplacé por uno nuevo</h4>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Elige esta opción si has comprado un equipo nuevo para sustituir a este. Esto nos permitirá calcular el ahorro y el retorno de tu inversión (ROI) en el futuro.
                                </p>
                            </div>
                            <div class="mt-4">
                                <a href="{{ route('entities.equipment.create', ['entity' => $equipment->entity, 'type' => $equipment->equipmentType->is_portable ? 'portable' : 'fixed', 'replacing' => $equipment->id]) }}" class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Registrar Equipo de Reemplazo
                                </a>
                            </div>
                        </div>

                    </div>

                    <div class="mt-8 text-center">
                        <a href="{{ route('entities.show', $equipment->entity) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            Cancelar y volver a la entidad
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
