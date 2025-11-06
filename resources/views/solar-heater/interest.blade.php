<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Información sobre tu Calefón - {{ $entity->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <p class="text-gray-600 mb-6">
                    Ayudanos a brindarte una recomendación precisa contándonos sobre tu sistema actual de agua caliente.
                </p>

                <form method="POST" action="{{ route('solar-heater.interest.store', $entity) }}" class="space-y-6">
                    @csrf

                    <!-- Tipo de Calefón Actual -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ¿Qué tipo de calefón tenés actualmente?
                        </label>
                        <select name="current_heater_type" required class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                            <option value="">-- Seleccioná una opción --</option>
                            <option value="electric" {{ old('current_heater_type', $entity->current_heater_type) === 'electric' ? 'selected' : '' }}>⚡ Eléctrico (Termotanque)</option>
                            <option value="gas" {{ old('current_heater_type', $entity->current_heater_type) === 'gas' ? 'selected' : '' }}>🔥 A Gas Natural (red)</option>
                            <option value="glp" {{ old('current_heater_type', $entity->current_heater_type) === 'glp' ? 'selected' : '' }}>🔴 A GLP (garrafas)</option>
                            <option value="wood" {{ old('current_heater_type', $entity->current_heater_type) === 'wood' ? 'selected' : '' }}>🪵 A Leña</option>
                            <option value="solar" {{ old('current_heater_type', $entity->current_heater_type) === 'solar' ? 'selected' : '' }}>☀️ Solar (Ya tengo)</option>
                            <option value="none" {{ old('current_heater_type', $entity->current_heater_type) === 'none' ? 'selected' : '' }}>❌ No tengo calefón</option>
                        </select>
                    </div>

                    <!-- Interés en Solar -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ¿Te interesa instalar o mejorar con un calefón solar?
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="solar_heater_interest" value="1" {{ old('solar_heater_interest', $entity->solar_heater_interest) ? 'checked' : '' }} class="rounded-full border-gray-300 text-orange-600 focus:ring-orange-500">
                                <span class="ml-2">✅ Sí, me interesa recibir información y presupuestos</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="solar_heater_interest" value="0" {{ !old('solar_heater_interest', $entity->solar_heater_interest) ? 'checked' : '' }} class="rounded-full border-gray-300 text-gray-600 focus:ring-gray-500">
                                <span class="ml-2">❌ No por ahora, solo quiero ver el análisis</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notas adicionales -->
                    <div>
                        <label for="solar_heater_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Notas adicionales (opcional)
                        </label>
                        <textarea name="solar_heater_notes" id="solar_heater_notes" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500" placeholder="Ej: Tengo poco espacio en el techo, mi calefón tiene 10 años, etc.">{{ old('solar_heater_notes', $entity->solar_heater_notes) }}</textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('solar-heater.index') }}" class="text-gray-600 hover:text-gray-800">
                            ← Volver al análisis
                        </a>
                        <button type="submit" class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">
                            Guardar Información
                        </button>
                    </div>
                </form>

            </div>

            <!-- Información adicional -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">💡 ¿Cómo funciona?</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>✓ Analizamos tu consumo y tipo de calefón</li>
                    <li>✓ Calculamos el ahorro potencial con solar</li>
                    <li>✓ Si expresás interés, te conectamos con instaladores certificados (próximamente)</li>
                    <li>✓ Seguimos tu inversión y monitoreamos el ahorro real</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
