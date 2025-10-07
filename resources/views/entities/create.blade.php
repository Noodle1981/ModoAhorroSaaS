<x-app-layout>
    <h1>Añadir Nueva Entidad</h1>

    <!-- Muestra los errores de validación -->
    @if ($errors->any())
        <div style="color:red; margin-bottom: 1rem;">
            <strong>¡Ups! Hubo algunos problemas con tu entrada.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('entities.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">Nombre de la Entidad (ej: "Casa", "Oficina Centro")</label><br>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-top: 15px;">
    <label for="type">Tipo de Entidad</label><br>
    <select id="type" name="type" required style="width: 100%; padding: 8px;">
        <option value="">Selecciona un tipo</option>
        <option value="hogar" {{ old('type') == 'hogar' ? 'selected' : '' }}>Hogar</option>
        <option value="oficina" {{ old('type') == 'oficina' ? 'selected' : '' }}>Oficina</option>
        <option value="comercio" {{ old('type') == 'comercio' ? 'selected' : '' }}>Comercio</option>
    </select>
</div>

        <div style="margin-top: 15px;">
            <label for="province_id">Provincia</label><br>
            <select id="province_id" name="province_id" required style="width: 100%; padding: 8px;">
                <option value="">Selecciona una provincia</option>
                @foreach ($provinces as $province)
                    <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                        {{ $province->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div style="margin-top: 15px;">
            <label for="locality_id">Localidad</label><br>
            <select id="locality_id" name="locality_id" required style="width: 100%; padding: 8px;">
                <option value="">Selecciona una localidad</option>
                @foreach ($localities as $locality)
                    <option value="{{ $locality->id }}" {{ old('locality_id') == $locality->id ? 'selected' : '' }}>
                        {{ $locality->name }} ({{ $locality->province->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-top: 15px;">
            <label for="address_street">Dirección</label><br>
            <input type="text" id="address_street" name="address_street" value="{{ old('address_street') }}" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-top: 15px;">
            <label for="details_occupants_count">Cantidad de Personas</label><br>
            <input type="number" name="details[occupants_count]" id="details_occupants_count" value="{{ old('details.occupants_count', 1) }}" min="1" style="width: 100%; padding: 8px;">
        </div>
        <div x-data="{ showCalculator: false, largo: '', ancho: '', superficie: '{{ old('details.surface_area') }}' }" x-init="$watch('largo', value => superficie = value * ancho); $watch('ancho', value => superficie = largo * value)">
            <div style="margin-top: 15px;">
                <label for="details_surface_area">
                    Superficie en m² (Opcional)
                    <a href="#" @click.prevent="showCalculator = !showCalculator" style="font-size: 0.8em; margin-left: 10px; text-decoration: none;">
                        ¿No la conoces? Calcúlala aquí
                    </a>
                </label><br>
                <input type="number" name="details[surface_area]" id="details_surface_area" x-model="superficie" min="0" style="width: 100%; padding: 8px; background-color: #f3f4f6;" readonly>
            </div>

            <div x-show="showCalculator" x-transition style="display: flex; gap: 15px; margin-top: 10px; background-color: #f9f9f9; padding: 15px; border-radius: 8px;">
                <div style="flex-grow: 1;">
                    <label for="calc_largo">Largo (m)</label><br>
                    <input type="number" id="calc_largo" x-model.debounce.500ms="largo" style="width: 100%; padding: 8px;" placeholder="ej: 5.5">
                </div>
                <div style="flex-grow: 1;">
                    <label for="calc_ancho">Ancho (m)</label><br>
                    <input type="number" id="calc_ancho" x-model.debounce.500ms="ancho" style="width: 100%; padding: 8px;" placeholder="ej: 4">
                </div>
            </div>
        </div>

        <!-- =========== SECCIÓN 2: ESTRUCTURA Y HABITACIONES =========== -->
        <div style="margin-top: 30px; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
            <h3>Estructura y Habitaciones de la Entidad</h3>
            <p style="color: #666; font-size: 0.9em;">Define los diferentes espacios (ej: Cocina, Living, Taller). Esto te permitirá asignar equipos a cada ubicación.</p>

            <div id="rooms-container" style="margin-top: 20px;">
                <label>Habitaciones / Espacios Definidos:</label>
                @foreach (old('details.rooms', []) as $index => $room)
                    <div class="room-item" style="display: flex; align-items: center; margin-bottom: 10px;">
                        <input type="text" name="details[rooms][{{ $index }}][name]" value="{{ $room['name'] }}" placeholder="Nombre de la habitación" style="flex-grow: 1; padding: 8px;" required>
                        <button type="button" onclick="removeRoom(this)" style="margin-left: 10px; background-color: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px;">Eliminar</button>
                    </div>
                @endforeach
            </div>

            <button type="button" id="add-room-btn" style="margin-top: 10px; background-color: #007bff; color: white; border: none; padding: 8px 15px; cursor: pointer; border-radius: 4px;">+ Añadir Habitación</button>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Guardar Entidad
            </button>
            <a href="{{ route('entities.index') }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addRoomBtn = document.getElementById('add-room-btn');
            const roomsContainer = document.getElementById('rooms-container');

            addRoomBtn.addEventListener('click', function () {
                const newIndex = Date.now(); // ID único basado en el tiempo
                const newRoomDiv = document.createElement('div');
                newRoomDiv.className = 'room-item';
                newRoomDiv.style.cssText = 'display: flex; align-items: center; margin-bottom: 10px;';

                newRoomDiv.innerHTML = `
                    <input type="text" name="details[rooms][${newIndex}][name]" placeholder="Nuevo nombre de habitación" style="flex-grow: 1; padding: 8px;" required>
                    <button type="button" onclick="removeRoom(this)" style="margin-left: 10px; background-color: #dc3545; color: white; border: none; padding: 5px 10px; cursor: pointer; border-radius: 4px;">Eliminar</button>
                `;
                
                roomsContainer.appendChild(newRoomDiv);
            });
        });

        function removeRoom(button) {
            button.parentElement.remove();
        }
    </script>
</x-app-layout>
