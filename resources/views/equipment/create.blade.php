<x-app-layout>
    <h1>Añadir Equipo al Inventario de: {{ $entity->name }}</h1>
    <p>
        <a href="{{ route('entities.show', $entity) }}">&larr; Volver a la Entidad</a>
    </p>

    @if ($errors->any())
        <!-- ... (código de errores) ... -->
    @endif

    <form action="{{ route('entities.equipment.store', $entity) }}" method="POST">
        @csrf

        <!-- =========== NUEVA SELECCIÓN EN CASCADA =========== -->
        <div style="margin-bottom: 15px;">
            <label for="category_id">1. Selecciona la Categoría</label><br>
            <select id="category_id" name="category_id" required style="width: 100%; padding: 8px;">
                <option value="">-- Selecciona una categoría --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" data-types="{{ json_encode($category->equipmentTypes) }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="equipment_type_id">2. Selecciona el Tipo de Equipo</label><br>
            <select id="equipment_type_id" name="equipment_type_id" required style="width: 100%; padding: 8px;" disabled>
                <option value="">-- Primero selecciona una categoría --</option>
            </select>
        </div>
        <!-- ================================================ -->

        <div style="margin-bottom: 15px;">
            <label for="location">3. Asigna una Ubicación</label><br>
            <select id="location" name="location" required style="width: 100%; padding: 8px;">
                <option value="">-- Selecciona una ubicación --</option>
                @forelse ($entity->details['rooms'] ?? [] as $room)
                    @if(!empty($room['name']))
                        <option value="{{ $room['name'] }}" {{ old('location') == $room['name'] ? 'selected' : '' }}>
                            {{ $room['name'] }}
                        </option>
                    @endif
                @empty
                    <option value="" disabled>Primero define las habitaciones en la entidad.</option>
                @endforelse
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="custom_name">4. Dale un Nombre Personalizado (Opcional)</label><br>
            <input type="text" id="custom_name" name="custom_name" value="{{ old('custom_name') }}" placeholder="Ej: Aire del Living" style="width: 100%; padding: 8px;">
        </div>
        
        <!-- =========== NUEVO CAMPO DE POTENCIA OBLIGATORIO =========== -->
        <div style="margin-bottom: 15px;">
            <label for="power_watts_override">5. Potencia Nominal (en Watts)</label><br>
            <input type="number" id="power_watts_override" name="power_watts_override" value="{{ old('power_watts_override') }}" required placeholder="Busca este valor en la etiqueta de tu equipo" style="width: 100%; padding: 8px;">
            <small style="color: #666;">Este es el dato más importante para la precisión del cálculo. Suele estar en una etiqueta en el aparato (ej: 1500W).</small>
        </div>
        <!-- ========================================================= -->

        <div style="margin-bottom: 15px;">
            <label for="quantity">6. Cantidad</label><br>
            <input type="number" id="quantity" name="quantity" value="{{ old('quantity', 1) }}" required min="1" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-bottom: 15px;">
            <label for="avg_daily_use_hours_override">7. Horas de Uso Promedio por Día (Opcional)</label><br>
            <input type="number" step="0.1" id="avg_daily_use_hours_override" name="avg_daily_use_hours_override" value="{{ old('avg_daily_use_hours_override') }}" placeholder="Ej: 2.5" style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit">Añadir al Inventario</button>
            <a href="{{ route('entities.show', $entity) }}">Cancelar</a>
        </div>
    </form>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const typeSelect = document.getElementById('equipment_type_id');

            categorySelect.addEventListener('change', function () {
                typeSelect.innerHTML = '<option value="">-- Cargando... --</option>';
                typeSelect.disabled = true;

                const selectedOption = this.options[this.selectedIndex];

                if (!selectedOption.value) {
                    typeSelect.innerHTML = '<option value="">-- Primero selecciona una categoría --</option>';
                    return;
                }

                // 1. Verificamos si el atributo data-types existe
                if (!selectedOption.dataset.types) {
                    typeSelect.innerHTML = '<option value="">-- Error: No se encontró el atributo data-types --</option>';
                    return;
                }

                try {
                    // 2. Intentamos procesar el JSON
                    const types = JSON.parse(selectedOption.dataset.types);

                    if (types.length > 0) {
                        typeSelect.innerHTML = '<option value="">-- Selecciona un tipo de equipo --</option>';
                        types.forEach(function (type) {
                            const option = document.createElement('option');
                            option.value = type.id;
                            option.textContent = type.name;
                            typeSelect.appendChild(option);
                        });
                        typeSelect.disabled = false;
                    } else {
                        // 3. El JSON está bien pero no contiene equipos
                        typeSelect.innerHTML = '<option value="">-- No hay tipos para esta categoría --</option>';
                    }
                } catch (e) {
                    // 4. El JSON es inválido y no se pudo procesar
                    typeSelect.innerHTML = '<option value="">-- Error: El formato de datos (JSON) es inválido --</option>';
                    console.error("Error al parsear JSON:", e);
                    console.log("Datos recibidos:", selectedOption.dataset.types);
                }
            });
        });
    </script>

</x-app-layout>