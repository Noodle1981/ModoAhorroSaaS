<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <i class="fas fa-edit text-blue-600 mr-2"></i>
                Editar Equipo
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600">
                {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
            </p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <form action="{{ route('equipment.update', $equipment) }}" method="POST" class="p-4 sm:p-6">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Select de Categoría -->
                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">1</span>
                            Categoría
                        </label>
                        <select id="category_id" name="category_id" required 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">-- Selecciona una categoría --</option>
                            @foreach ($categories as $category)
                                <option 
                                    value="{{ $category->id }}" 
                                    data-types="{{ json_encode($category->equipmentTypes) }}"
                                    {{ $equipment->equipmentType->category_id == $category->id ? 'selected' : '' }}
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Select de Tipo de Equipo -->
                    <div>
                        <label for="equipment_type_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">2</span>
                            Tipo de Equipo
                        </label>
                        <select id="equipment_type_id" name="equipment_type_id" required 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">-- Primero selecciona una categoría --</option>
                        </select>
                    </div>

                    <!-- Campo de Ubicación Condicional -->
                    <div id="location-wrapper">
                        <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">3</span>
                            Ubicación
                        </label>
                        <select id="location" name="location" required 
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="">-- Selecciona una ubicación --</option>
                            @forelse ($locations as $locationName)
                                <option value="{{ $locationName }}" {{ (old('location', $equipment->location) == $locationName) ? 'selected' : '' }}>
                                    {{ $locationName }}
                                </option>
                            @empty
                                <option value="" disabled>Primero debes definir las habitaciones en la entidad.</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Grid de Inputs Numéricos (responsive) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                        <!-- Potencia -->
                        <div>
                            <label for="power_watts_override" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">4</span>
                                Potencia (W)
                            </label>
                            <div class="relative">
                                <input type="number" id="power_watts_override" name="power_watts_override" min="0" 
                                       value="{{ old('power_watts_override', $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">W</span>
                            </div>
                        </div>

                        <!-- Minutos de uso -->
                        <div>
                            <label for="avg_daily_use_minutes_override" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">5</span>
                                Minutos/día
                                <span class="ml-2 text-xs font-normal text-gray-500">(solo ajustable en snapshots por período)</span>
                            </label>
                            <div class="relative">
                                <input type="number" id="avg_daily_use_minutes_override" name="avg_daily_use_minutes_override" 
                                       min="0" max="1440" 
                                       value="{{ old('avg_daily_use_minutes_override', $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes) }}"
                                       disabled
                                       title="El tiempo de uso solo se puede ajustar en los snapshots de cada período para mantener la precisión histórica"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">min</span>
                                <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                El tiempo de uso se ajusta por período en las facturas para mantener el histórico preciso.
                            </p>
                        </div>
                    </div>

                    <!-- Standby -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="has_standby_mode" value="1" 
                                   {{ old('has_standby_mode', $equipment->has_standby_mode) ? 'checked' : '' }}
                                   class="mt-1 w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                            <div class="flex-1">
                                <span class="block font-semibold text-gray-900">Activar cálculo de consumo en Standby</span>
                                <span class="block text-xs sm:text-sm text-gray-600 mt-1">
                                    Marca esto solo si este equipo consume algo estando apagado (ej: TV). Si no estás seguro, déjalo desmarcado.
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                        <button type="submit" 
                                class="flex-1 sm:flex-initial px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <i class="fas fa-save mr-2"></i>
                            Actualizar Equipo
                        </button>
                        <a href="{{ route('entities.show', $equipment->entity_id) }}" 
                           class="flex-1 sm:flex-initial px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg text-center transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const typeSelect = document.getElementById('equipment_type_id');
            const locationWrapper = document.getElementById('location-wrapper');
            const locationSelect = document.getElementById('location');
            const initialTypeId = {{ $equipment->equipment_type_id }};

            function populateTypes() {
                typeSelect.innerHTML = '<option value="">-- Selecciona un tipo --</option>';
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                if (selectedOption.value && selectedOption.dataset.types) {
                    const types = JSON.parse(selectedOption.dataset.types);
                    if (types.length > 0) {
                        types.forEach(function (type) {
                            const option = new Option(type.name, type.id);
                            option.dataset.isPortable = type.is_portable ? '1' : '0';
                            if (type.id === initialTypeId) {
                                option.selected = true;
                            }
                            typeSelect.appendChild(option);
                        });
                        typeSelect.disabled = false;
                    } else {
                        typeSelect.innerHTML = '<option value="">-- No hay tipos --</option>';
                        typeSelect.disabled = true;
                    }
                }
            }

            function toggleLocation() {
                const selectedTypeOption = typeSelect.options[typeSelect.selectedIndex];
                if (selectedTypeOption && selectedTypeOption.value) {
                    const isPortable = selectedTypeOption.dataset.isPortable === '1';
                    if (isPortable) {
                        locationWrapper.style.display = 'none';
                        locationSelect.required = false;
                        locationSelect.value = '';
                    } else {
                        locationWrapper.style.display = 'block';
                        locationSelect.required = true;
                    }
                } else {
                    locationWrapper.style.display = 'none';
                    locationSelect.required = false;
                }
            }

            // Popula al cargar la página
            populateTypes();
            toggleLocation();

            // Popula al cambiar la categoría
            categorySelect.addEventListener('change', function() {
                populateTypes();
                toggleLocation();
            });

            typeSelect.addEventListener('change', toggleLocation);
        });
    </script>
</x-app-layout>