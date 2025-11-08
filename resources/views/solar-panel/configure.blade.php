<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-solar-panel mr-2"></i>
            Configurar Instalación Solar - {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white rounded-lg shadow-lg p-6">
                
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Datos de la Instalación Solar
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Registrá los datos técnicos de tu instalación solar para monitorear su producción y rendimiento.
                    </p>
                </div>

                <form action="{{ route('solar-panel.configure.store', $entity) }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Capacidad Instalada -->
                    <div>
                        <label for="installed_kwp" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                            Capacidad Instalada (kWp) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="installed_kwp" 
                            id="installed_kwp" 
                            value="{{ old('installed_kwp', $installation->installed_kwp ?? '') }}"
                            required
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            placeholder="Ej: 5.5"
                        >
                        <p class="text-xs text-gray-500 mt-1">
                            Potencia total instalada en kilovatios pico (kWp)
                        </p>
                        @error('installed_kwp')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Información de Paneles -->
                    <fieldset class="border-2 border-blue-200 rounded-lg p-4 bg-blue-50">
                        <legend class="text-sm font-bold text-blue-900 px-3">
                            <i class="fas fa-solar-panel mr-2"></i>
                            Paneles Solares
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="panel_brand" class="block text-sm font-medium text-gray-700 mb-2">
                                    Marca de los Paneles
                                </label>
                                <input 
                                    type="text" 
                                    name="panel_brand" 
                                    id="panel_brand" 
                                    value="{{ old('panel_brand', $installation->panel_brand ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    placeholder="Ej: Canadian Solar"
                                >
                                @error('panel_brand')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="panel_model" class="block text-sm font-medium text-gray-700 mb-2">
                                    Modelo de los Paneles
                                </label>
                                <input 
                                    type="text" 
                                    name="panel_model" 
                                    id="panel_model" 
                                    value="{{ old('panel_model', $installation->panel_model ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                    placeholder="Ej: HiKu CS3W-400P"
                                >
                                @error('panel_model')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- Información de Inversor -->
                    <fieldset class="border-2 border-green-200 rounded-lg p-4 bg-green-50">
                        <legend class="text-sm font-bold text-green-900 px-3">
                            <i class="fas fa-microchip mr-2"></i>
                            Inversor
                        </legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="inverter_brand" class="block text-sm font-medium text-gray-700 mb-2">
                                    Marca del Inversor
                                </label>
                                <input 
                                    type="text" 
                                    name="inverter_brand" 
                                    id="inverter_brand" 
                                    value="{{ old('inverter_brand', $installation->inverter_brand ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                    placeholder="Ej: Fronius"
                                >
                                @error('inverter_brand')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="inverter_model" class="block text-sm font-medium text-gray-700 mb-2">
                                    Modelo del Inversor
                                </label>
                                <input 
                                    type="text" 
                                    name="inverter_model" 
                                    id="inverter_model" 
                                    value="{{ old('inverter_model', $installation->inverter_model ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50"
                                    placeholder="Ej: Primo 5.0-1"
                                >
                                @error('inverter_model')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- Datos de Instalación -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="installation_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar text-blue-500 mr-1"></i>
                                Fecha de Instalación
                            </label>
                            <input 
                                type="date" 
                                name="installation_date" 
                                id="installation_date" 
                                value="{{ old('installation_date', $installation->installation_date ?? '') }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                            @error('installation_date')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="roof_orientation" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-compass text-blue-500 mr-1"></i>
                                Orientación
                            </label>
                            <select 
                                name="roof_orientation" 
                                id="roof_orientation"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                                <option value="">Seleccionar...</option>
                                <option value="N" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'N' ? 'selected' : '' }}>Norte</option>
                                <option value="NE" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'NE' ? 'selected' : '' }}>Noreste</option>
                                <option value="NO" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'NO' ? 'selected' : '' }}>Noroeste</option>
                                <option value="E" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'E' ? 'selected' : '' }}>Este</option>
                                <option value="O" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'O' ? 'selected' : '' }}>Oeste</option>
                                <option value="SE" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'SE' ? 'selected' : '' }}>Sureste</option>
                                <option value="SO" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'SO' ? 'selected' : '' }}>Suroeste</option>
                                <option value="S" {{ old('roof_orientation', $installation->roof_orientation ?? '') === 'S' ? 'selected' : '' }}>Sur</option>
                            </select>
                            @error('roof_orientation')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="roof_slope_degrees" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-angle-double-up text-blue-500 mr-1"></i>
                                Inclinación (°)
                            </label>
                            <input 
                                type="number" 
                                name="roof_slope_degrees" 
                                id="roof_slope_degrees" 
                                value="{{ old('roof_slope_degrees', $installation->roof_slope_degrees ?? '') }}"
                                min="0"
                                max="90"
                                placeholder="Ej: 30"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                            >
                            @error('roof_slope_degrees')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- API/Monitoreo -->
                    <fieldset class="border-2 border-purple-200 rounded-lg p-4 bg-purple-50">
                        <legend class="text-sm font-bold text-purple-900 px-3">
                            <i class="fas fa-plug mr-2"></i>
                            Conexión con Sistema de Monitoreo (Opcional)
                        </legend>

                        <p class="text-sm text-gray-600 mb-4">
                            Si tu inversor tiene un sistema de monitoreo en línea, podés conectarlo para importar automáticamente los datos de producción.
                        </p>

                        <div class="space-y-4">
                            <div>
                                <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">
                                    URL de la API
                                </label>
                                <input 
                                    type="url" 
                                    name="api_url" 
                                    id="api_url" 
                                    value="{{ old('api_url', $installation->api_url ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                    placeholder="https://api.monitoring-system.com/v1/data"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Endpoint de la API del sistema de monitoreo
                                </p>
                                @error('api_url')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="api_token" class="block text-sm font-medium text-gray-700 mb-2">
                                    Token/Clave de API
                                </label>
                                <input 
                                    type="text" 
                                    name="api_token" 
                                    id="api_token" 
                                    value="{{ old('api_token', $installation->api_token ?? '') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring focus:ring-purple-200 focus:ring-opacity-50"
                                    placeholder="Tu token de autenticación"
                                >
                                <p class="text-xs text-gray-500 mt-1">
                                    Credencial para autenticar las solicitudes a la API
                                </p>
                                @error('api_token')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- Notas -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sticky-note text-gray-500 mr-1"></i>
                            Notas Adicionales
                        </label>
                        <textarea 
                            name="notes" 
                            id="notes" 
                            rows="3"
                            placeholder="Información adicional sobre la instalación, mantenimiento, etc."
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >{{ old('notes', $installation->notes ?? '') }}</textarea>
                        @error('notes')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
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
                            class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Instalación
                        </button>
                    </div>

                </form>

            </div>

        </div>
    </div>
