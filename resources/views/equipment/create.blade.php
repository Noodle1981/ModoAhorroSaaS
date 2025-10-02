<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Añadir Equipo {{ $type === 'portable' ? 'Portátil' : 'Fijo' }} a: {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <p class="mb-4">
                        <a href="{{ route('entities.show', $entity) }}" class="text-blue-600 hover:underline">&larr; Volver a la Entidad</a>
                    </p>

                    <!-- Muestra Errores de Validación -->
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
                            <strong>¡Ups! Hubo algunos problemas.</strong>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Formulario de Creación -->
                    <form action="{{ route('entities.equipment.store', $entity) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Campo oculto para el tipo (fijo/portátil) -->
                        <input type="hidden" name="type" value="{{ $type }}">

                        <!-- 1. Categoría -->
                        <div>
                            <label for="category_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">1. Selecciona la Categoría</label>
                            <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300">
                                <option value="">-- Selecciona una categoría --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-types='{{ $category->equipmentTypes->toJson() }}' {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- 2. Tipo de Equipo -->
                        <div>
                            <label for="equipment_type_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">2. Selecciona el Tipo de Equipo</label>
                            <select id="equipment_type_id" name="equipment_type_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300" disabled>
                                <option value="">-- Primero selecciona una categoría --</option>
                            </select>
                        </div>
                        
                        <!-- 3. Ubicación -->
                        <div>
                            <label for="location" class="block font-medium text-sm text-gray-700 dark:text-gray-300">3. Asigna una Ubicación</label>
                            @if ($type === 'portable')
                                <input type="text" value="Portátil (no requiere ubicación)" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 dark:bg-gray-800 dark:border-gray-600" disabled>
                                <input type="hidden" name="location" value="Portátil">
                            @else
                                <select id="location" name="location" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-300">
                                    <option value="">-- Selecciona una ubicación --</option>
                                    @forelse ($locations as $locationName)
                                        <option value="{{ $locationName }}" {{ old('location') == $locationName ? 'selected' : '' }}>
                                            {{ $locationName }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No has definido habitaciones en la entidad. Edita la entidad para añadirlas.</option>
                                    @endforelse
                                </select>
                            @endif
                        </div>

                        <!-- 4. Nombre Personalizado -->
                        <div>
                            <label for="custom_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">4. Nombre Personalizado (Opcional)</label>
                            <input type="text" id="custom_name" name="custom_name" value="{{ old('custom_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600" placeholder="Ej: Heladera del quincho">
                        </div>

                        <!-- 5. Potencia -->
                        <div>
                            <label for="power_watts_override" class="block font-medium text-sm text-gray-700 dark:text-gray-300">5. Potencia (en Watts)</label>
                            <input type="number" id="power_watts_override" name="power_watts_override" value="{{ old('power_watts_override') }}" required min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600" placeholder="Se rellenará al seleccionar un tipo">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Busca este valor en la etiqueta de tu equipo (ej: 1500W). Es clave para la precisión.</p>
                        </div>
                        
                        <!-- 6. Cantidad -->
                         <div>
                            <label for="quantity" class="block font-medium text-sm text-gray-700 dark:text-gray-300">6. Cantidad</label>
                            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                        </div>

                        <!-- Costo de Adquisición -->
                        <div>
                            <label for="acquisition_cost" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Costo de Adquisición (Opcional)</label>
                            <input type="number" step="0.01" id="acquisition_cost" name="acquisition_cost" value="{{ old('acquisition_cost') }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600" placeholder="Ej: 85000">
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Ingresa el costo de compra del equipo para poder calcular el retorno de la inversión (ROI) en el futuro.</p>
                        </div>

                        <!-- 7. Tiempo de Uso (Adaptativo) -->
                        <div id="usage-time-wrapper" class="hidden">
                            <label id="usage-label" class="block font-medium text-sm text-gray-700 dark:text-gray-300">7. Uso Diario Promedio</label>
                            <div class="flex items-center mt-1">
                                <input type="number" step="0.01" id="usage_input" name="" value="" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                <span id="usage_unit" class="ml-2 text-sm text-gray-500 dark:text-gray-400"></span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Hemos rellenado un valor promedio, ajústalo a tu realidad.</p>
                        </div>

                        <!-- 8. Standby (Condicional) -->
                        <div id="standby-wrapper" class="hidden mt-4">
                            <label for="has_standby_mode" class="inline-flex items-center">
                                <input type="checkbox" id="has_standby_mode" name="has_standby_mode" value="1" {{ old('has_standby_mode') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300"><strong>Activar cálculo de consumo en Standby (consumo fantasma)</strong></span>
                            </label>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Márcalo para equipos que quedan enchufados sin uso activo (TV, consolas, etc.). No lo marques para equipos que se desenchufan o funcionan 24h (ej: heladera).
                            </p>
                        </div>

                        <!-- Botón de Guardar -->
                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
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
            const powerInput = document.getElementById('power_watts_override');
            
            const usageWrapper = document.getElementById('usage-time-wrapper');
            const usageLabel = document.getElementById('usage-label');
            const usageInput = document.getElementById('usage_input');
            const usageUnit = document.getElementById('usage_unit');

            const standbyWrapper = document.getElementById('standby-wrapper');
            const standbyCheckbox = document.getElementById('has_standby_mode');

            let typesData = {}; // Almacena los datos de los tipos de la categoría seleccionada

            function populateTypeSelect(types) {
                typeSelect.innerHTML = '<option value="">-- Primero selecciona una categoría --</option>'; // Reset
                if (types && types.length > 0) {
                    types.forEach(type => {
                        const option = new Option(type.name, type.id);
                        typeSelect.appendChild(option);
                    });
                    typeSelect.disabled = false;
                    typeSelect.innerHTML = '<option value="">-- Selecciona un tipo --</option>' + typeSelect.innerHTML;
                } else {
                    typeSelect.disabled = true;
                }
            }

            categorySelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                typesData = {}; // Reset
                typeSelect.innerHTML = '<option value="">-- Primero selecciona una categoría --</option>';
                typeSelect.disabled = true;
                resetDependentFields();

                if (selectedOption.value && selectedOption.dataset.types) {
                    try {
                        const types = JSON.parse(selectedOption.dataset.types);
                        types.forEach(type => { typesData[type.id] = type; });
                        populateTypeSelect(types);
                    } catch (e) {
                        console.error("Error parsing JSON data for types: ", e);
                    }
                }
            });

            typeSelect.addEventListener('change', function() {
                const typeId = this.value;
                resetDependentFields();

                if (typeId && typesData[typeId]) {
                    const selectedType = typesData[typeId];

                    // 5. Potencia
                    powerInput.value = selectedType.default_power_watts ?? '';

                    // 7. Tiempo de Uso
                    const defaultMinutes = parseInt(selectedType.default_avg_daily_use_minutes, 10);
                    if (!isNaN(defaultMinutes)) {
                        usageWrapper.classList.remove('hidden');
                        if (defaultMinutes >= 60) {
                            usageInput.name = 'avg_daily_use_hours_override';
                            usageInput.value = (defaultMinutes / 60).toFixed(1);
                            usageUnit.textContent = 'horas';
                        } else {
                            usageInput.name = 'avg_daily_use_minutes_override';
                            usageInput.value = defaultMinutes;
                            usageUnit.textContent = 'minutos';
                        }
                    } else {
                         usageWrapper.classList.add('hidden');
                    }

                    // 8. Standby
                    if (selectedType.standby_power_watts && parseFloat(selectedType.standby_power_watts) > 0 && defaultMinutes < 1440) {
                        standbyWrapper.classList.remove('hidden');
                    } else {
                        standbyWrapper.classList.add('hidden');
                    }
                }
            });

            function resetDependentFields() {
                powerInput.value = '';
                usageWrapper.classList.add('hidden');
                usageInput.value = '';
                usageInput.name = '';
                standbyWrapper.classList.add('hidden');
                standbyCheckbox.checked = false;
            }

            // --- Manejo de valores `old()` en caso de error de validación ---
            function restoreFormState() {
                const oldCategoryId = '{{ old("category_id") }}';
                const oldTypeId = '{{ old("equipment_type_id") }}';

                if (oldCategoryId) {
                    categorySelect.value = oldCategoryId;
                    // Disparamos el evento para poblar los tipos
                    categorySelect.dispatchEvent(new Event('change'));

                    if (oldTypeId) {
                        // Necesitamos esperar un instante para que el DOM se actualice
                        setTimeout(() => {
                            typeSelect.value = oldTypeId;
                            // Y disparamos el evento del tipo para poblar los campos dependientes
                            if (typeSelect.value === oldTypeId) { // Asegurarse que el valor fue seteado
                                typeSelect.dispatchEvent(new Event('change'));
                            }
                        }, 50); // 50ms es usualmente suficiente
                    }
                }
            }

            restoreFormState();
        });
    </script>
</x-app-layout>
