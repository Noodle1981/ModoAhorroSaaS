<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Standby (Consumo Fantasma)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Tarjetas de acciones principales -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Card: Gestionar Equipos con Standby -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">Gestionar Equipos con Standby</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-blue-200 text-blue-800">Paso 1</span>
                        </div>
                        <p class="text-sm text-gray-700 mb-4">Revisa y ajusta qué equipos de tu inventario tienen standby activo.</p>
                        <a href="#gestionar-equipos" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow text-sm">
                            <i class="fas fa-cogs mr-2"></i> Ir a gestionar
                        </a>
                    </div>

                    <!-- Card: Otras Recomendaciones de Standby -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-800">Otras Recomendaciones de Standby</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-yellow-200 text-yellow-800">Paso 2</span>
                        </div>
                        <p class="text-sm text-gray-700 mb-4">Activa standby en todos los equipos salvo los de uso continuo (24h). Esto se reflejará en el último período pendiente de ajuste.</p>

                        @if(!$confirmedAt)
                            <div class="text-sm text-yellow-800 bg-yellow-200/60 border border-yellow-300 rounded p-2 mb-3">
                                Primero confirmá tu configuración actual en "Gestionar".
                            </div>
                            <button class="px-4 py-2 bg-gray-400 text-white rounded cursor-not-allowed text-sm" disabled>
                                <i class="fas fa-magic mr-2"></i> Aplicar recomendaciones
                            </button>
                        @else
                            <div id="recomendaciones"></div>
                            <form method="POST" action="{{ route('standby.apply-recommendations') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded shadow text-sm">
                                    <i class="fas fa-magic mr-2"></i> Aplicar recomendaciones
                                </button>
                            </form>
                            <p class="text-xs text-gray-600 mt-2">Confirmado: {{ \Carbon\Carbon::parse($confirmedAt)->format('d/m/Y H:i') }}</p>
                            @if($lastPendingInvoice)
                                <p class="text-xs text-gray-600 mt-1">Último período pendiente: {{ $lastPendingInvoice->start_date->format('d/m') }} - {{ $lastPendingInvoice->end_date->format('d/m/Y') }}</p>
                            @endif
                            <p class="text-xs text-yellow-700 mt-2">Podés ver recomendaciones detalladas antes de aplicar cambios masivos usando el botón "Ver recomendaciones" arriba.</p>
                        @endif
                    </div>
                </div>

            <!-- Mensaje de éxito -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif
                @if (session('warning'))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative">
                        {{ session('warning') }}
                    </div>
                @endif

            <!-- Sección 1: Categorías de Equipos -->
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Configuración por Categoría</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Define qué categorías de equipos soportan cálculo de standby por defecto. Esto afectará nuevos equipos al crearlos.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($categories as $category)
                    <form method="POST" action="{{ route('standby.category.update', $category) }}" class="flex items-center justify-between p-4 border border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100">
                        @csrf
                        @method('PATCH')
                        <div class="flex items-center gap-2">
                            <input type="hidden" name="supports_standby" value="0">
                            <input 
                                type="checkbox" 
                                name="supports_standby" 
                                value="1" 
                                {{ $category->supports_standby ? 'checked' : '' }}
                                onchange="this.form.submit()"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span class="font-medium">{{ $category->name }}</span>
                            <span class="text-xs text-gray-500">({{ $category->equipment_types_count }} tipos)</span>
                        </div>
                    </form>
                    @endforeach
                </div>
            </div>

            <!-- Sección 2: Tus Equipos -->
            <div id="gestionar-equipos" class="bg-white shadow-sm sm:rounded-lg p-6" x-data="standbyManager()">
                <h3 class="text-lg font-semibold mb-4">Gestión de Equipos Propios</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Activa o desactiva el cálculo de standby para tus equipos existentes. Agrupados por categoría.
                </p>

                @if ($equipments->isEmpty())
                    <p class="text-gray-500">No tienes equipos registrados aún.</p>
                @else
                    @foreach ($equipments as $categoryName => $items)
                    <div class="mb-6" x-data="{ open: false }">
                        <div class="flex items-center justify-between bg-gray-100 p-3 rounded cursor-pointer" @click="open = !open">
                            <h4 class="font-semibold">{{ $categoryName }} ({{ $items->count() }})</h4>
                            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>

                        <div x-show="open" x-collapse class="mt-2 space-y-2">
                            <!-- Acción en bloque -->
                            <div class="flex items-center gap-2 p-2 bg-blue-50 border border-blue-200 rounded">
                                <span class="text-sm font-medium">Acciones en bloque para esta categoría:</span>
                                <button 
                                    type="button" 
                                    @click="selectAll('{{ $categoryName }}', true)"
                                    class="text-xs px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Marcar todos
                                </button>
                                <button 
                                    type="button" 
                                    @click="selectAll('{{ $categoryName }}', false)"
                                    class="text-xs px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600">
                                    Desmarcar todos
                                </button>
                            </div>

                            @foreach ($items as $eq)
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded bg-white">
                                <div class="flex items-center gap-3">
                                    <input 
                                        type="checkbox" 
                                        :checked="selected.includes({{ $eq->id }})"
                                        @change="toggleSelect({{ $eq->id }})"
                                        class="rounded border-gray-300 text-blue-600"
                                    >
                                    <div>
                                        <strong>{{ $eq->custom_name ?? $eq->equipmentType->name }}</strong>
                                        <span class="text-xs text-gray-500">({{ $eq->entity->name }})</span>
                                        <div class="text-xs text-gray-400">
                                            Cant: {{ $eq->quantity }} | Potencia: {{ $eq->power_watts_override }}W
                                        </div>
                                    </div>
                                </div>
                                <span 
                                    class="px-2 py-1 text-xs rounded"
                                    :class="selected.includes({{ $eq->id }}) && pendingStandby ? 'bg-green-100 text-green-700' : (selected.includes({{ $eq->id }}) && !pendingStandby ? 'bg-red-100 text-red-700' : '{{ $eq->has_standby_mode ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}')"
                                >
                                    <span x-show="!selected.includes({{ $eq->id }})">{{ $eq->has_standby_mode ? 'Activo' : 'Inactivo' }}</span>
                                    <span x-show="selected.includes({{ $eq->id }}) && pendingStandby">✓ Activo</span>
                                    <span x-show="selected.includes({{ $eq->id }}) && !pendingStandby">✗ Inactivo</span>
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    <!-- Botones de acción global -->
                    <form method="POST" action="{{ route('standby.equipment.bulk') }}" class="flex items-center gap-4 mt-6">
                        @csrf
                        <input type="hidden" name="equipment_ids" :value="JSON.stringify(selected)">
                        <button 
                            type="submit" 
                            name="has_standby_mode" 
                            value="1"
                            @click="pendingStandby = true"
                            :disabled="selected.length === 0"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 disabled:bg-gray-300">
                            Activar Standby (<span x-text="selected.length"></span>)
                        </button>
                        <button 
                            type="submit" 
                            name="has_standby_mode" 
                            value="0"
                            @click="pendingStandby = false"
                            :disabled="selected.length === 0"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:bg-gray-300">
                            Desactivar Standby (<span x-text="selected.length"></span>)
                        </button>
                        <button 
                            type="button" 
                            @click="selected = []"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Limpiar Selección
                        </button>
                    </form>

                    <!-- Confirmación de configuración -->
                    <form method="POST" action="{{ route('standby.confirm') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow text-sm">
                            <i class="fas fa-check mr-2"></i> Confirmar configuración
                        </button>
                        @if($confirmedAt)
                            <span class="ml-3 text-xs text-gray-600">Última confirmación: {{ \Carbon\Carbon::parse($confirmedAt)->diffForHumans() }}</span>
                            <a href="{{ route('standby.index') }}#recomendaciones" class="ml-4 inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-xs">
                                <i class="fas fa-lightbulb mr-1"></i> Ver recomendaciones
                            </a>
                        @endif
                    </form>
                @endif
            </div>

        </div>
    </div>

    <script>
        function standbyManager() {
            return {
                selected: [],
                pendingStandby: null,
                toggleSelect(id) {
                    if (this.selected.includes(id)) {
                        this.selected = this.selected.filter(x => x !== id);
                    } else {
                        this.selected.push(id);
                    }
                },
                selectAll(categoryName, standbyState) {
                    // En el servidor agrupamos por categoryName; aquí seleccionamos todos los IDs de esa categoría
                    const allItemsInCategory = @json($equipments).hasOwnProperty(categoryName) ? @json($equipments)[categoryName] : [];
                    allItemsInCategory.forEach(eq => {
                        if (!this.selected.includes(eq.id)) {
                            this.selected.push(eq.id);
                        }
                    });
                    this.pendingStandby = standbyState;
                }
            };
        }
    </script>
</x-app-layout>
