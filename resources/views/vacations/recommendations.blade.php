<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Recomendaciones para tu Ausencia
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('vacations.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Planes
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">
            <i class="fas fa-lightbulb text-yellow-500 mr-3"></i>
            Recomendaciones para tu Ausencia
        </h1>
        <p class="text-gray-600 mt-2">
            {{ $entity->name }} • {{ $plan->days_away }} días ({{ $plan->start_date->format('d/m/Y') }} - {{ $plan->end_date->format('d/m/Y') }})
        </p>
    </div>

    <!-- Banner de ahorro -->
    <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg p-8 text-white mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold mb-2">
                    <i class="fas fa-leaf mr-2"></i>
                    Ahorrá Energía Mientras Estás Fuera
                </h2>
                <p class="text-green-100">
                    Seguir estas recomendaciones puede reducir significativamente tu consumo durante tu ausencia
                </p>
            </div>
            <div class="text-right">
                <div class="text-5xl font-bold">{{ $plan->days_away }}</div>
                <div class="text-green-100 text-sm">días de ahorro</div>
            </div>
        </div>
    </div>

    <!-- SIEMPRE APAGAR -->
    @if($recommendations['always_off']->isNotEmpty())
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-4">
                <div class="bg-red-100 rounded-full p-3 mr-4">
                    <i class="fas fa-power-off text-red-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Apagar Siempre
                    </h3>
                    <p class="text-sm text-gray-600">
                        Estos equipos consumen mucho y no son necesarios mientras no estás
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recommendations['always_off'] as $equipment)
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-{{ $equipment->category->icon ?? 'plug' }} text-red-600 text-xl mr-3 mt-1"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $equipment->equipmentType->name }}</h4>
                                <p class="text-xs text-gray-600">{{ $equipment->location ?? $equipment->category->name }}</p>
                                @if($equipment->equipmentType->average_power_watts)
                                    <p class="text-xs text-red-600 font-semibold mt-1">
                                        {{ number_format($equipment->equipmentType->average_power_watts) }} W
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- APAGAR SI AUSENCIA LARGA -->
    @if($recommendations['if_long']->isNotEmpty())
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-4">
                <div class="bg-orange-100 rounded-full p-3 mr-4">
                    <i class="fas fa-snowflake text-orange-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Apagar en Ausencias Largas (> 7 días)
                    </h3>
                    <p class="text-sm text-gray-600">
                        Para viajes de más de una semana, considerá apagar estos equipos (vaciar y limpiar antes)
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recommendations['if_long'] as $equipment)
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-{{ $equipment->category->icon ?? 'plug' }} text-orange-600 text-xl mr-3 mt-1"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $equipment->equipmentType->name }}</h4>
                                <p class="text-xs text-gray-600">{{ $equipment->location ?? $equipment->category->name }}</p>
                                <p class="text-xs text-orange-700 mt-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Vaciar contenido antes
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- DESENCHUFAR STANDBY -->
    @if($recommendations['standby_off']->isNotEmpty())
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-4">
                <div class="bg-yellow-100 rounded-full p-3 mr-4">
                    <i class="fas fa-plug text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Desenchufar (Modo Standby)
                    </h3>
                    <p class="text-sm text-gray-600">
                        Estos equipos consumen en standby. Desenchufarlos suma ahorro
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recommendations['standby_off'] as $equipment)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-{{ $equipment->category->icon ?? 'plug' }} text-yellow-600 text-xl mr-3 mt-1"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $equipment->equipmentType->name }}</h4>
                                <p class="text-xs text-gray-600">{{ $equipment->location ?? $equipment->category->name }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- MANTENER ENCENDIDOS -->
    @if($recommendations['keep_on']->isNotEmpty())
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900">
                        Mantener Encendidos
                    </h3>
                    <p class="text-sm text-gray-600">
                        Equipos de seguridad y conectividad que es mejor dejar funcionando
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recommendations['keep_on'] as $equipment)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-{{ $equipment->category->icon ?? 'plug' }} text-blue-600 text-xl mr-3 mt-1"></i>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">{{ $equipment->equipmentType->name }}</h4>
                                <p class="text-xs text-gray-600">{{ $equipment->location ?? $equipment->category->name }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Checklist para imprimir -->
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl shadow-md p-8">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">
            <i class="fas fa-clipboard-check text-blue-600 mr-2"></i>
            Checklist Antes de Salir
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                    Energía
                </h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li><i class="far fa-square text-gray-400 mr-2"></i>Apagar todos los equipos de climatización</li>
                    <li><i class="far fa-square text-gray-400 mr-2"></i>Desenchufar TVs, consolas y cargadores</li>
                    <li><i class="far fa-square text-gray-400 mr-2"></i>Apagar termotanque eléctrico</li>
                    @if($plan->days_away > 7)
                        <li><i class="far fa-square text-gray-400 mr-2"></i>Vaciar y apagar heladera/freezer</li>
                    @endif
                </ul>
            </div>
            <div class="bg-white rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-3">
                    <i class="fas fa-lock text-blue-500 mr-2"></i>
                    Seguridad
                </h4>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li><i class="far fa-square text-gray-400 mr-2"></i>Verificar alarma activada</li>
                    <li><i class="far fa-square text-gray-400 mr-2"></i>Cerrar llaves de gas y agua</li>
                    <li><i class="far fa-square text-gray-400 mr-2"></i>Dejar luces de seguridad con timer</li>
                    <li><i class="far fa-square text-gray-400 mr-2"></i>Revisar puertas y ventanas</li>
                </ul>
            </div>
        </div>
        <div class="mt-6 text-center">
            <button onclick="window.print()" 
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                <i class="fas fa-print mr-2"></i>
                Imprimir Checklist
            </button>
        </div>
    </div>

</div>
</div>
</x-app-layout>
