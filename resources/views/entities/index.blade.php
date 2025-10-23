<x-app-layout>

    @php
        // Obtenemos la información del plan para la lógica condicional
        $plan = $user->subscription?->plan;
        $canAddMore = false;
        if ($plan) {
            $entityCount = $entities->count();
            $maxEntities = $plan->max_entities;
            $canAddMore = is_null($maxEntities) || $entityCount < $maxEntities;
        }
    @endphp

    <a href="{{ route('dashboard') }}" style="text-decoration: none; color: #007bff; margin-bottom: 1rem; display: inline-block;">&larr; Volver al Dashboard</a>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Mis Entidades</h1>
        
        {{-- Lógica para mostrar el botón o el mensaje de mejora --}}
        @if ($canAddMore)
            <a href="{{ route('entities.create') }}" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                + Añadir Nueva Entidad
            </a>
        @else
            <div style="text-align: right;">
                <span style="background-color: #6c757d; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; cursor: not-allowed; opacity: 0.65; font-size: 0.9em;">
                    Límite de entidades alcanzado
                </span>
                <a href="#" style="font-size: 0.9em; margin-top: 5px; display: block;">Mejorar plan</a>
            </div>
        @endif
    </div>

    @if($entities->isEmpty())
        <div style="border: 1px solid #ddd; padding: 20px; text-align: center; border-radius: 8px; background-color: #f9f9f9;">
            <p>Aún no has añadido ninguna entidad. ¡Crea la primera para empezar a analizar tus consumos!</p>
        </div>
    @else
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Nombre</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tipo</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Dirección</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entities as $entity)
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd;"><a href="{{ route('entities.show', $entity) }}" style="text-decoration: none; color: #007bff; font-weight: bold;">{{ $entity->name }}</a></td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ ucfirst($entity->type) }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $entity->address_street }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                            <a href="{{ route('entities.edit', $entity) }}">Editar</a> |
                            <form action="{{ route('entities.destroy', $entity) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar esta entidad?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: red; background: none; border: none; cursor: pointer; padding: 0; font-size: inherit; text-decoration: underline;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-app-layout>
