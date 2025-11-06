<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-cog mr-2"></i>
            Configurar Datos del Techo - {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Espacios Disponibles para Paneles Solares
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Completa la información sobre techo, terreno o patio disponible para instalar paneles solares.
                        Podés tener paneles en uno o ambos espacios.
                    </p>
                </div>

                <form action="{{ route('solar-panel.configure.store', $entity) }}" method="POST" class="space-y-8">
                    @csrf

                    <!-- ===== SECCIÓN TECHO ===== -->
                    <fieldset class="border-2 border-blue-200 rounded-lg p-6 bg-blue-50">
                        <legend class="text-lg font-bold text-blue-900 px-3">
                            <i class="fas fa-home mr-2"></i>
                            Espacio en Techo (opcional)
                        </legend>

                    <!-- Área Total -->
                    <div class="mt-4">
                        <label for="roof_area_m2" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-ruler-combined text-blue-500 mr-1"></i>
                            Área Total del Techo (m²)
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="roof_area_m2" 
                            id="roof_area_m2" 
                            value="{{ old('roof_area_m2', $entity->roof_area_m2) }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Superficie total del techo apta para instalación
                        </p>
                        @error('roof_area_m2')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Obstáculos -->
                    <div class="mt-4">
                        <label for="roof_obstacles_percent" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-percentage text-orange-500 mr-1"></i>
                            Porcentaje Ocupado por Obstáculos (%)
                        </label>
                        <input 
                            type="number" 
                            name="roof_obstacles_percent" 
                            id="roof_obstacles_percent" 
                            value="{{ old('roof_obstacles_percent', $entity->roof_obstacles_percent ?? 0) }}"
                            min="0"
                            max="100"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Espacio ocupado por tanques de agua, chimeneas, claraboyas, antenas, etc.
                        </p>
                        @error('roof_obstacles_percent')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sombreado -->
                    <div class="mt-4">
                        <h4 class="font-semibold text-gray-800 mb-4">
                            <i class="fas fa-cloud text-gray-500 mr-2"></i>
                            Sombreado del Techo
                        </h4>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="has_shading" 
                                    value="1"
                                    {{ old('has_shading', $entity->has_shading) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    onchange="document.getElementById('shading-details').classList.toggle('hidden', !this.checked)"
                                >
                                <span class="ml-2 text-sm text-gray-700">El techo tiene sombras durante el día</span>
                            </label>
                        </div>

                        <div id="shading-details" class="{{ old('has_shading', $entity->has_shading) ? '' : 'hidden' }} space-y-4 ml-6">
                            <div>
                                <label for="shading_hours_daily" class="block text-sm font-medium text-gray-700 mb-2">
                                    Horas de Sombra Promedio (por día)
                                </label>
                                <input 
                                    type="number" 
                                    name="shading_hours_daily" 
                                    id="shading_hours_daily" 
                                    value="{{ old('shading_hours_daily', $entity->shading_hours_daily) }}"
                                    min="0"
                                    max="12"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                @error('shading_hours_daily')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="shading_source" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fuente de la Sombra
                                </label>
                                <input 
                                    type="text" 
                                    name="shading_source" 
                                    id="shading_source" 
                                    value="{{ old('shading_source', $entity->shading_source) }}"
                                    placeholder="Ej: árboles, edificio vecino, cerro"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Qué genera la sombra sobre el techo
                                </p>
                                @error('shading_source')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Orientación -->
                    <div class="mt-4">
                        <h4 class="font-semibold text-gray-800 mb-4">
                            <i class="fas fa-compass text-blue-500 mr-2"></i>
                            Orientación e Inclinación del Techo
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="roof_orientation" class="block text-sm font-medium text-gray-700 mb-2">
                                    Orientación Principal
                                </label>
                                <select 
                                    name="roof_orientation" 
                                    id="roof_orientation"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                    <option value="">Seleccionar...</option>
                                    <option value="N" {{ old('roof_orientation', $entity->roof_orientation) === 'N' ? 'selected' : '' }}>Norte (óptimo)</option>
                                    <option value="NE" {{ old('roof_orientation', $entity->roof_orientation) === 'NE' ? 'selected' : '' }}>Noreste</option>
                                    <option value="NO" {{ old('roof_orientation', $entity->roof_orientation) === 'NO' ? 'selected' : '' }}>Noroeste</option>
                                    <option value="E" {{ old('roof_orientation', $entity->roof_orientation) === 'E' ? 'selected' : '' }}>Este</option>
                                    <option value="O" {{ old('roof_orientation', $entity->roof_orientation) === 'O' ? 'selected' : '' }}>Oeste</option>
                                    <option value="SE" {{ old('roof_orientation', $entity->roof_orientation) === 'SE' ? 'selected' : '' }}>Sureste</option>
                                    <option value="SO" {{ old('roof_orientation', $entity->roof_orientation) === 'SO' ? 'selected' : '' }}>Suroeste</option>
                                    <option value="S" {{ old('roof_orientation', $entity->roof_orientation) === 'S' ? 'selected' : '' }}>Sur</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">
                                    Hacia dónde mira la superficie del techo
                                </p>
                                @error('roof_orientation')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="roof_slope_degrees" class="block text-sm font-medium text-gray-700 mb-2">
                                    Inclinación (grados)
                                </label>
                                <input 
                                    type="number" 
                                    name="roof_slope_degrees" 
                                    id="roof_slope_degrees" 
                                    value="{{ old('roof_slope_degrees', $entity->roof_slope_degrees) }}"
                                    min="0"
                                    max="90"
                                    placeholder="Ej: 30"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Ángulo del techo (0° = plano, 90° = vertical)
                                </p>
                                @error('roof_slope_degrees')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    </fieldset>

                    <!-- ===== SECCIÓN TERRENO/PATIO ===== -->
                    <fieldset class="border-2 border-green-200 rounded-lg p-6 bg-green-50">
                        <legend class="text-lg font-bold text-green-900 px-3">
                            <i class="fas fa-tree mr-2"></i>
                            Espacio en Terreno o Patio (opcional)
                        </legend>

                        <p class="text-sm text-gray-600 mb-4">
                            Si tenés espacio en el patio, jardín o terreno, podés instalar paneles en estructuras elevadas.
                        </p>

                        <!-- Área del terreno -->
                        <div>
                            <label for="ground_area_m2" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-ruler-combined text-green-500 mr-1"></i>
                                Área Disponible de Terreno (m²)
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="ground_area_m2" 
                                id="ground_area_m2" 
                                value="{{ old('ground_area_m2', $entity->ground_area_m2) }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                            >
                            <p class="text-xs text-gray-500 mt-1">
                                Superficie del patio/terreno disponible para paneles (sin construcciones ni áreas de uso diario)
                            </p>
                            @error('ground_area_m2')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ubicación del terreno -->
                        <div class="mt-4">
                            <label for="ground_location" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-map-marker-alt text-green-500 mr-1"></i>
                                Ubicación del Espacio
                            </label>
                            <select 
                                name="ground_location" 
                                id="ground_location"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                            >
                                <option value="">Seleccionar...</option>
                                <option value="front" {{ old('ground_location', $entity->ground_location) === 'front' ? 'selected' : '' }}>Patio delantero / Frente</option>
                                <option value="back" {{ old('ground_location', $entity->ground_location) === 'back' ? 'selected' : '' }}>Patio trasero / Fondo</option>
                                <option value="side" {{ old('ground_location', $entity->ground_location) === 'side' ? 'selected' : '' }}>Lateral / Costado</option>
                            </select>
                            @error('ground_location')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Árboles/sombras -->
                        <div class="mt-4">
                            <label class="flex items-center mb-2">
                                <input 
                                    type="checkbox" 
                                    name="ground_has_trees" 
                                    value="1"
                                    {{ old('ground_has_trees', $entity->ground_has_trees) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                    onchange="document.getElementById('ground-shade-detail').classList.toggle('hidden', !this.checked)"
                                >
                                <span class="ml-2 text-sm text-gray-700">Hay árboles u obstáculos que generan sombra</span>
                            </label>

                            <div id="ground-shade-detail" class="{{ old('ground_has_trees', $entity->ground_has_trees) ? '' : 'hidden' }} ml-6">
                                <label for="ground_shade_percent" class="block text-sm font-medium text-gray-700 mb-2">
                                    Porcentaje Aproximado de Sombra (%)
                                </label>
                                <input 
                                    type="number" 
                                    name="ground_shade_percent" 
                                    id="ground_shade_percent" 
                                    value="{{ old('ground_shade_percent', $entity->ground_shade_percent ?? 0) }}"
                                    min="0"
                                    max="100"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Qué porcentaje del terreno tiene sombra durante el día
                                </p>
                                @error('ground_shade_percent')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notas del terreno -->
                        <div class="mt-4">
                            <label for="ground_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notas sobre el Terreno
                            </label>
                            <textarea 
                                name="ground_notes" 
                                id="ground_notes" 
                                rows="2"
                                placeholder="Ej: patio amplio sin uso, jardín frontal, etc."
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                            >{{ old('ground_notes', $entity->ground_notes) }}</textarea>
                            @error('ground_notes')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </fieldset>

                    <!-- Interés general -->
                    <div class="border-t pt-6">
                        <div class="mb-4">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="solar_panel_interest" 
                                    value="1"
                                    {{ old('solar_panel_interest', $entity->solar_panel_interest) ? 'checked' : '' }}
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm text-gray-700 font-medium">
                                    <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                    Estoy interesado en recibir información sobre instalación de paneles solares
                                </span>
                            </label>
                        </div>

                        <div>
                            <label for="solar_panel_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notas Adicionales
                            </label>
                            <textarea 
                                name="solar_panel_notes" 
                                id="solar_panel_notes" 
                                rows="3"
                                placeholder="Consultas, comentarios, o información adicional..."
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >{{ old('solar_panel_notes', $entity->solar_panel_notes) }}</textarea>
                            @error('solar_panel_notes')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-between pt-6 border-t">
                        <a href="{{ route('solar-panel.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </a>

                        <button 
                            type="submit"
                            class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Configuración
                        </button>
                    </div>

                </form>

            </div>

            <!-- Ayuda -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-question-circle text-blue-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h5 class="font-semibold text-blue-900 mb-2">¿Cómo medir el área del techo?</h5>
                        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                            <li>Puedes usar herramientas como Google Maps con medición de distancias</li>
                            <li>Si es techo plano: largo × ancho = área total</li>
                            <li>Si es techo inclinado: considera la proyección horizontal o multiplica por factor de inclinación</li>
                            <li>Excluye áreas no aptas: aleros angostos, caídas pronunciadas, zonas con estructuras permanentes</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
