<x-app-layout>
    <x-slot name="header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Inventario de Equipos para ') }} '{{ $entity->name }}'
            </h2>
            <a href="{{ route('entities.equipment.create', $entity) }}" style="background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                {{ __('Añadir Nuevo Equipo') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    @if($equipments->isEmpty())
                        <div style="text-align: center; padding: 20px; border: 2px dashed #ccc; border-radius: 8px;">
                            <p class="text-gray-500">{{ __('Aún no has añadido ningún equipo a esta entidad.') }}</p>
                            <p class="mt-2">{{ __('¡Empieza añadiendo tu primer equipo para poder analizar su consumo!') }}</p>
                        </div>
                    @else
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                            @foreach ($equipments as $equipment)
                                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <h3 class="font-bold text-lg">{{ $equipment->custom_name ?? $equipment->equipmentType->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $equipment->equipmentType->equipmentCategory->name }}</p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="vertical-align: middle;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ $equipment->location ?? 'Sin ubicación' }}
                                        </p>
                                        <p class="text-sm text-gray-500"><strong>{{ __('Cantidad:') }}</strong> {{ $equipment->quantity }}</p>
                                    </div>
                                    <div style="margin-top: 16px; display: flex; justify-content: flex-end; gap: 8px;">
                                        <a href="{{ route('equipment.show', $equipment) }}" style="background-color: #007bff; color: white; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 0.875rem;">{{ __('Detalles') }}</a>
                                        <a href="{{ route('equipment.edit', $equipment) }}" style="background-color: #ffc107; color: black; padding: 8px 12px; text-decoration: none; border-radius: 5px; font-size: 0.875rem;">{{ __('Editar') }}</a>
                                        <form action="{{ route('equipment.destroy', $equipment) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este equipo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background-color: #dc3545; color: white; padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; font-size: 0.875rem;">{{ __('Eliminar') }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
