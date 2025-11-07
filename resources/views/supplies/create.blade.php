<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                <i class="fas fa-plug text-green-600 mr-2"></i>
                Añadir Suministro Eléctrico
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600">
                Entidad: <span class="font-semibold">{{ $entity->name }}</span>
            </p>
        </div>

        <!-- Errores de validación -->
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-600 mt-0.5"></i>
                    <div class="flex-1">
                        <strong class="block text-red-800 font-semibold mb-2">¡Ups! Hubo algunos problemas:</strong>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <form action="{{ route('entities.supplies.store', $entity) }}" method="POST" class="p-4 sm:p-6">
                @csrf
                <input type="hidden" name="type" value="electricity">

                <div class="space-y-6">
                    <!-- Identificador del Suministro -->
                    <div>
                        <label for="supply_point_identifier" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-barcode text-gray-400 mr-2"></i>
                            Identificador del Suministro (NIS, CUPS, etc.)
                        </label>
                        <input type="text" 
                               id="supply_point_identifier" 
                               name="supply_point_identifier" 
                               value="{{ old('supply_point_identifier') }}" 
                               required 
                               placeholder="Ej: ES0021000012345678AB"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                        <p class="mt-2 text-xs sm:text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            El CUPS (Código Universal de Punto de Suministro) lo encontrarás en tu factura eléctrica
                        </p>
                    </div>

                    <!-- Botones de acción -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                        <button type="submit" 
                                class="flex-1 sm:flex-initial px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Suministro
                        </button>
                        <a href="{{ route('entities.show', $entity) }}" 
                           class="flex-1 sm:flex-initial px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg text-center transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 sm:p-5">
            <div class="flex items-start gap-3">
                <i class="fas fa-lightbulb text-blue-600 text-xl mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm sm:text-base text-blue-900 font-medium">
                        Esta versión de la plataforma solo estará disponible para cálculos de eficiencia en energía eléctrica.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>