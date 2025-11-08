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

                    <!-- Nombre Personalizado (Opcional) -->
                    <div>
                        <label for="custom_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">3</span>
                            Nombre Personalizado
                            <span class="ml-2 text-xs font-normal text-gray-500">(opcional)</span>
                        </label>
                        <input type="text" id="custom_name" name="custom_name" maxlength="100"
                               value="{{ old('custom_name', $equipment->custom_name) }}"
                               placeholder="Ej: TV del Salón, Heladera Principal..."
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <p class="mt-1 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Si no lo completas, se usará el nombre del tipo de equipo
                        </p>
                    </div>

                    <!-- Campo de Ubicación Condicional -->
                    <div id="location-wrapper">
                        <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">4</span>
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
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">5</span>
                                Potencia (W)
                            </label>
                            <div class="relative">
                                <input type="number" id="power_watts_override" name="power_watts_override" min="0" 
                                       value="{{ old('power_watts_override', $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">W</span>
                            </div>
                        </div>

                        <!-- Cantidad -->
                        <div>
                            <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">6</span>
                                Cantidad
                            </label>
                            <div class="relative">
                                <input type="number" id="quantity" name="quantity" min="1" required
                                       value="{{ old('quantity', $equipment->quantity ?? 1) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">uds</span>
                            </div>
                        </div>
                    </div>

                    <!-- Minutos de uso (AUTO-CALCULADO) -->
                    <div>
                        <label for="avg_daily_use_minutes_override" class="block text-sm font-semibold text-gray-700 mb-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold mr-2">7</span>
                            Minutos/día
                            <span class="ml-2 text-xs font-normal text-gray-500">(se calcula automáticamente según frecuencia)</span>
                        </label>
                            <div class="relative">
                                <input type="hidden" id="avg_daily_use_minutes_override" name="avg_daily_use_minutes_override" 
                                       value="{{ old('avg_daily_use_minutes_override', $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes) }}">
                                <input type="number" id="avg_daily_use_minutes_display" 
                                       min="0" max="1440" 
                                       value="{{ old('avg_daily_use_minutes_override', $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes) }}"
                                       readonly
                                       title="Este valor se calcula automáticamente según la frecuencia de uso configurada"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">min</span>
                                <i class="fas fa-calculator absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                Se calcula automáticamente según la frecuencia de uso configurada abajo.
                            </p>

                        <!-- Frecuencia de Uso -->
                        <fieldset class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <legend class="text-sm font-semibold text-gray-700 px-2">Frecuencia de Uso</legend>
                            <label class="flex items-center gap-2 text-sm mt-2">
                                <input type="hidden" name="is_daily_use" value="0">
                                <input type="checkbox" name="is_daily_use" id="is_daily_use" value="1" {{ old('is_daily_use', $equipment->is_daily_use ?? true) ? 'checked' : '' }} class="rounded border-gray-300">
                                <span>✅ Uso diario (todos los días)</span>
                            </label>
                            <div id="non-daily-frequency" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4" style="display:none;">
                                <div>
                                    <label for="usage_days_per_week" class="block text-xs font-medium text-gray-600 mb-1">Días por semana</label>
                                    <input type="number" name="usage_days_per_week" id="usage_days_per_week" min="0" max="7" value="{{ old('usage_days_per_week', $equipment->usage_days_per_week) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" />
                                </div>
                                <div>
                                    <label for="minutes_per_session" class="block text-xs font-medium text-gray-600 mb-1">Minutos por sesión/ciclo</label>
                                    <input type="number" name="minutes_per_session" id="minutes_per_session" min="0" max="1440" value="{{ old('minutes_per_session', $equipment->minutes_per_session) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ej: 120" />
                                    <small class="text-[10px] text-gray-500 block mt-1">Duración de un ciclo (ej: lavado completo)</small>
                                </div>
                                <div class="sm:col-span-2">
                                    <span class="block text-xs font-semibold text-gray-600 mb-1">Días específicos:</span>
                                    <div class="flex flex-wrap gap-2">
                                        @php($weekMap = [1=>'Lun',2=>'Mar',3=>'Mié',4=>'Jue',5=>'Vie',6=>'Sáb',7=>'Dom'])
                                        @foreach ($weekMap as $dNum => $dLabel)
                                            <label class="flex items-center gap-1 text-xs px-2 py-1 border border-gray-300 rounded cursor-pointer hover:bg-blue-50">
                                                <input type="checkbox" name="usage_weekdays[]" value="{{ $dNum }}" 
                                                    {{ in_array($dNum, old('usage_weekdays', $equipment->usage_weekdays ?? [])) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 weekday-checkbox" />
                                                <span>{{ $dLabel }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <p class="mt-1 text-[10px] text-gray-500">💡 Si seleccionas días, recalcularemos días/semana automáticamente.</p>
                                </div>
                                <div id="derived-daily-minutes" class="sm:col-span-2 text-[11px] text-green-700 font-medium bg-green-50 px-3 py-2 rounded" style="display:none;">
                                    📊 Promedio diario derivado: <strong><span id="derived-daily-value">0</span> min/día</strong>
                                </div>
                            </div>
                        </fieldset>
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
                        <a href="{{ route('entities.equipment.index', $equipment->entity_id) }}" 
                           class="flex-1 sm:flex-initial px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg text-center transition-all duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver a Equipos
                        </a>
                        <a href="{{ route('entities.equipment.index', $equipment->entity_id) }}" 
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

            // Frecuencia
            const isDailyCheckbox = document.getElementById('is_daily_use');
            const nonDailyWrapper = document.getElementById('non-daily-frequency');
            const usageDaysInput = document.getElementById('usage_days_per_week');
            const minutesPerSessionInput = document.getElementById('minutes_per_session');
            const weekdayCheckboxes = document.querySelectorAll('.weekday-checkbox');
            const derivedDailyBox = document.getElementById('derived-daily-minutes');
            const derivedDailyValue = document.getElementById('derived-daily-value');
            const avgDailyHidden = document.getElementById('avg_daily_use_minutes_override');
            const avgDailyDisplay = document.getElementById('avg_daily_use_minutes_display');
            
            function updateFrequencyVisibility(){
                if(isDailyCheckbox.checked){
                    nonDailyWrapper.style.display='none';
                }else{
                    nonDailyWrapper.style.display='';
                }
                computeDerivedDaily();
            }
            
            function computeDerivedDaily(){
                if(isDailyCheckbox.checked){
                    derivedDailyBox.style.display='none';
                    return;
                }
                let days=usageDaysInput.value?parseInt(usageDaysInput.value,10):0;
                let weekdayCount=0;
                weekdayCheckboxes.forEach(cb=>{if(cb.checked)weekdayCount++;});
                if(weekdayCount>0){
                    days=weekdayCount;
                    usageDaysInput.value=days;
                }
                let sessionMin=minutesPerSessionInput.value?parseInt(minutesPerSessionInput.value,10):0;
                if(days>0&&sessionMin>0){
                    const derived=Math.round((days*sessionMin)/7);
                    derivedDailyValue.textContent=derived;
                    derivedDailyBox.style.display='';
                    // Sincronizar con campo hidden y display
                    avgDailyHidden.value=derived;
                    avgDailyDisplay.value=derived;
                }else{
                    derivedDailyBox.style.display='none';
                }
            }
            
            isDailyCheckbox.addEventListener('change',updateFrequencyVisibility);
            usageDaysInput&&usageDaysInput.addEventListener('input',computeDerivedDaily);
            minutesPerSessionInput&&minutesPerSessionInput.addEventListener('input',computeDerivedDaily);
            weekdayCheckboxes.forEach(cb=>cb.addEventListener('change',computeDerivedDaily));
            updateFrequencyVisibility();
        });
    </script>
</x-app-layout>
