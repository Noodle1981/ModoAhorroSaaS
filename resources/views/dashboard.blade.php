<x-app-layout>

    @php
        // Usamos el operador null-safe (?->) por si la suscripción fuera nula.
        // De esta forma, $plan será null en lugar de causar un error.
        $plan = $user->subscription?->plan;
    @endphp

    <h1>Dashboard Principal</h1>

    {{-- Verificamos si el usuario realmente tiene un plan asociado --}}
    @if ($plan)
        @php
            // Accedemos a las entidades a través de la compañía del usuario.
            // Añadimos una comprobación para asegurarnos de que la compañía existe.
            $entityCount = $user->company ? $user->company->entities()->count() : 0;
            $maxEntities = $plan->max_entities;
        @endphp

        {{-- Lógica para el Plan Gratuito --}}
        @if ($plan->name === 'Gratuito')
            
            @if ($entityCount < $maxEntities)
                {{-- Aún no ha creado su única entidad permitida --}}
                <p>¡Bienvenido a tu panel de control de Modo Ahorro!</p>
                <p>Estás en el plan <strong>Gratuito</strong>. Comienza a ahorrar energía y dinero.</p>
                <div style="margin-top: 2rem;">
                    <a href="{{ route('entities.create') }}" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                        + Analiza tu hogar
                    </a>
                </div>
            @else
                {{-- Ya creó la entidad permitida --}}
                <p>¡Bienvenido de nuevo!</p>
                <p>Ya estás analizando tu hogar. Para gestionar más entidades (oficinas, otros hogares, etc.), necesitarás un plan superior.</p>
            @endif

            {{-- Mensaje de Upsell --}}
            <div style="margin-top: 3rem; border: 1px solid #e2e8f0; border-radius: 5px; padding: 1.5rem; background-color: #f7fafc;">
                <h3 style="font-size: 1.25rem; font-weight: bold;">¿Necesitas analizar más lugares?</h3>
                <p style="margin-top: 0.5rem;">Adquiere un plan superior para analizar múltiples hogares, oficinas y/o comercios y lleva tu ahorro al siguiente nivel.</p>
                <a href="#" style="display: inline-block; margin-top: 1rem; background-color: #2d3748; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                    Ver Planes y Precios
                </a>
            </div>

        {{-- Lógica para Planes de Pago (Base, Gestor, etc.) --}}
        @else
            <p>¡Bienvenido a tu panel de control de Modo Ahorro!</p>
            <p>Estás en el plan <strong>{{ $plan->name }}</strong>. Desde aquí podrás gestionar tus entidades, revisar tus consumos y obtener recomendaciones.</p>

            {{-- Muestra el botón de añadir solo si no ha alcanzado el límite (si es que tiene uno) --}}
            @if (is_null($maxEntities) || $entityCount < $maxEntities)
                <div style="margin-top: 2rem;">
                    <a href="{{ route('entities.create') }}" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                        + Añadir Nueva Entidad
                    </a>
                </div>
            @else
                <div style="margin-top: 2rem; background-color: #fffbea; border: 1px solid #fce58a; padding: 1rem; border-radius: 5px;">
                    <p>Has alcanzado el límite de <strong>{{ $maxEntities }} entidades</strong> para tu plan actual.</p>
                </div>
            @endif

        @endif

    @else
        {{-- Fallback por si un usuario no tiene plan. --}}
        <div style="margin-top: 2rem; background-color: #fed7d7; border: 1px solid #f56565; padding: 1rem; border-radius: 5px;">
            <h3 style="font-size: 1.25rem; font-weight: bold;">Error de Configuración</h3>
            <p>Tu cuenta no tiene un plan de suscripción activo. Por favor, contacta con el soporte técnico.</p>
        </div>
    @endif

</x-app-layout>