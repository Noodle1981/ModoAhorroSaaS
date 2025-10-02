<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Análisis de Viabilidad Solar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Potencial de Autoconsumo Solar</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Aún no tienes ninguna instalación solar registrada. Aquí puedes analizar el potencial de tus propiedades para generar tu propia energía.
                </p>
            </div>

            @php
                $suitable_entities = $entities->filter(function($entity) {
                    return in_array($entity->entity_type, ['hogar', 'comercio']) && !empty($entity->area);
                });
            @endphp

            @if($suitable_entities->isEmpty())
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div style="padding: 40px; text-align: center; background-color: #fffbe6; border: 1px solid #ffe58f; border-radius: 8px;">
                        <h4>No hemos encontrado propiedades aptas para el análisis.</h4>
                        <p class="mt-2">Para analizar la viabilidad, necesitamos que registres la <strong>superficie en m²</strong> de tus <strong>casas o comercios</strong>.</p>
                        <a href="{{ route('entities.index') }}" style="margin-top: 15px; display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Ir a Mis Entidades</a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($suitable_entities as $entity)
                        @php
                            // Cálculo simple: 1 kWp (kilovatio pico) ocupa aprox. 5-7 m².
                            // Usamos un promedio de 6m² por kWp.
                            // 1 kWp genera aprox. 1200-1500 kWh/año en muchas partes de Argentina. Usamos 1350 kWh/año/kWp.
                            $surface = $entity->area;
                            $potential_kwp = floor($surface / 6);
                            $annual_generation_kwh = $potential_kwp * 1350;
                        @endphp

                        <div class="p-6 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <h4 class="text-md font-bold text-gray-900 dark:text-gray-100">{{ $entity->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $entity->entity_type }} - {{ $surface }} m²</p>
                            
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Potencial de Instalación Estimado:</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $potential_kwp }} kWp</p>
                            </div>
                            <div class="mt-2">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Generación Anual Estimada:</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($annual_generation_kwh, 0, ',', '.') }} kWh/año</p>
                            </div>
                            <p class="mt-4 text-xs text-gray-500 dark:text-gray-500 italic">
                                *Estimación simple basada en la superficie. Un análisis profesional es requerido para una cotización precisa.
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
