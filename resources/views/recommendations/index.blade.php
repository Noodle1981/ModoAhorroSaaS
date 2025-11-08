<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Centro de Recomendaciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <p class="text-gray-600 mb-6">
                    Aquí encontrarás herramientas y recomendaciones para optimizar tu consumo energético.
                </p>

                <!-- Grid de Cards de Recomendaciones -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Card: Gestión de Standby -->
                    <a href="{{ route('standby.index') }}" class="block p-6 bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-power-off text-3xl text-purple-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-purple-200 text-purple-800 rounded-full">Consumo Fantasma</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Gestión de Standby</h3>
                        <p class="text-sm text-gray-600">
                            Controla qué equipos calculan consumo en modo de espera. Reduce el consumo fantasma.
                        </p>
                    </a>

                    <!-- Card: Mantenimiento Preventivo -->
                    <a href="{{ route('maintenance.index') }}" class="block p-6 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-wrench text-3xl text-blue-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-blue-200 text-blue-800 rounded-full">Mantenimiento</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Mantenimiento Preventivo</h3>
                        <p class="text-sm text-gray-600">
                            Programa y realiza seguimiento del mantenimiento de tus equipos para maximizar eficiencia.
                        </p>
                    </a>

                    <!-- Card: Energía Solar -->
                    <a href="{{ route('solar.dashboard') }}" class="block p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-sun text-3xl text-yellow-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-yellow-200 text-yellow-800 rounded-full">Ahorro en Factura</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Energía Solar</h3>
                        <p class="text-sm text-gray-600">
                            Calculá cuánto menos pagarías en tu factura de luz con paneles solares en tu techo.
                        </p>
                    </a>

                    <!-- Card: Calefón Solar -->
                    <a href="{{ route('solar-heater.index') }}" class="block p-6 bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-fire text-3xl text-orange-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-orange-200 text-orange-800 rounded-full">Agua Caliente</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Calefón Solar</h3>
                        <p class="text-sm text-gray-600">
                            Reemplazá tu calefón eléctrico/gas por uno solar y ahorrá hasta 70% en agua caliente.
                        </p>
                    </a>

                    <!-- Card: Paneles Solares -->
                    <a href="{{ route('solar-panel.index') }}" class="block p-6 bg-gradient-to-br from-cyan-50 to-cyan-100 border border-cyan-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-solar-panel text-3xl text-cyan-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-cyan-200 text-cyan-800 rounded-full">Generación</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Paneles Solares</h3>
                        <p class="text-sm text-gray-600">
                            Calculá el potencial de generación solar en tu techo y conocé la viabilidad.
                        </p>
                    </a>

                    <!-- Card: Modo Vacaciones -->
                    <a href="{{ route('vacations.index') }}" class="block p-6 bg-gradient-to-br from-teal-50 to-teal-100 border border-teal-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-plane-departure text-3xl text-teal-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-teal-200 text-teal-800 rounded-full">Ahorro Automático</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Modo Vacaciones</h3>
                        <p class="text-sm text-gray-600">
                            Planificá tus ausencias y recibí recomendaciones sobre qué equipos apagar antes de irte.
                        </p>
                    </a>

                    <!-- Card: Optimización de Horarios (ACTIVO) -->
                    <a href="{{ route('schedule-optimization.index') }}" class="block p-6 bg-gradient-to-br from-gray-50 to-blue-50 border border-blue-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-clock text-3xl text-blue-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-blue-200 text-blue-800 rounded-full">Hábitos</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Optimización de Horarios</h3>
                        <p class="text-sm text-gray-600">
                            Descubrí cuándo conviene usar tu lavarropas para reducir consumo y pagar menos (fin de semana y tarifa reducida).
                        </p>
                    </a>

                    <!-- Card: Análisis de Reemplazo -->
                    <a href="{{ route('replacement-recommendations.index') }}" class="block p-6 bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg hover:shadow-lg transition-all hover:scale-105">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-exchange-alt text-3xl text-green-600"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-green-200 text-green-800 rounded-full">ROI Automático</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800 mb-2">Análisis de Reemplazo</h3>
                        <p class="text-sm text-gray-600">
                            Identifica equipos ineficientes y calcula el ROI de reemplazarlos.
                        </p>
                    </a>

                    <!-- Card: Placeholder - Gamificación -->
                    <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-300 rounded-lg opacity-60">
                        <div class="flex items-center justify-between mb-4">
                            <i class="fas fa-trophy text-3xl text-gray-400"></i>
                            <span class="px-2 py-1 text-xs font-semibold bg-gray-300 text-gray-600 rounded-full">Próximamente</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-500 mb-2">Desafíos de Ahorro</h3>
                        <p class="text-sm text-gray-500">
                            Completa desafíos semanales y gana insignias por reducir tu consumo.
                        </p>
                    </div>

                </div>

                <!-- Mensaje informativo -->
                <div class="mt-8 p-4 bg-blue-50 border-l-4 border-blue-400 text-blue-700">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">
                                <strong>¿Necesitás ayuda?</strong> Cada recomendación incluye explicaciones detalladas para que tomes las mejores decisiones energéticas.
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
