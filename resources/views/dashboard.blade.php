<x-app-layout>

    @php
        $plan = $user->subscription?->plan;
    @endphp

    <h1 style="font-size: 2em; font-weight: bold; margin-bottom: 20px;">Dashboard General</h1>

    <!-- ======================================================= -->
    <!-- RESUMEN GLOBAL                                          -->
    <!-- ======================================================= -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="padding: 20px; background-color: #f9f9f9; border-radius: 8px; text-align: center;">
            <span style="font-size: 0.9em; color: #666;">Nº de Entidades</span>
            <p style="font-size: 2.5em; font-weight: bold; margin: 5px 0; color: #007bff;">
                {{ $globalSummary->entity_count }}
            </p>
        </div>
        <!-- Puedes añadir más métricas globales aquí cuando las implementes en el controlador -->
    </div>

    <!-- ======================================================= -->
    <!-- LISTA DE ENTIDADES                                      -->
    <!-- ======================================================= -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.5em; font-weight: bold;">Mis Entidades</h2>
            @if ($plan && (is_null($plan->max_entities) || $globalSummary->entity_count < $plan->max_entities))
                <a href="{{ route('entities.create') }}" style="background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                    + Añadir Nueva Entidad
                </a>
            @endif
        </div>

        @if($entities->isEmpty())
            <div style="border: 1px dashed #ccc; padding: 40px; text-align: center; border-radius: 8px; background-color: #fdfdfd;">
                <p>Aún no has añadido ninguna entidad.</p>
                <a href="{{ route('entities.create') }}" style="display: inline-block; margin-top: 10px; background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    + Crea tu primera entidad
                </a>
            </div>
        @else
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                @foreach ($entities as $entity)
                    <div style="border: 1px solid #eee; border-radius: 8px; padding: 20px; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h3 style="font-size: 1.2em; font-weight: bold;">{{ $entity->name }}</h3>
                        <p style="color: #666; margin-top: 5px;">{{ ucfirst($entity->type) }}</p>
                        <div style="margin-top: 15px;">
                            <a href="{{ route('entities.show', $entity) }}" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; display: inline-block;">
                                Ver Dashboard &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- ======================================================= -->
    <!-- INFORMACIÓN DEL PLAN Y MEJORA                         -->
    <!-- ======================================================= -->
    @if ($plan && !is_null($plan->max_entities) && $globalSummary->entity_count >= $plan->max_entities)
        <div style="margin-top: 3rem; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; background-color: #f7fafc; text-align: center;">
            <h3 style="font-size: 1.25rem; font-weight: bold;">Límite de Entidades Alcanzado</h3>
            <p style="margin-top: 0.5rem;">Has alcanzado el límite de {{ $plan->max_entities }} entidades para tu plan <strong>{{ $plan->name }}</strong>.</p>
            <a href="#" style="display: inline-block; margin-top: 1rem; background-color: #2d3748; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                Mejorar Plan para Añadir Más
            </a>
        </div>
    @endif

</x-app-layout>
