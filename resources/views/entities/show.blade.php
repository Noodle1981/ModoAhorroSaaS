<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium inline-flex items-center mb-2">
                    <i class="fas fa-arrow-left mr-2"></i> Volver al Dashboard
                </a>
                <h2 class="text-2xl font-bold text-gray-800">{{ $entity->name }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-tag mr-1"></i> {{ ucfirst($entity->type) }}
                    </span>
                    @if(isset($entity->details['mixed_use']) && $entity->details['mixed_use'])
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-300">
                            <i class="fas fa-store mr-1"></i> Uso Mixto
                        </span>
                    @endif
                    @if($entity->address_street)
                        <span class="text-xs text-gray-600">
                            <i class="fas fa-map-marker-alt mr-1"></i> {{ $entity->address_street }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('entities.insights', $entity) }}" 
                   class="inline-flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-chart-line mr-2"></i> Insights IA
                </a>
                <a href="{{ route('entities.equipment.index', $entity) }}" 
                   class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-bolt mr-2"></i> Inventario
                </a>
                <a href="{{ route('entities.edit', $entity) }}" 
                   class="inline-flex items-center px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-gray-900 text-sm font-semibold rounded-lg transition">
                    <i class="fas fa-edit mr-2"></i> Editar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-4">

        {{-- BANNER: SNAPSHOTS INVALIDADOS --}}
        @php
            $pendingAlerts = \App\Models\SnapshotChangeAlert::where('entity_id', $entity->id)
                ->where('status', 'pending')
                ->count();
            
            $invalidatedSnapshots = \App\Models\EquipmentUsageSnapshot::whereIn('status', ['invalidated', 'draft'])
                ->whereHas('equipment', function($q) use ($entity) {
                    $q->where('entity_id', $entity->id);
                })
                ->count();
        @endphp

        @if($pendingAlerts > 0 || $invalidatedSnapshots > 0)
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border-l-4 border-orange-400 rounded-lg shadow-md p-4 animate-pulse">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-orange-500 text-2xl mt-1"></i>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-semibold text-orange-900 mb-1">
                            ⚠️ Cambios Detectados en Equipos
                        </h3>
                        <p class="text-sm text-orange-800 mb-3">
                            Se detectaron <strong>{{ $pendingAlerts }} {{ Str::plural('cambio', $pendingAlerts) }}</strong> 
                            que afectan <strong>{{ $invalidatedSnapshots }} {{ Str::plural('período', $invalidatedSnapshots) }} histórico{{ $invalidatedSnapshots > 1 ? 's' : '' }}</strong>. 
                            <span class="font-semibold">Debes recalcular los consumos para mantener la precisión del análisis.</span>
                        </p>
                        <a href="{{ route('snapshots.review-changes', $entity) }}" 
                           class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-lg shadow transition">
                            <i class="fas fa-sync-alt mr-2"></i> Revisar y Recalcular Ahora
                        </a>
                    </div>
                    <button onclick="this.parentElement.parentElement.style.display='none'" 
                            class="ml-4 text-orange-400 hover:text-orange-600 transition">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
        @endif

        {{-- SUMINISTROS Y CONTRATOS --}}
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-plug mr-2 text-blue-500"></i> Suministros y Contratos
                </h3>
                <a href="{{ route('entities.supplies.create', $entity) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i> Agregar
                </a>
            </div>
            
            @if($entity->supplies->isEmpty())
                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                    <i class="fas fa-plug text-4xl text-gray-300 mb-2"></i>
                    <p class="text-gray-600 text-sm">No tienes suministros registrados.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($entity->supplies as $supply)
                        <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 flex items-center">
                                        <i class="fas fa-bolt text-yellow-500 mr-2"></i> {{ ucfirst($supply->type) }}
                                    </h4>
                                    <p class="text-xs text-gray-600 mt-1">ID: {{ $supply->supply_point_identifier }}</p>
                                    
                                    @if($supply->contracts->isNotEmpty())
                                        <div class="mt-2 space-y-1.5">
                                            <p class="text-xs font-semibold text-gray-700">Contratos ({{ $supply->contracts->count() }}):</p>
                                            @foreach($supply->contracts as $contract)
                                                <div class="ml-3 p-2 bg-white rounded border border-gray-200">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <div class="flex-1 min-w-0">
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <span class="font-medium text-sm truncate">{{ $contract->rate_name }}</span>
                                                                @if($contract->is_active)
                                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Activo</span>
                                                                @endif
                                                            </div>
                                                            <p class="text-xs text-gray-500 mt-0.5">
                                                                <i class="fas fa-file-invoice mr-1"></i> {{ $contract->invoices->count() }} facturas
                                                            </p>
                                                        </div>
                                                        <div class="flex gap-2 flex-shrink-0">
                                                            <a href="{{ route('contracts.show', $contract) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">Ver</a>
                                                            <a href="{{ route('contracts.invoices.create', $contract) }}" class="text-green-600 hover:text-green-700 text-xs font-medium">
                                                                <i class="fas fa-plus"></i> Factura
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="mt-2 bg-yellow-50 border border-yellow-200 rounded p-2">
                                            <p class="text-xs text-yellow-800">
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Sin contratos. 
                                                <a href="{{ route('supplies.contracts.create', $supply) }}" class="font-semibold underline">Agregar contrato</a>
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('supplies.show', $supply) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium whitespace-nowrap">
                                    Detalles <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ANÁLISIS DE CONSUMO --}}
        @if(isset($entity->details['mixed_use']) && $entity->details['mixed_use'] && $meterAnalysis)
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                <div class="flex gap-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl flex-shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-yellow-800 mb-1">Entidad con Uso Mixto</h4>
                        <p class="text-yellow-700 text-sm leading-relaxed">
                            Esta entidad tiene uso <strong>residencial y comercial</strong>. Los análisis pueden tener menor precisión. 
                            Para análisis profesionales, considera separar las entidades o actualizar al <strong>Plan Gestor</strong>.
                        </p>
                        <a href="#" class="text-blue-600 hover:text-blue-700 font-semibold text-xs mt-2 inline-block">
                            Ver planes profesionales <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @endif

        @if(!$meterAnalysis)
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <i class="fas fa-chart-line text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Empieza a Analizar tu Consumo</h3>
                <p class="text-gray-600 text-sm mb-4 max-w-2xl mx-auto">
                    Para desbloquear el análisis de consumo y las recomendaciones, añade tu información de suministro y carga tu primera factura.
                </p>
                @php
                    $firstSupply = $entity->supplies->first();
                    $firstContract = $firstSupply?->contracts->where('is_active', true)->first() ?? $firstSupply?->contracts->first();
                @endphp
                @if ($firstContract)
                    <a href="{{ route('contracts.invoices.create', $firstContract) }}" 
                       class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition">
                        <i class="fas fa-plus mr-2"></i> Cargar Primera Factura
                    </a>
                @elseif ($firstSupply)
                    <p class="text-sm text-gray-600">
                        El siguiente paso es <a href="{{ route('supplies.contracts.create', $firstSupply) }}" class="text-blue-600 hover:text-blue-700 font-semibold underline">crear un contrato</a>.
                    </p>
                @else
                    <p class="text-sm text-gray-600">
                        Para empezar, <a href="{{ route('entities.supplies.create', $entity) }}" class="text-blue-600 hover:text-blue-700 font-semibold underline">añade un punto de suministro</a>.
                    </p>
                @endif
            </div>
        @else
            {{-- SMART METER --}}
            <div class="mb-4">
                <x-electric-meter :analysis="$meterAnalysis" />
            </div>

            {{-- RESUMEN GENERAL --}}
            @if($summary)
            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border-2 border-blue-300 rounded-lg p-4 shadow-md">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-4">
                    <h3 class="text-lg font-bold text-blue-900">
                        <i class="fas fa-chart-area mr-2"></i> Análisis General Acumulado
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('entities.equipment.index', $entity) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-cog mr-2"></i> Equipamiento
                        </a>
                        <a href="{{ route('entities.reports.improvements', $entity) }}" 
                           class="inline-flex items-center px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            <i class="fas fa-lightbulb mr-2"></i> Informe Mejoras
                        </a>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                    <div class="bg-white rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-600 mb-1">Período Analizado</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $summary->start_date }}</p>
                        <p class="text-xs text-gray-500">{{ $summary->end_date }}</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-600 mb-1">Nivel Acierto</p>
                        <p class="text-2xl font-bold text-blue-600">{{ number_format($summary->average_percentage_explained, 0) }}%</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-600 mb-1">Consumo Real</p>
                        <p class="text-lg font-bold text-gray-800">{{ number_format($summary->total_real_consumption, 0) }}</p>
                        <p class="text-xs text-gray-500">kWh</p>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center">
                        <p class="text-xs text-gray-600 mb-1">Coste Total</p>
                        <p class="text-lg font-bold text-gray-800">${{ number_format($summary->total_amount, 2) }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- HISTORIAL DE PERÍODOS --}}
            @if(!empty($periodsAnalysis))
                <div class="bg-white rounded-lg shadow-md p-4">
                    <h3 class="text-lg font-bold text-gray-800 mb-3 pb-3 border-b">
                        <i class="fas fa-history mr-2 text-purple-500"></i> Historial de Períodos Analizados
                    </h3>
                    <div class="space-y-3">
                        @foreach ($periodsAnalysis as $analysis)
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 mb-1">{{ $analysis->period_label }}</h4>
                                        <p class="text-sm text-gray-700 mb-2">
                                            Tu inventario explica el <strong class="text-lg text-blue-600">{{ number_format($analysis->percentage_explained, 0) }}%</strong> de tu consumo.
                                        </p>
                                        
                                        @if($analysis->percentage_explained < 50)
                                            <div class="bg-red-50 border border-red-200 rounded p-2 text-xs text-red-800">
                                                <strong><i class="fas fa-exclamation-circle mr-1"></i> Atención:</strong> 
                                                Solo explica el {{ number_format($analysis->percentage_explained, 0) }}%. Faltan equipos o ajustar horas de uso.
                                            </div>
                                        @elseif($analysis->percentage_explained < 80)
                                            <div class="bg-yellow-50 border border-yellow-200 rounded p-2 text-xs text-yellow-800">
                                                <strong><i class="fas fa-info-circle mr-1"></i> Consejo:</strong> 
                                                Puedes mejorar la precisión ajustando las horas de uso.
                                            </div>
                                        @elseif($analysis->percentage_explained > 110)
                                            <div class="bg-blue-50 border border-blue-200 rounded p-2 text-xs text-blue-800">
                                                <strong><i class="fas fa-info-circle mr-1"></i> Nota:</strong> 
                                                Explica más del 100%. Las horas pueden estar sobreestimadas.
                                            </div>
                                        @else
                                            <div class="bg-green-50 border border-green-200 rounded p-2 text-xs text-green-800">
                                                <strong><i class="fas fa-check-circle mr-1"></i> Excelente:</strong> 
                                                El análisis es preciso y confiable ({{ number_format($analysis->percentage_explained, 0) }}%).
                                            </div>
                                        @endif

                                        <div class="grid grid-cols-3 gap-2 mt-3 pt-2 border-t border-gray-200">
                                            <div class="text-center">
                                                <p class="text-[10px] text-gray-500 uppercase">Real</p>
                                                <p class="text-sm font-bold text-gray-800">{{ number_format($analysis->real_consumption, 0) }} kWh</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-[10px] text-gray-500 uppercase">Explicado</p>
                                                <p class="text-sm font-bold text-gray-800">{{ number_format($analysis->estimated_consumption, 0) }} kWh</p>
                                            </div>
                                            <div class="text-center">
                                                <p class="text-[10px] text-gray-500 uppercase">Importe</p>
                                                <p class="text-sm font-bold text-gray-800">${{ number_format($analysis->total_amount, 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="{{ route('snapshots.create', $analysis->invoice) }}" 
                                       class="inline-flex items-center justify-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-gray-900 text-sm font-semibold rounded-lg transition whitespace-nowrap">
                                        <i class="fas fa-sliders-h mr-2"></i> Ajustar
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

    </div>
</x-app-layout>
