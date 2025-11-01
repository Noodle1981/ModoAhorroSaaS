<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Informe de Mejoras para: <span class="font-bold">{{ $entity->name }}</span>
            </h2>
            <a href="{{ route('entities.show', $entity) }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600">
                &larr; Volver a la Entidad
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (!$hasBillingData)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-6 rounded-lg shadow-md" role="alert">
                    <h3 class="font-bold text-lg">Datos Insuficientes</h3>
                    <p class="mt-2">Para poder generar un informe de mejoras, necesitamos que cargues al menos una factura con datos de consumo.</p>
                    @php
                        $firstSupply = $entity->supplies->first();
                        $firstContract = $firstSupply?->contracts->where('is_active', true)->first() ?? $firstSupply?->contracts->first();
                    @endphp
                    @if ($firstContract)
                        <div class="mt-4">
                            <a href="{{ route('contracts.invoices.create', $firstContract) }}" class="font-bold text-yellow-800 hover:underline">
                                + Cargar tu primera factura ahora
                            </a>
                        </div>
                    @endif
                </div>
            @elseif (empty($opportunities))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-6 rounded-lg shadow-md text-center">
                    <h3 class="font-bold text-lg">¡Felicitaciones!</h3>
                    <p class="mt-2">No hemos encontrado oportunidades claras de reemplazo en tu inventario actual. ¡Parece que tus equipos son bastante eficientes!</p>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:px-20 bg-white border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-800">Oportunidades de Ahorro por Reemplazo</h3>
                        <p class="mt-2 text-gray-600">Hemos analizado tu inventario y hemos encontrado las siguientes oportunidades para reducir tu consumo y tus costos reemplazando equipos por modelos más eficientes.</p>
                    </div>

                    <div class="p-6 sm:px-20">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($opportunities as $opp)
                                <div class="border border-gray-200 rounded-lg p-6 shadow-lg hover:shadow-2xl transition-shadow duration-300">
                                    <div class="flex items-start justify-between">
                                        <h4 class="text-lg font-bold text-indigo-700">{{ $opp['user_equipment'] }}</h4>
                                        <span class="px-2 py-1 text-xs font-semibold text-indigo-800 bg-indigo-100 rounded-full">Reemplazo</span>
                                    </div>
                                    
                                    <p class="text-sm text-gray-500 mt-2">Reemplazar por:</p>
                                    <p class="font-semibold text-gray-800">{{ $opp['suggestion'] }}</p>

                                    <div class="mt-6 pt-4 border-t border-gray-200">
                                        <p class="text-sm text-gray-500">Ahorro Anual Estimado</p>
                                        <p class="text-2xl font-bold text-green-600">${{ number_format($opp['ahorro_anual_pesos'], 0, ',', '.') }}</p>
                                    </div>

                                    <div class="mt-4">
                                        <p class="text-sm text-gray-500">Recupero de la Inversión</p>
                                        <p class="text-lg font-semibold text-gray-800">~{{ number_format($opp['retorno_inversion_anios'], 1, ',', '.') }} años</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
