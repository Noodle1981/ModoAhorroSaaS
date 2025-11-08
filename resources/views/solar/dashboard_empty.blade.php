<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-sun mr-2"></i>
            Panel Solar - Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                
                <!-- Estado Vacío -->
                <div class="text-center py-12">
                    <i class="fas fa-solar-panel text-gray-300 text-6xl mb-6"></i>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">
                        ¿Cuánto podrías ahorrar con energía solar?
                    </h3>
                    <p class="text-gray-600 mb-8 max-w-2xl mx-auto">
                        Calculá cuánto menos pagarías en tu factura de luz instalando paneles solares.
                        Solo necesitamos algunos datos básicos de tu propiedad para mostrarte el ahorro potencial.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <!-- Botón Analizar Potencial -->
                        <a href="{{ route('solar.index') }}" 
                           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                            <i class="fas fa-calculator mr-2"></i>
                            Calcular Mi Ahorro
                        </a>
                    </div>
                </div>

            </div>

            <!-- Información Adicional -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-piggy-bank text-green-600 text-3xl mr-3"></i>
                        <h4 class="font-semibold text-gray-800">Reducí tu Factura</h4>
                    </div>
                    <p class="text-sm text-gray-700">
                        Pagá hasta 70% menos en tu factura de electricidad generando tu propia energía limpia.
                    </p>
                </div>

                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-chart-line text-yellow-600 text-3xl mr-3"></i>
                        <h4 class="font-semibold text-gray-800">Inversión Rentable</h4>
                    </div>
                    <p class="text-sm text-gray-700">
                        Recuperá tu inversión en 5-10 años y seguí ahorrando por 20 años más.
                    </p>
                </div>

                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-leaf text-blue-600 text-3xl mr-3"></i>
                        <h4 class="font-semibold text-gray-800">Energía Limpia</h4>
                    </div>
                    <p class="text-sm text-gray-700">
                        Reducí tu huella de carbono mientras ahorrás dinero. Mejor para el planeta y tu bolsillo.
                    </p>
                </div>

            </div>

            <!-- Ayuda -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h5 class="font-semibold text-blue-900 mb-2">¿Cómo funciona?</h5>
                        <p class="text-sm text-blue-800 mb-2">
                            Los paneles solares generan electricidad que usás directamente en tu casa, 
                            reduciendo lo que comprás de la red eléctrica. Menos consumo de red = factura más baja.
                        </p>
                        <p class="text-sm text-blue-800">
                            Nuestro análisis calcula cuánto podrías generar según tu ubicación, área disponible
                            y consumo actual, mostrándote el ahorro real en pesos.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
