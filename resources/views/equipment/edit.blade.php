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
        
        <!-- El resto de los campos (location, custom_name, etc.) -->
        
        <!-- ... (código de los otros campos como en el create.blade.php) ... -->

        <button type="submit">Actualizar Equipo</button>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const typeSelect = document.getElementById('equipment_type_id');
            const initialTypeId = {{ $equipment->equipment_type_id }};

            function populateTypes() {
                typeSelect.innerHTML = '<option value="">-- Selecciona un tipo --</option>';
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                if (selectedOption.value && selectedOption.dataset.types) {
                    const types = JSON.parse(selectedOption.dataset.types);
                    if (types.length > 0) {
                        types.forEach(function (type) {
                            const option = new Option(type.name, type.id);
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

            // Popula al cargar la página
            populateTypes();

            // Popula al cambiar la categoría
            categorySelect.addEventListener('change', populateTypes);
        });
    </script>
</x-app-layout>