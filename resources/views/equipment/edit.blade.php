<x-app-layout>
    <h1>Editar Equipo del Inventario: {{ $equipment->custom_name ?? $equipment->equipmentType->name }}</h1>
    
    <form action="{{ route('equipment.update', $equipment) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Select de Categoría -->
        <div style="margin-bottom: 15px;">
            <label for="category_id">1. Categoría</label><br>
            <select id="category_id" name="category_id" required style="width: 100%; padding: 8px;">
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

        <!-- Select de Tipo de Equipo (se rellena con JS) -->
        <div style="margin-bottom: 15px;">
            <label for="equipment_type_id">2. Tipo de Equipo</label><br>
            <select id="equipment_type_id" name="equipment_type_id" required style="width: 100%; padding: 8px;">
                <option value="">-- Primero selecciona una categoría --</option>
            </select>
        </div>
        
        <!-- =========== CAMPO DE UBICACIÓN CONDICIONAL =========== -->
        <div id="location-wrapper" style="margin-bottom: 15px;">
    <label for="location">3. Asigna una Ubicación</label><br>
    <select id="location" name="location" required style="width: 100%; padding: 8px;">
        <option value="">-- Selecciona una ubicación --</option>
        
        <!-- Usamos la nueva variable $locations que nos pasa el controlador -->
        @forelse ($locations as $locationName)
            <option value="{{ $locationName }}" {{ old('location') == $locationName ? 'selected' : '' }}>
                {{ $locationName }}
            </option>
        @empty
            <option value="" disabled>Primero debes definir las habitaciones en la entidad.</option>
        @endforelse
    </select>
</div>

        <!-- Potencia (override) -->
        <div style="margin-bottom: 15px;">
            <label for="power_watts_override">4. Potencia (W)</label><br>
            <input type="number" id="power_watts_override" name="power_watts_override" min="0" value="{{ old('power_watts_override', $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts) }}" style="width: 100%; padding: 8px;">
        </div>

        <!-- Minutos de uso promedio por día -->
        <div style="margin-bottom: 15px;">
            <label for="avg_daily_use_minutes_override">5. Minutos de uso promedio por día</label><br>
            <input type="number" id="avg_daily_use_minutes_override" name="avg_daily_use_minutes_override" min="0" max="1440" value="{{ old('avg_daily_use_minutes_override', $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes) }}" style="width: 100%; padding: 8px;">
        </div>

        <!-- Standby -->
        <div style="margin-bottom: 15px;">
            <label style="display:inline-flex;align-items:center;gap:8px;">
                <input type="checkbox" name="has_standby_mode" value="1" {{ old('has_standby_mode', $equipment->has_standby_mode) ? 'checked' : '' }}>
                <span><strong>Activar cálculo de consumo en Standby</strong></span>
            </label>
            <div style="font-size:12px;color:#6b7280;margin-top:4px;">
                Marca esto solo si este equipo consume algo estando apagado (ej: TV). Si no estás seguro, déjalo desmarcado.
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Actualizar Equipo
            </button>
        </div>
    </form>

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