<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nuevo Plan de Vacaciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
    
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('vacations.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold mb-4 inline-block">
            <i class="fas fa-arrow-left mr-2"></i>
            Volver a Planes
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">
            <i class="fas fa-suitcase-rolling text-blue-600 mr-3"></i>
            Nuevo Plan de Vacaciones
        </h1>
        <p class="text-gray-600 mt-2">
            Ingresá las fechas de tu ausencia para recibir recomendaciones personalizadas
        </p>
    </div>

    <!-- Formulario -->
    <div class="bg-white rounded-xl shadow-md p-8">
        <form action="{{ route('vacations.store') }}" method="POST" id="vacation-form">
            @csrf

            <!-- Seleccionar Entidad -->
            <div class="mb-6">
                <label for="entity_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-home text-blue-500 mr-2"></i>
                    ¿Qué propiedad vas a dejar sola?
                </label>
                <select 
                    name="entity_id" 
                    id="entity_id" 
                    required
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                >
                    <option value="">Seleccionar propiedad...</option>
                    @foreach($entities as $entity)
                        <option value="{{ $entity->id }}" {{ old('entity_id') == $entity->id ? 'selected' : '' }}>
                            {{ $entity->name }}
                        </option>
                    @endforeach
                </select>
                @error('entity_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                <!-- Fecha de Salida -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-plane-departure text-green-500 mr-2"></i>
                        Fecha de Salida
                    </label>
                    <input 
                        type="date" 
                        name="start_date" 
                        id="start_date" 
                        required
                        min="{{ now()->format('Y-m-d') }}"
                        value="{{ old('start_date') }}"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        onchange="calculateDays()"
                    >
                    @error('start_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fecha de Regreso -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-plane-arrival text-blue-500 mr-2"></i>
                        Fecha de Regreso
                    </label>
                    <input 
                        type="date" 
                        name="end_date" 
                        id="end_date" 
                        required
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        value="{{ old('end_date') }}"
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        onchange="calculateDays()"
                    >
                    @error('end_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Preview de días -->
            <div id="days-preview" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-700">
                        <i class="fas fa-calendar-check text-blue-600 mr-2"></i>
                        Estarás fuera durante
                    </span>
                    <span class="text-2xl font-bold text-blue-700">
                        <span id="days-count">0</span> días
                    </span>
                </div>
            </div>

            <!-- Notas -->
            <div class="mb-6">
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-yellow-500 mr-2"></i>
                    Notas (opcional)
                </label>
                <textarea 
                    name="notes" 
                    id="notes" 
                    rows="3"
                    placeholder="Ej: Viaje familiar a Mar del Plata, dejar alarma activada..."
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botones -->
            <div class="flex gap-4">
                <button 
                    type="submit"
                    class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg transition"
                >
                    <i class="fas fa-check mr-2"></i>
                    Crear Plan y Ver Recomendaciones
                </button>
                <a 
                    href="{{ route('vacations.index') }}"
                    class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
                >
                    Cancelar
                </a>
            </div>

        </form>
    </div>

    <!-- Información -->
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-bold text-blue-900 mb-3">
            <i class="fas fa-info-circle mr-2"></i>
            ¿Cómo funciona?
        </h3>
        <ul class="space-y-2 text-sm text-blue-800">
            <li class="flex items-start">
                <i class="fas fa-check text-blue-600 mr-3 mt-1"></i>
                <span>Después de crear el plan, recibirás recomendaciones personalizadas sobre qué equipos apagar</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check text-blue-600 mr-3 mt-1"></i>
                <span>Las sugerencias varían según la duración: viajes cortos vs largos tienen diferentes estrategias</span>
            </li>
            <li class="flex items-start">
                <i class="fas fa-check text-blue-600 mr-3 mt-1"></i>
                <span>Podés calcular cuánto ahorrarías en energía durante tu ausencia</span>
            </li>
        </ul>
    </div>

</div>

<script>
function calculateDays() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        if (diffDays > 0) {
            document.getElementById('days-count').textContent = diffDays;
            document.getElementById('days-preview').classList.remove('hidden');
        } else {
            document.getElementById('days-preview').classList.add('hidden');
        }
    }
}

// Actualizar min de end_date cuando cambia start_date
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = this.value;
    if (startDate) {
        const minEndDate = new Date(startDate);
        minEndDate.setDate(minEndDate.getDate() + 1);
        document.getElementById('end_date').min = minEndDate.toISOString().split('T')[0];
    }
});
</script>
</div>
</div>
</x-app-layout>
