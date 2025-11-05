<x-app-layout>
    <div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
        <div style="margin-bottom: 30px;">
            <a href="{{ route('entities.show', $entity) }}" style="text-decoration: none; color: #007bff; font-size: 0.9em;">
                ← Volver a {{ $entity->name }}
            </a>
            <h1 style="font-size: 2em; font-weight: bold; margin-top: 10px;">Editar Entidad</h1>
        </div>

        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <strong>¡Ups! Hubo algunos problemas:</strong>
                <ul style="margin: 10px 0 0 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('entities.update', $entity) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- =========== SECCIÓN 1: DATOS GENERALES =========== -->
            <div style="background-color: white; border: 1px solid #dee2e6; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="font-size: 1.3em; font-weight: bold; margin-bottom: 20px; color: #333;">📋 Datos Generales</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label for="name" style="font-weight: 600; display: block; margin-bottom: 5px;">Nombre de la Entidad</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $entity->name) }}" required style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;">
                    </div>
                    <div>
                        <label for="type" style="font-weight: 600; display: block; margin-bottom: 5px;">Tipo de Entidad</label>
                        <select id="type" name="type" required style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;">
                            <option value="hogar" {{ old('type', $entity->type) == 'hogar' ? 'selected' : '' }}>🏠 Hogar</option>
                            <option value="oficina" {{ old('type', $entity->type) == 'oficina' ? 'selected' : '' }}>🏢 Oficina</option>
                            <option value="comercio" {{ old('type', $entity->type) == 'comercio' ? 'selected' : '' }}>🏪 Comercio</option>
                        </select>
                    </div>
                    <div>
                        <label for="locality_id" style="font-weight: 600; display: block; margin-bottom: 5px;">Localidad</label>
                        <select id="locality_id" name="locality_id" required style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;">
                            @foreach ($localities as $locality)
                                <option value="{{ $locality->id }}" {{ old('locality_id', $entity->locality_id) == $locality->id ? 'selected' : '' }}>
                                    {{ $locality->name }} ({{ $locality->province->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="address_street" style="font-weight: 600; display: block; margin-bottom: 5px;">Dirección</label>
                        <input type="text" id="address_street" name="address_street" value="{{ old('address_street', $entity->address_street) }}" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;">
                    </div>
                    <div>
                        <label for="address_postal_code" style="font-weight: 600; display: block; margin-bottom: 5px;">Código Postal</label>
                        <input type="text" id="address_postal_code" name="address_postal_code" value="{{ old('address_postal_code', $entity->address_postal_code) }}" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;">
                    </div>
                    <div>
                        <label for="details_occupants" style="font-weight: 600; display: block; margin-bottom: 5px;">👥 Número de Ocupantes</label>
                        <input type="number" name="details[occupants]" id="details_occupants" value="{{ old('details.occupants', $entity->details['occupants'] ?? $entity->details['occupants_count'] ?? 1) }}" min="1" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;">
                        <small style="color: #6c757d; font-size: 0.85em;">Personas que habitan/trabajan en esta entidad</small>
                    </div>
                    <div>
                        <label for="details_area" style="font-weight: 600; display: block; margin-bottom: 5px;">📐 Superficie (m²)</label>
                        <input type="number" name="details[area_m2]" id="details_area" value="{{ old('details.area_m2', $entity->details['area_m2'] ?? '') }}" min="1" step="0.01" style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;">
                        <small style="color: #6c757d; font-size: 0.85em;">Metros cuadrados totales (opcional)</small>
                    </div>
                </div>
            </div>

            <!-- =========== SECCIÓN 2: HABITACIONES =========== -->
            <div style="background-color: white; border: 1px solid #dee2e6; padding: 25px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="font-size: 1.3em; font-weight: bold; margin-bottom: 10px; color: #333;">🏠 Habitaciones y Espacios</h3>
                <p style="color: #6c757d; font-size: 0.9em; margin-bottom: 20px;">
                    Define los diferentes espacios de tu entidad. Esto te permitirá organizar tus equipos por ubicación cuando los registres en el inventario.
                </p>

                {{-- Uso Mixto --}}
                @if($entity->type === 'hogar')
                    <div style="background-color: #e7f3ff; border: 2px solid #007bff; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: start; gap: 10px;">
                            <input type="checkbox" 
                                   id="mixed_use" 
                                   name="details[mixed_use]" 
                                   value="1"
                                   {{ old('details.mixed_use', $entity->details['mixed_use'] ?? false) ? 'checked' : '' }}
                                   style="margin-top: 4px; width: 18px; height: 18px; cursor: pointer;">
                            <div style="flex-grow: 1;">
                                <label for="mixed_use" style="font-weight: 600; color: #0056b3; cursor: pointer; font-size: 1em;">
                                    ¿Esta vivienda tiene uso comercial/profesional mixto?
                                </label>
                                <p style="color: #495057; font-size: 0.85em; margin-top: 8px; line-height: 1.5;">
                                    Marca esta opción si en tu hogar también funciona un negocio, taller, consultorio u oficina. 
                                    Por ejemplo: almacén al frente, taller en el garaje, peluquería en casa, etc.
                                </p>
                                <div id="mixed-use-warning" style="display: none; margin-top: 12px; background-color: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 5px;">
                                    <strong style="color: #856404;">⚠️ Importante:</strong>
                                    <ul style="margin: 8px 0 0 20px; color: #856404; font-size: 0.85em;">
                                        <li>El análisis de consumo puede ser menos preciso con uso mixto</li>
                                        <li>Los equipos comerciales suelen consumir más que los residenciales</li>
                                        <li>Considera el <strong>Plan Gestor</strong> para análisis profesional de comercios</li>
                                    </ul>
                                    <a href="{{ route('dashboard') }}" style="display: inline-block; margin-top: 10px; color: #007bff; font-weight: 600; text-decoration: underline; font-size: 0.9em;">
                                        Ver planes disponibles →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const mixedUseCheckbox = document.getElementById('mixed_use');
                            const warningDiv = document.getElementById('mixed-use-warning');
                            
                            function toggleWarning() {
                                if (mixedUseCheckbox.checked) {
                                    warningDiv.style.display = 'block';
                                } else {
                                    warningDiv.style.display = 'none';
                                }
                            }
                            
                            toggleWarning(); // Check on load
                            mixedUseCheckbox.addEventListener('change', toggleWarning);
                        });
                    </script>
                @endif

                <div id="rooms-container" style="margin-bottom: 15px;">
                    @php
                        $rooms = old('details.rooms', $entity->details['rooms'] ?? []);
                        $roomCount = count($rooms);
                    @endphp
                    
                    @if($roomCount === 0)
                        <div style="background-color: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <strong>⚠️ No hay habitaciones definidas.</strong>
                            <p style="margin: 5px 0 0 0; font-size: 0.9em;">Agrega al menos una habitación para poder organizar mejor tus equipos.</p>
                        </div>
                    @else
                        @foreach ($rooms as $index => $room)
                            <div class="room-item" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 12px; background-color: #f8f9fa; border-radius: 5px; border: 1px solid #dee2e6;">
                                <span style="font-weight: bold; color: #6c757d; min-width: 30px;">{{ $index + 1 }}.</span>
                                <input type="hidden" name="details[rooms][{{ $index }}][id]" value="{{ $room['id'] ?? $index + 1 }}">
                                <input type="text" name="details[rooms][{{ $index }}][name]" value="{{ $room['name'] }}" placeholder="Nombre de la habitación (ej: Cocina, Living, Dormitorio 1)" style="flex-grow: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;" required>
                                <button type="button" onclick="removeRoom(this)" style="background-color: #dc3545; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-weight: 600; white-space: nowrap;">
                                    🗑️ Eliminar
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" id="add-room-btn" style="background-color: #28a745; color: white; border: none; padding: 12px 20px; cursor: pointer; border-radius: 5px; font-weight: 600; font-size: 0.95em;">
                    ➕ Añadir Habitación
                </button>
            </div>

            <!-- Botones de Acción -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
                <a href="{{ route('entities.show', $entity) }}" style="color: #6c757d; text-decoration: none; font-weight: 600;">
                    ← Cancelar y volver
                </a>
                <button type="submit" style="background-color: #28a745; color: white; padding: 14px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 1em; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                    💾 Guardar Cambios
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const roomsContainer = document.getElementById('rooms-container');
            const addRoomBtn = document.getElementById('add-room-btn');
            let roomIndex = {{ count(old('details.rooms', $entity->details['rooms'] ?? [])) }};

            addRoomBtn.addEventListener('click', function() {
                const newIndex = roomIndex++;
                const roomNumber = document.querySelectorAll('.room-item').length + 1;
                
                const newRoomDiv = document.createElement('div');
                newRoomDiv.className = 'room-item';
                newRoomDiv.style.cssText = 'display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding: 12px; background-color: #f8f9fa; border-radius: 5px; border: 1px solid #dee2e6;';
                newRoomDiv.innerHTML = `
                    <span style="font-weight: bold; color: #6c757d; min-width: 30px;">${roomNumber}.</span>
                    <input type="hidden" name="details[rooms][${newIndex}][id]" value="${newIndex + 1}">
                    <input type="text" name="details[rooms][${newIndex}][name]" placeholder="Nombre de la habitación (ej: Cocina, Living, Dormitorio ${roomNumber})" style="flex-grow: 1; padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 0.95em;" required>
                    <button type="button" onclick="removeRoom(this)" style="background-color: #dc3545; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; font-weight: 600; white-space: nowrap;">
                        🗑️ Eliminar
                    </button>
                `;
                roomsContainer.appendChild(newRoomDiv);
                updateRoomNumbers();
            });
        });

        function removeRoom(button) {
            if (confirm('¿Estás seguro de eliminar esta habitación?')) {
                const roomItem = button.closest('.room-item');
                roomItem.remove();
                updateRoomNumbers();
            }
        }

        function updateRoomNumbers() {
            const roomItems = document.querySelectorAll('.room-item');
            roomItems.forEach((item, index) => {
                const numberSpan = item.querySelector('span');
                if (numberSpan) {
                    numberSpan.textContent = (index + 1) + '.';
                }
            });
        }
    </script>
</x-app-layout>