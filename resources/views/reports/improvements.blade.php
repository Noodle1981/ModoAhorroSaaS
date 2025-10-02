<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Análisis de Mejoras para: {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (!$hasBillingData)
                {{-- ESTADO 0: SIN DATOS DE FACTURACIÓN --}}
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div style="padding: 40px; text-align: center; background-color: #fffbe6; border: 1px solid #ffe58f; border-radius: 8px;">
                        <h3>Recomendaciones Desactivadas</h3>
                        <p>Para poder calcular ahorros y ofrecerte recomendaciones personalizadas, necesitamos que cargues al menos una factura de energía con consumo.</p>
                        <p>Los cálculos de ahorro se basan en el costo por kWh de tu última factura.</p>
                        @php
                            $route = route('entities.supplies.create', $entity);
                            if ($entity->supplies->isNotEmpty()) {
                                $contract = $entity->supplies->first()->contracts->where('is_active', true)->first() ?? $entity->supplies->first()->contracts->first();
                                if ($contract) {
                                    $route = route('contracts.invoices.create', $contract);
                                } else {
                                    $route = route('supplies.contracts.create', $entity->supplies->first());
                                }
                            }
                        @endphp
                        <a href="{{ $route }}" style="margin-top: 15px; display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Empezar a Cargar Datos</a>
                    </div>
                </div>
            @else
                <!-- Sección de Oportunidades de Reemplazo de Equipos -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Oportunidades de Reemplazo de Equipos</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Análisis de equipos ineficientes en tu inventario que podrían ser reemplazados por modelos más nuevos y de menor consumo.</p>
                    
                    <div class="mt-6 space-y-4">
                        @forelse ($opportunities['equipment'] as $opp)
                            <div class="p-4 rounded-lg border-l-4 {{ $opp['retorno_inversion_anios'] < 2 ? 'border-green-500 bg-green-50 dark:bg-green-900/20' : 'border-orange-500 bg-orange-50 dark:bg-orange-900/20' }}">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-gray-200">Reemplazar: <span class="font-bold">{{ $opp['user_equipment'] }}</span></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Sugerencia: {{ $opp['suggestion'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-lg text-green-600 dark:text-green-400">Ahorro Anual: ${{ number_format($opp['ahorro_anual_pesos'], 2, ',', '.') }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Retorno de Inversión: {{ number_format($opp['retorno_inversion_anios'], 1, ',', '.') }} años</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">¡Felicidades! No hemos encontrado oportunidades claras de ahorro mediante el reemplazo de equipos. Tu inventario parece ser eficiente.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Sección de Oportunidades de Cambio de Hábitos -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Oportunidades de Cambio de Hábitos</h3>
                     <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Sugerencias para optimizar el uso de tus equipos según tu tarifa eléctrica.</p>
                    <div class="mt-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Próximamente: En esta sección te ayudaremos a identificar qué equipos de alto consumo podrías utilizar en los horarios más económicos de tu tarifa para maximizar el ahorro.</p>
                        {{-- @forelse ($opportunities['behavior'] as $opp) ... @empty ... @endforelse --}}
                    </div>
                </div>

                <!-- Sección de Oportunidades de Mantenimiento -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Oportunidades de Mantenimiento</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Alertas sobre mantenimientos preventivos para asegurar la eficiencia y longevidad de tus equipos.</p>
                    <div class="mt-6">
                         <p class="text-sm text-gray-500 dark:text-gray-400">Próximamente: En esta sección recibirás recordatorios y sugerencias para el mantenimiento de tus equipos, como la limpieza de filtros de aires acondicionados.</p>
                        {{-- @forelse ($opportunities['maintenance'] as $opp) ... @empty ... @endforelse --}}
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
