<x-app-layout>
    <!-- ... (Título y errores) ... -->

    <form action="{{ route('entities.equipment.store', $entity) }}" method="POST">
        @csrf

        <!-- Selección en Cascada -->
        <div style="margin-bottom: 15px;">
            <label for="category_id">1. Selecciona la Categoría</label><br>
            <select id="category_id" ...>
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
        
        <!-- =========== CAMPO DE UBICACIÓN CONDICIONAL =========== -->
        <div id="location-wrapper" style="margin-bottom: 15px; display: none;"> <!-- Oculto por defecto -->
            <label for="location">3. Asigna una Ubicación</label><br>
            <select id="location" name="location" style="width: 100%; padding: 8px;">
                <option value="">-- Selecciona una ubicación --</option>
                @if(isset($locations))
                    @foreach ($locations as $loc)
                        <option value="{{ $loc }}">{{ $loc }}</option>
                    @endforeach
                @endif
            </select>
        </div>
        <!-- =================================================== -->

        <!-- ... (resto de los campos: custom_name, power, quantity, etc.) ... -->
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const typeSelect = document.getElementById('equipment_type_id');
            const locationWrapper = document.getElementById('location-wrapper');
            const locationSelect = document.getElementById('location');

            // --- LÓGICA PARA LA CASCADA (YA LA TENÍAMOS) ---
            categorySelect.addEventListener('change', function () {
                // ... (código existente de la cascada) ...
                // Limpiamos los tipos y reseteamos la ubicación
                typeSelect.innerHTML = '<option value="">-- Selecciona un tipo --</option>';
                typeSelect.disabled = true;
                locationWrapper.style.display = 'none';
                locationSelect.required = false;

                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value && selectedOption.dataset.types) {
                    const types = JSON.parse(selectedOption.dataset.types);
                    types.forEach(function (type) {
                        // Guardamos el dato de si es portátil en el option
                        const option = new Option(type.name, type.id);
                        option.dataset.isPortable = type.is_portable ? '1' : '0';
                        typeSelect.appendChild(option);
                    });
                    typeSelect.disabled = false;
                }
            });

            // --- NUEVA LÓGICA PARA MOSTRAR/OCULTAR UBICACIÓN ---
            typeSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const isPortable = selectedOption.dataset.isPortable === '1';
                    if (isPortable) {
                        locationWrapper.style.display = 'none'; // Ocultar
                        locationSelect.required = false;       // No es requerido
                        locationSelect.value = '';             // Limpiar valor
                    } else {
                        locationWrapper.style.display = 'block'; // Mostrar
                        locationSelect.required = true;        // Es requerido
                    }
                } else {
                    locationWrapper.style.display = 'none';
                    locationSelect.required = false;
                }
            });
        });
    </script>
</x-app-layout>