<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-exchange-alt mr-2 text-green-600"></i>
                Análisis de Reemplazo de Equipos
            </h2>
            <a href="{{ route('recommendations.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Volver a Recomendaciones
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Mensajes de éxito --}}
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-check-circle text-green-600 mt-1 mr-3"></i>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            {{-- Banner de equipos sin reemplazo --}}
            @if((session('no_match_count') && session('no_match_count') > 0) || (session('insufficient_savings') && session('insufficient_savings') > 0))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-1"></i>
                    <div class="flex-1">
                        <h3 class="font-semibold text-yellow-800 mb-1">Resultados parciales del análisis</h3>
                        @if(session('no_match_count') && session('no_match_count') > 0)
                            <p class="text-sm text-yellow-700">
                                Se analizaron {{ session('analyzed_count') }} equipos. <strong>{{ session('no_match_count') }}</strong> no tienen reemplazo en el catálogo.
                            </p>
                        @endif
                        @if(session('insufficient_savings') && session('insufficient_savings') > 0)
                            <p class="text-sm text-yellow-700 mt-1">
                                <strong>{{ session('insufficient_savings') }}</strong> equipos fueron descartados por ahorro insuficiente (umbral configurado).
                            </p>
                        @endif
                        <p class="text-xs text-yellow-600 mt-2 flex items-center gap-1">
                            <i class="fas fa-lightbulb"></i>
                            Volvé a ejecutar el seeder o agrega más modelos eficientes para esos tipos.
                        </p>
                    </div>
                    <form method="POST" action="{{ route('replacement-recommendations.generate') }}" class="flex items-center">
                        @csrf
                        <input type="hidden" name="entity_id" value="{{ request('entity_id') ?? ($entities->first()->id ?? '') }}">
                        <button class="px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-xs rounded-md">
                            Reintentar Análisis
                        </button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Estadísticas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Pendientes</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ $stats['total_pending'] }}</p>
                        </div>
                        <i class="fas fa-clock text-3xl text-yellow-200"></i>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Aceptadas</p>
                            <p class="text-2xl font-bold text-blue-600">{{ $stats['total_accepted'] }}</p>
                        </div>
                        <i class="fas fa-thumbs-up text-3xl text-blue-200"></i>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">En Recupero</p>
                            <p class="text-2xl font-bold text-purple-600">{{ $stats['total_in_recovery'] }}</p>
                        </div>
                        <i class="fas fa-chart-line text-3xl text-purple-200"></i>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Ahorro Potencial</p>
                            <p class="text-xl font-bold text-green-600">${{ number_format($stats['total_savings_potential'], 0) }}</p>
                            <p class="text-xs text-gray-400">por año</p>
                        </div>
                        <i class="fas fa-piggy-bank text-3xl text-green-200"></i>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Inversión Total</p>
                            <p class="text-xl font-bold text-red-600">${{ number_format($stats['total_investment_required'], 0) }}</p>
                            <p class="text-xs text-gray-400">requerida</p>
                        </div>
                        <i class="fas fa-coins text-3xl text-red-200"></i>
                    </div>
                </div>
                {{-- Nueva tarjeta: ROI promedio --}}
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">ROI Promedio</p>
                            @php
                                $avgRoi = \App\Models\ReplacementRecommendation::pending()->whereNotNull('roi_months')->avg('roi_months');
                            @endphp
                            <p class="text-xl font-bold text-purple-600">
                                {{ $avgRoi ? round($avgRoi,1).' meses' : '—' }}
                            </p>
                            <p class="text-xs text-gray-400">pendientes</p>
                        </div>
                        <i class="fas fa-hourglass-half text-3xl text-purple-200"></i>
                    </div>
                </div>
                {{-- ROI Global (ponderado por totales) --}}
                <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">ROI Global</p>
                            @php
                                $totalSavings = $stats['total_savings_potential'] ?? 0;
                                $totalInvest = $stats['total_investment_required'] ?? 0;
                                $roiGlobal = ($totalSavings > 0 && $totalInvest > 0) ? round(($totalInvest / $totalSavings) * 12, 1) : null;
                            @endphp
                            <p class="text-xl font-bold text-indigo-600">
                                {{ $roiGlobal ? $roiGlobal.' meses' : '—' }}
                            </p>
                            <p class="text-xs text-gray-400">si se ejecuta todo</p>
                        </div>
                        <i class="fas fa-calculator text-3xl text-indigo-200"></i>
                    </div>
                </div>
            </div>

            {{-- Filtros y Generador --}}
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    
                    {{-- Filtro por entidad --}}
                    <form method="GET" action="{{ route('replacement-recommendations.index') }}" class="flex gap-2">
                        <select name="entity_id" class="flex-1 rounded-md border-gray-300" onchange="this.form.submit()">
                            <option value="">Todas las entidades</option>
                            @foreach($entities as $entity)
                            <option value="{{ $entity->id }}" {{ request('entity_id') == $entity->id ? 'selected' : '' }}>
                                {{ $entity->name }}
                            </option>
                            @endforeach
                        </select>
                    </form>

                    {{-- Filtro por estado --}}
                    <form method="GET" action="{{ route('replacement-recommendations.index') }}" class="flex gap-2">
                        <input type="hidden" name="entity_id" value="{{ request('entity_id') }}">
                        <select name="status" class="flex-1 rounded-md border-gray-300" onchange="this.form.submit()">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                            <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Aceptadas</option>
                            <option value="in_recovery" {{ request('status') == 'in_recovery' ? 'selected' : '' }}>En Recupero</option>
                        </select>
                    </form>

                    {{-- Generador de recomendaciones --}}
                    <form method="POST" action="{{ route('replacement-recommendations.generate') }}" class="flex gap-2">
                        @csrf
                        <select name="entity_id" required class="flex-1 rounded-md border-gray-300">
                            <option value="">Seleccionar entidad</option>
                            @foreach($entities as $entity)
                            <option value="{{ $entity->id }}">{{ $entity->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 whitespace-nowrap">
                            <i class="fas fa-magic mr-1"></i> Analizar Ahora
                        </button>
                    </form>

                </div>
            </div>

            {{-- Lista de Recomendaciones --}}
            @if($recommendations->isEmpty())
            <div class="bg-white p-12 rounded-lg shadow-sm text-center">
                <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No hay recomendaciones de reemplazo</p>
                <p class="text-gray-400 text-sm mt-2">Selecciona una entidad y presiona "Generar" para analizar equipos</p>
            </div>
            @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach($recommendations as $rec)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                    
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-4 text-white">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h3 class="font-bold text-lg">{{ $rec->current_equipment_name }}</h3>
                                <p class="text-sm text-green-100">
                                    <i class="fas fa-home mr-1"></i>
                                    {{ $rec->entityEquipment?->entity?->name ?? 'Sin entidad' }}
                                </p>
                            </div>
                            <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                {{ $rec->status === 'pending' ? 'bg-yellow-200 text-yellow-800' : '' }}
                                {{ $rec->status === 'accepted' ? 'bg-blue-200 text-blue-800' : '' }}
                                {{ $rec->status === 'in_recovery' ? 'bg-purple-200 text-purple-800' : '' }}
                            ">
                                @if($rec->status === 'pending') Pendiente
                                @elseif($rec->status === 'accepted') Aceptada
                                @elseif($rec->status === 'in_recovery') En Recupero
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Comparativa --}}
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center p-3 bg-red-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase mb-1">Actual</p>
                                <p class="font-bold text-red-600">{{ number_format($rec->current_annual_kwh, 0) }} kWh/año</p>
                                <p class="text-xs text-gray-500">{{ $rec->current_power_watts }}W</p>
                            </div>
                            <div class="text-center p-3 bg-green-50 rounded-lg">
                                <p class="text-xs text-gray-500 uppercase mb-1">Recomendado</p>
                                <p class="font-bold text-green-600">{{ number_format($rec->recommended_annual_kwh, 0) }} kWh/año</p>
                                <p class="text-xs text-gray-500">{{ $rec->recommended_power_watts }}W · {{ $rec->recommended_energy_label }}</p>
                            </div>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg mb-4">
                            <p class="text-sm font-semibold text-blue-800 mb-1">
                                <i class="fas fa-arrow-right mr-1"></i>
                                {{ $rec->recommended_equipment_name }}
                            </p>
                            @if($rec->marketEquipment?->purchase_link)
                            <p class="text-xs text-blue-600">
                                <i class="fas fa-link mr-1"></i>
                                <a href="{{ $rec->marketEquipment->purchase_link }}" target="_blank" class="underline hover:text-blue-800">
                                    Ver producto
                                </a>
                            </p>
                            @endif
                        </div>

                        {{-- ROI --}}
                        <div class="border-t pt-4">
                            <div class="grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-gray-500">Ahorro anual</p>
                                    <p class="font-bold text-green-600">${{ number_format($rec->money_saved_per_year, 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Inversión</p>
                                    <p class="font-bold text-red-600">${{ number_format($rec->investment_required, 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">ROI</p>
                                    <p class="font-bold text-purple-600">{{ $rec->roi_months }} meses</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Ahorro</p>
                                    <p class="font-bold text-green-600">{{ $rec->savings_percentage }}%</p>
                                </div>
                            </div>

                            @if($rec->is_good_investment)
                            <div class="mt-3 p-2 bg-green-50 rounded text-center">
                                <p class="text-xs font-semibold text-green-700">
                                    <i class="fas fa-thumbs-up mr-1"></i>
                                    Excelente inversión (ROI < 5 años)
                                </p>
                            </div>
                            @endif
                        </div>

                        {{-- Acciones --}}
                        <div class="mt-4 flex gap-2">
                            @if($rec->status === 'pending')
                            <form method="POST" action="{{ route('replacement-recommendations.accept', $rec) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                                    <i class="fas fa-check mr-1"></i> Aceptar
                                </button>
                            </form>
                            {{-- Exportar a PDF individual --}}
                            <form method="GET" action="{{ route('replacement-recommendations.export', $rec) }}" class="flex-1">
                                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">
                                    <i class="fas fa-file-pdf mr-1"></i> PDF
                                </button>
                            </form>
                            <form method="POST" action="{{ route('replacement-recommendations.reject', $rec) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500 text-sm"
                                    onclick="return confirm('¿Descartar esta recomendación?')">
                                    <i class="fas fa-times mr-1"></i> Descartar
                                </button>
                            </form>
                            @elseif($rec->status === 'accepted')
                            <button onclick="showRecoveryModal({{ $rec->id }})" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-sm">
                                <i class="fas fa-play mr-1"></i> Iniciar Seguimiento ROI
                            </button>
                            @elseif($rec->status === 'in_recovery')
                            <div class="flex-1 text-center">
                                <p class="text-xs text-gray-500">Recupero estimado:</p>
                                <p class="font-semibold text-purple-600">{{ $rec->estimated_recovery_date->format('d/m/Y') }}</p>
                            </div>
                            <form method="POST" action="{{ route('replacement-recommendations.complete', $rec) }}" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm"
                                    onclick="return confirm('¿Confirmar que la inversión fue completamente recuperada?')">
                                    <i class="fas fa-trophy mr-1"></i> Marcar Completada
                                </button>
                            </form>
                            @endif
                        </div>

                    </div>
                </div>
                @endforeach
            </div>

            {{-- Paginación --}}
            <div class="bg-white p-4 rounded-lg shadow-sm">
                {{ $recommendations->links() }}
            </div>
            @endif

        </div>
    </div>

    {{-- Modal para iniciar seguimiento ROI --}}
    <div id="recoveryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-bold mb-4">Iniciar Seguimiento de Recupero</h3>
            <form id="recoveryForm" method="POST" action="">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Fecha de compra del equipo nuevo
                    </label>
                    <input type="date" name="start_date" required 
                        class="w-full rounded-md border-gray-300" 
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                        Iniciar Seguimiento
                    </button>
                    <button type="button" onclick="closeRecoveryModal()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showRecoveryModal(recommendationId) {
            const modal = document.getElementById('recoveryModal');
            const form = document.getElementById('recoveryForm');
            form.action = `/replacement-recommendations/${recommendationId}/start-recovery`;
            modal.classList.remove('hidden');
        }

        function closeRecoveryModal() {
            document.getElementById('recoveryModal').classList.add('hidden');
        }
    </script>
    {{-- Botón flotante exportar todas --}}
    <div class="fixed bottom-6 right-6 z-40">
        <form method="GET" action="{{ route('replacement-recommendations.export-all') }}">
            <button type="submit" class="flex items-center gap-2 px-4 py-3 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700">
                <i class="fas fa-file-download"></i>
                <span class="text-sm font-semibold">Exportar Todo PDF</span>
            </button>
        </form>
    </div>
</x-app-layout>
