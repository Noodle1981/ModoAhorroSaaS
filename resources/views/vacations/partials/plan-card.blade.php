<div class="bg-white rounded-xl shadow-md hover:shadow-xl transition p-6 border-l-4 border-{{ $statusColor }}-500">
    
    <!-- Header -->
    <div class="flex justify-between items-start mb-4">
        <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900 mb-1">
                <i class="fas fa-home text-{{ $statusColor }}-500 mr-2"></i>
                {{ $plan->entity->name }}
            </h3>
            <p class="text-sm text-gray-600">
                <i class="fas fa-calendar-alt mr-1"></i>
                {{ $plan->start_date->format('d/m/Y') }} - {{ $plan->end_date->format('d/m/Y') }}
            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
            @switch($plan->status)
                @case('active')
                    <i class="fas fa-check-circle mr-1"></i>En Curso
                    @break
                @case('pending')
                    <i class="fas fa-clock mr-1"></i>Pendiente
                    @break
                @case('completed')
                    <i class="fas fa-check mr-1"></i>Completado
                    @break
            @endswitch
        </span>
    </div>

    <!-- Días -->
    <div class="bg-{{ $statusColor }}-50 rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-700">Duración</span>
            <span class="text-2xl font-bold text-{{ $statusColor }}-700">
                {{ $plan->days_away }}
                <span class="text-sm font-normal">días</span>
            </span>
        </div>
    </div>

    <!-- Notas -->
    @if($plan->notes)
        <div class="mb-4">
            <p class="text-sm text-gray-600 italic">
                "{{ Str::limit($plan->notes, 80) }}"
            </p>
        </div>
    @endif

    <!-- Acciones -->
    <div class="flex gap-2">
        <a href="{{ route('vacations.recommendations', $plan) }}" 
           class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-{{ $statusColor }}-600 hover:bg-{{ $statusColor }}-700 text-white text-sm font-semibold rounded-lg transition">
            <i class="fas fa-lightbulb mr-2"></i>
            Ver Recomendaciones
        </a>
        
        @if($plan->status !== 'completed')
            <form action="{{ route('vacations.destroy', $plan) }}" method="POST" class="inline"
                  onsubmit="return confirm('¿Eliminar este plan de vacaciones?')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-semibold rounded-lg transition">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        @endif
    </div>

</div>
