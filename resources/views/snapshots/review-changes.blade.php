@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Encabezado -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                    Cambios Detectados en Equipos
                </h1>
                <p class="text-sm text-gray-600 mt-1">
                    Se detectaron modificaciones que requieren recalcular los consumos históricos de <strong>{{ $entity->name }}</strong>
                </p>
            </div>
            <a href="{{ route('entities.show', $entity) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i> Volver
            </a>
        </div>
    </div>

    <!-- Alertas Pendientes -->
    @if($pendingAlerts->isNotEmpty())
        <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6 rounded">
            <div class="flex items-start">
                <i class="fas fa-bell text-orange-500 mt-1 mr-3"></i>
                <div class="flex-1">
                    <h3 class="font-semibold text-orange-900 mb-2">
                        {{ $pendingAlerts->count() }} {{ Str::plural('cambio', $pendingAlerts->count()) }} detectado{{ $pendingAlerts->count() > 1 ? 's' : '' }}
                    </h3>
                    <div class="space-y-2">
                        @foreach($pendingAlerts as $alert)
                            <div class="bg-white rounded p-3 shadow-sm">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $alert->message }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-clock mr-1"></i> {{ $alert->created_at->diffForHumans() }}
                                        </p>
                                        
                                        @if($alert->historyRecord)
                                            <div class="mt-2 text-xs">
                                                <span class="font-semibold text-gray-700">Detalle del cambio:</span>
                                                <span class="text-gray-600">{{ $alert->historyRecord->getReadableDescription() }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="ml-3 px-2 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded">
                                        {{ $alert->getAffectedSnapshotsCount() }} período{{ $alert->getAffectedSnapshotsCount() > 1 ? 's' : '' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Botón Recalcular Todo -->
                    <form action="{{ route('snapshots.recalculate-all', $entity) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-sync-alt mr-2"></i> Recalcular Todos los Períodos Afectados
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-3"></i>
                <p class="text-green-900 font-medium">✅ No hay cambios pendientes de procesar</p>
            </div>
        </div>
    @endif

    <!-- Snapshots Invalidados por Período -->
    @if($invalidatedSnapshots->isNotEmpty())
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-history mr-2"></i> Períodos con Snapshots Invalidados
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    @foreach($invalidatedSnapshots as $date => $snapshots)
                        @php
                            $periodDate = \Carbon\Carbon::parse($date);
                        @endphp
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <!-- Encabezado del período -->
                            <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                                <div>
                                    <h3 class="font-semibold text-gray-900">
                                        {{ $periodDate->translatedFormat('F Y') }}
                                    </h3>
                                    <p class="text-xs text-gray-600">{{ $snapshots->count() }} equipo{{ $snapshots->count() > 1 ? 's' : '' }}</p>
                                </div>
                                <form action="{{ route('snapshots.recalculate-period', [$entity, $date]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-calculator mr-1"></i> Recalcular Período
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Tabla de equipos -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado Actual</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razón de Invalidación</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Consumo Anterior</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recálculos</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($snapshots as $snapshot)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-plug text-gray-400 mr-2"></i>
                                                        <div>
                                                            <p class="text-sm font-medium text-gray-900">
                                                                {{ $snapshot->equipment->custom_name ?? $snapshot->equipment->equipmentType->name }}
                                                            </p>
                                                            <p class="text-xs text-gray-500">
                                                                {{ $snapshot->equipment->equipmentType->category->name }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="text-xs">
                                                        <p><strong>Potencia:</strong> {{ $snapshot->equipment->power_watts_override ?? $snapshot->equipment->equipmentType->default_power_watts }} W</p>
                                                        <p><strong>Uso:</strong> {{ $snapshot->equipment->avg_daily_use_minutes_override ?? $snapshot->equipment->equipmentType->default_avg_daily_use_minutes }} min/día</p>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($snapshot->status === 'invalidated')
                                                        <div class="text-xs">
                                                            <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-700 font-medium mb-1">
                                                                <i class="fas fa-times-circle mr-1"></i> Invalidado
                                                            </span>
                                                            <p class="text-gray-600 mt-1">{{ $snapshot->invalidation_reason }}</p>
                                                            <p class="text-gray-500 mt-1">
                                                                <i class="fas fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($snapshot->invalidated_at)->diffForHumans() }}
                                                            </p>
                                                        </div>
                                                    @else
                                                        <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs font-medium">
                                                            <i class="fas fa-edit mr-1"></i> Borrador
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    @if($snapshot->calculated_period_kwh)
                                                        <p class="font-mono">{{ number_format($snapshot->calculated_period_kwh, 2) }} kWh</p>
                                                        <p class="text-xs text-gray-500">({{ $snapshot->power_watts }}W × {{ $snapshot->avg_daily_use_minutes }}min)</p>
                                                    @else
                                                        <span class="text-gray-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-sm text-center">
                                                    @if($snapshot->recalculation_count > 0)
                                                        <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-semibold">
                                                            {{ $snapshot->recalculation_count }}×
                                                        </span>
                                                    @else
                                                        <span class="text-gray-400">0</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <form action="{{ route('snapshots.recalculate', $snapshot) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                            <i class="fas fa-sync mr-1"></i> Recalcular
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Historial de Recálculos Recientes -->
    @if($recentRecalculations->isNotEmpty())
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-check-double mr-2"></i> Recálculos Recientes (últimos 30 días)
                </h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Equipo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Período</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Consumo Recalculado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Veces Recalculado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Última Actualización</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentRecalculations as $snapshot)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        {{ $snapshot->equipment->custom_name ?? $snapshot->equipment->equipmentType->name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ \Carbon\Carbon::parse($snapshot->snapshot_date)->translatedFormat('F Y') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-sm font-semibold text-green-600">
                                            {{ number_format($snapshot->calculated_period_kwh, 2) }} kWh
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center">
                                        <span class="inline-flex items-center px-2 py-1 rounded bg-blue-100 text-blue-700 text-xs font-semibold">
                                            {{ $snapshot->recalculation_count }}×
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <i class="fas fa-clock mr-1"></i> {{ $snapshot->updated_at->diffForHumans() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
