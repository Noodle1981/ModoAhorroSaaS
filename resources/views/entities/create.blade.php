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
            
            {{-- Lógica Dinámica para el Tipo de Entidad --}}
            @if (count($allowed_types) === 1)
                {{-- Si solo hay un tipo, muéstralo como texto y usa un input oculto --}}
                <input type="hidden" name="type" value="{{ $allowed_types[0] }}">
                <p style="padding: 8px; background-color: #f4f4f4; border: 1px solid #ddd; border-radius: 5px;">
                    <strong>{{ ucfirst($allowed_types[0]) }}</strong> (Permitido por tu plan actual)
                </p>
            @else
                {{-- Si hay varios tipos, muestra el menú desplegable --}}
                <select id="type" name="type" required style="width: 100%; padding: 8px;">
                    <option value="">Selecciona un tipo</option>
                    @foreach ($allowed_types as $type)
                        <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }} {{-- Pone la primera letra en mayúscula --}}
                        </option>
                    @endforeach
                </select>
            @endif
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
            <label for="address_postal_code">Código Postal</label><br>
            <input type="text" id="address_postal_code" name="address_postal_code" value="{{ old('address_postal_code') }}" style="width: 100%; padding: 8px;">
        </div>

        {{-- Detalles adicionales (habitaciones, personas, etc.) --}}
        <div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
            <h3 style="margin-bottom: 15px;">Detalles de la Entidad</h3>
            
            {{-- Habitaciones/Espacios --}}
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold;">Habitaciones / Espacios</label>
                <p style="font-size: 12px; color: #666; margin-bottom: 10px;">
                    Define las habitaciones o espacios de tu entidad. Esto te ayudará a organizar tus equipos por ubicación.
                </p>
                <div id="rooms-container">
                    {{-- Habitaciones iniciales (si hay old data) --}}
                    @foreach (old('details.rooms', [['name' => 'Sala/Living'], ['name' => 'Cocina']]) as $index => $room)
                        <div class="room-item" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                            <input type="hidden" name="details[rooms][{{ $index }}][id]" value="{{ $room['id'] ?? $index + 1 }}">
                            <input type="text" name="details[rooms][{{ $index }}][name]" value="{{ $room['name'] }}" placeholder="Nombre de la habitación" style="flex-grow: 1; padding: 8px;" required>
                            <button type="button" onclick="removeRoom(this)" style="background-color: #dc3545; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer;">
                                Eliminar
                            </button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-room-btn" style="background-color: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin-top: 10px;">
                    + Agregar Habitación
                </button>
            </div>

            {{-- Número de ocupantes --}}
            <div style="margin-top: 15px;">
                <label for="details_occupants">Número de Ocupantes</label><br>
                <input type="number" id="details_occupants" name="details[occupants]" value="{{ old('details.occupants', 2) }}" min="1" style="width: 150px; padding: 8px;">
                <small style="color: #666; margin-left: 10px;">Personas que habitan o trabajan en esta entidad</small>
            </div>

            {{-- Superficie (opcional) --}}
            <div style="margin-top: 15px;">
                <label for="details_area">Superficie (m²) - Opcional</label><br>
                <input type="number" id="details_area" name="details[area_m2]" value="{{ old('details.area_m2') }}" min="1" step="0.01" style="width: 150px; padding: 8px;">
                <small style="color: #666; margin-left: 10px;">Metros cuadrados totales</small>
            </div>

            {{-- Uso Mixto (solo si es tipo hogar) --}}
            <div x-data="{ mixedUse: {{ old('details.mixed_use') ? 'true' : 'false' }} }" style="margin-top: 20px; padding: 15px; background-color: #fffbea; border-left: 4px solid #f59e0b; border-radius: 5px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="checkbox" 
                           id="details_mixed_use" 
                           name="details[mixed_use]" 
                           value="1"
                           x-model="mixedUse"
                           {{ old('details.mixed_use') ? 'checked' : '' }}
                           style="width: 20px; height: 20px; cursor: pointer;">
                    <label for="details_mixed_use" style="font-weight: bold; cursor: pointer; margin: 0;">
                        ¿Esta vivienda tiene uso comercial/profesional mixto?
                    </label>
                </div>
                <p style="font-size: 12px; color: #666; margin: 10px 0 0 30px;">
                    Por ejemplo: almacén en el frente, taller en el garaje, consultorio en una habitación, oficina profesional, etc.
                </p>

                {{-- Advertencia que aparece cuando se marca --}}
                <div x-show="mixedUse" 
                     x-transition
                     style="margin-top: 15px; padding: 12px; background-color: #fef3c7; border-radius: 4px; border: 1px solid #f59e0b;">
                    <div style="display: flex; gap: 10px;">
                        <span style="font-size: 20px;">⚠️</span>
                        <div style="flex: 1;">
                            <p style="margin: 0 0 8px 0; font-weight: bold; color: #92400e;">
                                Importante: Limitaciones del análisis con uso mixto
                            </p>
                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #78350f;">
                                El análisis de consumo en propiedades de uso mixto (residencial + comercial) puede tener menor precisión porque:
                            </p>
                            <ul style="margin: 0 0 8px 0; padding-left: 20px; font-size: 13px; color: #78350f;">
                                <li>Los patrones de uso comercial difieren significativamente de los residenciales</li>
                                <li>Los equipos comerciales (heladeras comerciales, maquinaria, etc.) tienen consumos muy distintos</li>
                                <li>Los horarios de operación son diferentes</li>
                            </ul>
                            <p style="margin: 0; font-size: 13px; color: #78350f;">
                                <strong>Recomendación:</strong> Para análisis más precisos, considera crear entidades separadas para cada uso 
                                o utiliza el <a href="{{ route('entities.index') }}" style="color: #d97706; text-decoration: underline;">Plan Gestor</a> 
                                diseñado específicamente para gestionar negocios y consumos comerciales.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Guardar Entidad
            </button>
            <a href="{{ route('entities.index') }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>

    {{-- JavaScript para gestionar habitaciones dinámicamente --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomsContainer = document.getElementById('rooms-container');
            const addRoomBtn = document.getElementById('add-room-btn');
            let roomIndex = {{ count(old('details.rooms', [['name' => 'Sala/Living'], ['name' => 'Cocina']])) }};

            addRoomBtn.addEventListener('click', function() {
                const newIndex = roomIndex++;
                const newRoomDiv = document.createElement('div');
                newRoomDiv.className = 'room-item';
                newRoomDiv.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px; align-items: center;';
                newRoomDiv.innerHTML = `
                    <input type="hidden" name="details[rooms][${newIndex}][id]" value="${newIndex + 1}">
                    <input type="text" name="details[rooms][${newIndex}][name]" placeholder="Nombre de la habitación" style="flex-grow: 1; padding: 8px;" required>
                    <button type="button" onclick="removeRoom(this)" style="background-color: #dc3545; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer;">
                        Eliminar
                    </button>
                `;
                roomsContainer.appendChild(newRoomDiv);
            });
        });

        function removeRoom(button) {
            const roomItem = button.closest('.room-item');
            roomItem.remove();
        }
    </script>
</x-app-layout>