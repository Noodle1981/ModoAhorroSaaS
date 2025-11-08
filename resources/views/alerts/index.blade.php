<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-bell text-yellow-500 mr-2"></i> Alertas Inteligentes
            </h2>
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-3 rounded">
                <p class="text-green-800 text-sm">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Filtros y resumen --}}
        <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center gap-4">
                    <form method="GET" action="{{ route('alerts.index') }}" class="flex items-center gap-2">
                        <label class="text-sm text-gray-600">Filtro:</label>
                        <select name="filter" class="rounded-md border-gray-300 text-sm" onchange="this.form.submit()">
                            <option value="active" {{ $filter=='active' ? 'selected' : '' }}>Activas</option>
                            <option value="unread" {{ $filter=='unread' ? 'selected' : '' }}>No leídas</option>
                            <option value="all" {{ $filter=='all' ? 'selected' : '' }}>Todas</option>
                        </select>
                    </form>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                        <p class="text-[11px] uppercase text-yellow-700">Activas</p>
                        <p class="text-lg font-bold text-yellow-800">{{ $stats['active'] }}</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded p-3">
                        <p class="text-[11px] uppercase text-blue-700">No leídas</p>
                        <p class="text-lg font-bold text-blue-800">{{ $stats['unread'] }}</p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded p-3">
                        <p class="text-[11px] uppercase text-gray-700">Total</p>
                        <p class="text-lg font-bold text-gray-800">{{ $stats['total'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lista de alertas --}}
        @if($alerts->isEmpty())
            <div class="bg-white rounded-lg p-12 text-center shadow-sm border">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-3"></i>
                <p class="text-gray-600">No hay alertas para mostrar.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($alerts as $alert)
                    <div class="bg-white rounded-lg shadow-sm border p-4 flex gap-3">
                        <div class="mt-1">
                            <i class="fas {{ $alert->icon }} text-{{ $alert->color_class }}-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-900 truncate">{{ $alert->title }}</p>
                                <span class="text-xs text-gray-400">{{ $alert->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-700 mt-1">{{ $alert->description }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $alert->entity?->name ?? 'Sin entidad' }}</p>
                            <div class="mt-3 flex items-center gap-2">
                                @if($alert->type === 'standby_pending')
                                    <a href="{{ route('standby.index') }}" class="px-3 py-1.5 text-xs rounded bg-green-600 text-white hover:bg-green-700">
                                        <i class="fas fa-plug mr-1"></i> Ir a Standby
                                    </a>
                                @elseif($alert->type === 'standby_recommendation_available')
                                    <a href="{{ route('standby.index') }}#recomendaciones" class="px-3 py-1.5 text-xs rounded bg-yellow-600 text-white hover:bg-yellow-700">
                                        <i class="fas fa-lightbulb mr-1"></i> Ver recomendaciones
                                    </a>
                                @elseif($alert->type === 'standby_new_equipment')
                                    <a href="{{ route('standby.index') }}#gestionar-equipos" class="px-3 py-1.5 text-xs rounded bg-blue-600 text-white hover:bg-blue-700">
                                        <i class="fas fa-plug mr-1"></i> Revisar equipo nuevo
                                    </a>
                                @elseif($alert->type === 'usage_pending')
                                    <a href="{{ route('usage.index') }}" class="px-3 py-1.5 text-xs rounded bg-purple-600 text-white hover:bg-purple-700">
                                        <i class="fas fa-calendar-check mr-1"></i> Configurar uso
                                    </a>
                                @elseif($alert->type === 'usage_recommendation_available')
                                    <a href="{{ route('usage.index') }}#recs" class="px-3 py-1.5 text-xs rounded bg-purple-500 text-white hover:bg-purple-600">
                                        <i class="fas fa-calendar-plus mr-1"></i> Ver recomendaciones uso
                                    </a>
                                @elseif($alert->type === 'usage_new_equipment')
                                    <a href="{{ route('usage.index') }}" class="px-3 py-1.5 text-xs rounded bg-indigo-600 text-white hover:bg-indigo-700">
                                        <i class="fas fa-calendar-day mr-1"></i> Definir uso equipo
                                    </a>
                                @endif
                                @if(!$alert->is_read)
                                    <form method="POST" action="{{ route('alerts.read', $alert) }}">
                                        @csrf
                                        <button class="px-3 py-1.5 text-xs rounded bg-blue-600 text-white hover:bg-blue-700">
                                            Marcar leído
                                        </button>
                                    </form>
                                @endif
                                @if(!$alert->is_dismissed)
                                    <form method="POST" action="{{ route('alerts.dismiss', $alert) }}" onsubmit="return confirm('¿Descartar esta alerta?')">
                                        @csrf
                                        <button class="px-3 py-1.5 text-xs rounded bg-gray-600 text-white hover:bg-gray-700">
                                            Descartar
                                        </button>
                                    </form>
                                    @if($alert->type === 'maintenance_due' && isset($alert->data['entity_equipment_id']))
                                        <form method="POST" action="{{ route('alerts.maintenance-complete', $alert) }}" onsubmit="return confirm('¿Registrar mantenimiento ahora y cerrar alerta?')">
                                            @csrf
                                            <button class="px-3 py-1.5 text-xs rounded bg-green-600 text-white hover:bg-green-700">
                                                Registrar mantenimiento
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm border">
                {{ $alerts->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
