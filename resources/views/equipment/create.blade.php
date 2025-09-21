<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Añadir Equipo a: {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <p class="mb-4">
                        <a href="{{ route('entities.show', $entity) }}" style="color: #3b82f6; text-decoration: underline;">&larr; Volver a la Entidad</a>
                    </p>

                    <!-- Muestra Errores de Validación -->
                    @if ($errors->any())
                        <div style="margin-bottom: 1rem; padding: 1rem; background-color: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 0.5rem;">
                            <strong>¡Ups! Hubo algunos problemas.</strong>
                            <ul style="margin-top: 0.75rem; list-style-type: disc; list-style-position: inside; font-size: 0.875rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Formulario de Creación -->
                    <form action="{{ route('entities.equipment.store', $entity) }}" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        @csrf
                        
                        <!-- 1. Categoría -->
                        <div>
                            <label for="category_id">1. Selecciona la Categoría</label>
                            <select id="category_id" name="category_id" required style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;">
                                <option value="">-- Selecciona una categoría --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-types="{{ json_encode($category->equipmentTypes) }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Tipo de Equipo -->
                        <div>
                            <label for="equipment_type_id">2. Selecciona el Tipo de Equipo</label>
                            <select id="equipment_type_id" name="equipment_type_id" required style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;" disabled>
                                <option value="">-- Primero selecciona una categoría --</option>
                            </select>
                        </div>
                        
                        <!-- 3. Ubicación (Condicional) -->
                        <div id="location-wrapper" style="display: none;">
                            <label for="location">3. Asigna una Ubicación</label>
                            <select id="location" name="location" style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;">
                                <option value="">-- Selecciona una ubicación --</option>
                                @forelse ($locations as $locationName)
                                    <option value="{{ $locationName }}" {{ old('location') == $locationName ? 'selected' : '' }}>
                                        {{ $locationName }}
                                    </option>
                                @empty
                                    <option value="" disabled>Primero debes definir las habitaciones en la entidad.</option>
                                @endforelse
                            </select>
                        </div>

                        <!-- 4. Nombre Personalizado -->
                        <div>
                            <label for="custom_name">4. Nombre Personalizado (Opcional)</label>
                            <input type="text" id="custom_name" name="custom_name" value="{{ old('custom_name') }}" style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;" placeholder="Ej: Heladera del quincho">
                        </div>

                        <!-- 5. Potencia -->
                        <div>
                            <label for="power_watts_override">5. Potencia (en Watts)</label>
                            <input type="number" id="power_watts_override" name="power_watts_override" value="{{ old('power_watts_override') }}" required min="0" style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;" placeholder="Se rellenará automáticamente">
                            <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">Busca este valor en la etiqueta de tu equipo (ej: 1500W). Es clave para la precisión.</p>
                        </div>
                        
                        <!-- 6. Cantidad -->
                         <div>
                            <label for="quantity">6. Cantidad</label>
                            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required min="1" style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;">
                        </div>

                        <!-- 7. Tiempo de Uso (Adaptativo) -->
                        <div id="usage-time-wrapper">
                            <div id="hours-input-wrapper" style="display: none;">
                                <label for="avg_daily_use_hours_override">7. Horas de Uso Promedio por Día (Editable)</label>
                                <input type="number" step="0.1" id="avg_daily_use_hours_override" name="avg_daily_use_hours_override" value="{{ old('avg_daily_use_hours_override') }}" min="0" max="24" style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;" placeholder="Valor sugerido, puedes cambiarlo">
                                <small style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">Hemos rellenado un valor promedio, ajústalo a tu realidad.</small>
                            </div>
                            <div id="minutes-input-wrapper" style="display: none;">
                                <label for="avg_daily_use_minutes_override">7. Minutos de Uso Promedio por Día (Editable)</label>
                                <input type="number" id="avg_daily_use_minutes_override" name="avg_daily_use_minutes_override" value="{{ old('avg_daily_use_minutes_override') }}" min="0" max="1440" style="width: 100%; margin-top: 0.25rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.5rem;" placeholder="Valor sugerido, puedes cambiarlo">
                                <small style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">Hemos rellenado un valor promedio, ajústalo a tu realidad.</small>
                            </div>
                        </div>

                        <!-- 8. Standby (Condicional) -->
                        <div id="standby-wrapper" style="display: none; margin-top: 1rem;">
                            <label for="has_standby_mode" style="display: inline-flex; align-items: center;">
                                <input type="checkbox" id="has_standby_mode" name="has_standby_mode" value="1" {{ old('has_standby_mode') ? 'checked' : '' }} style="border-radius: 0.25rem; border-color: #d1d5db; color: #4f46e5;">
                                <span style="margin-left: 0.5rem; font-size: 0.875rem;"><strong>Activar cálculo de consumo en Standby</strong></span>
                            </label>
                            <p style="font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;">
                                <strong>¿Qué es?</strong> Es la energía que consume un aparato al estar "apagado" pero enchufado (consumo fantasma). Márcalo para equipos como TV, consolas, etc. No lo marques para equipos que se desenchufan o funcionan 24h (ej: heladera).
                            </p>
                        </div>

                        <!-- Botón de Guardar -->
                        <div style="display: flex; align-items: center; justify-content: flex-end; margin-top: 1.5rem;">
                            <button type="submit" style="padding: 0.5rem 1rem; background-color: #16a34a; border: 1px solid transparent; border-radius: 0.375rem; font-weight: 600; font-size: 0.75rem; color: white; text-transform: uppercase;">
                                Guardar Equipo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const typeSelect = document.getElementById('equipment_type_id');
            const locationWrapper = document.getElementById('location-wrapper');
            const locationSelect = document.getElementById('location');
            const powerInput = document.getElementById('power_watts_override');
            const standbyWrapper = document.getElementById('standby-wrapper');
            const hoursInputWrapper = document.getElementById('hours-input-wrapper');
            const hoursInput = document.getElementById('avg_daily_use_hours_override');
            const minutesInputWrapper = document.getElementById('minutes-input-wrapper');
            const minutesInput = document.getElementById('avg_daily_use_minutes_override');

            let allTypes = {};

            categorySelect.addEventListener('change', function () {
                typeSelect.innerHTML = '<option value="">-- Selecciona un tipo --</option>';
                typeSelect.disabled = true;

                // Resetear todos los campos dependientes
                locationWrapper.style.display = 'none';
                locationSelect.required = false;
                standbyWrapper.style.display = 'none';
                hoursInputWrapper.style.display = 'none';
                minutesInputWrapper.style.display = 'none';
                powerInput.value = '';
                hoursInput.value = '';
                minutesInput.value = '';

                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value && selectedOption.dataset.types) {
                    try {
                        const types = JSON.parse(selectedOption.dataset.types);
                        allTypes = {}; 
                        types.forEach(function (type) {
                            allTypes[type.id] = type;
                            const option = new Option(type.name, type.id);
                            typeSelect.appendChild(option);
                        });
                        if (types.length > 0) {
                            typeSelect.disabled = false;
                        }
                    } catch (e) {
                        console.error("Error parsing JSON data for types: ", e);
                    }
                }
            });

            typeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const typeId = selectedOption.value;

                // Resetear campos antes de recalcular
                hoursInputWrapper.style.display = 'none';
                minutesInputWrapper.style.display = 'none';
                hoursInput.value = '';
                minutesInput.value = '';
                standbyWrapper.style.display = 'none';

                if (typeId && allTypes[typeId]) {
                    const selectedType = allTypes[typeId];
                    
                    powerInput.value = selectedType.default_power_watts ?? '';

                    const defaultMinutes = parseInt(selectedType.default_avg_daily_use_minutes, 10);

                    if (!isNaN(defaultMinutes)) {
                        if (defaultMinutes >= 60) {
                            hoursInputWrapper.style.display = 'block';
                            minutesInputWrapper.style.display = 'none';
                            hoursInput.value = (defaultMinutes / 60).toFixed(1);
                        } else {
                            minutesInputWrapper.style.display = 'block';
                            hoursInputWrapper.style.display = 'none';
                            minutesInput.value = defaultMinutes;
                        }
                    }

                    if (selectedType.is_portable) {
                        locationWrapper.style.display = 'none';
                        locationSelect.required = false;
                        locationSelect.value = '';
    
                    } else {
                        locationWrapper.style.display = 'block';
                        locationSelect.required = true;
                    }

                    if (selectedType.standby_power_watts && parseFloat(selectedType.standby_power_watts) > 0 && defaultMinutes < 1440) {
                        standbyWrapper.style.display = 'block';
                    } else {
                        standbyWrapper.style.display = 'none';
                    }
                }
            });
            
            // Disparar eventos change al cargar para manejar valores 'old()'
            if (categorySelect.value) {
                categorySelect.dispatchEvent(new Event('change'));
                const oldTypeId = '{{ old('equipment_type_id') }}';
                if(oldTypeId) {
                    // Pequeño delay para asegurar que el typeSelect se haya poblado
                    setTimeout(() => {
                        typeSelect.value = oldTypeId;
                        if (typeSelect.selectedIndex > 0) { // Asegurarse que el oldTypeId es válido
                           typeSelect.dispatchEvent(new Event('change'));
                        }
                    }, 100);
                }
            }
        });
    </script>
</x-app-layout>