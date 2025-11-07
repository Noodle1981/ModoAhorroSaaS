<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-tools mr-2 text-orange-500"></i> Mantenimiento de Equipos
            </h2>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- TAREAS VENCIDAS --}}
        @if(isset($overdueTasks) && count($overdueTasks) > 0)
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex items-center mb-3">
                <i class="fas fa-times-circle text-red-600 text-xl mr-3"></i>
                <h3 class="text-lg font-bold text-red-800">
                    Tienes {{ count($overdueTasks) }} tarea(s) de mantenimiento vencida(s)
                </h3>
            </div>
            <div class="space-y-3">
                @foreach($overdueTasks as $item)
                    @php($task=$item['task'])
                    @php($equipment=$item['equipment'])
                    <div class="bg-white rounded-lg p-4 border border-red-200 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">
                                    <i class="fas fa-bolt text-red-500 mr-2"></i>
                                    {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    {{ $equipment->entity->name }}
                                    @if($equipment->location)
                                        - {{ $equipment->location }}
                                    @endif
                                </p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="fas fa-exclamation mr-1"></i> {{ $task->name ?? $task->task_name }} VENCIDA
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        <i class="fas fa-clock mr-1"></i> Último: {{ $item['lastLog'] ? $item['lastLog']->performed_on_date->format('d/m/Y') : 'Nunca' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                        <i class="fas fa-calendar mr-1"></i> Debió: {{ $item['dueDate']->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>
                            <button onclick="openMaintenanceModal({{ $equipment->id }}, {{ $task->id }})" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition whitespace-nowrap">
                                <i class="fas fa-check mr-2"></i> Registrar Ahora
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- TAREAS PRÓXIMAS (<=3 días) --}}
        @if(count($pendingTasks) > 0)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
            <div class="flex items-center mb-3">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-xl mr-3"></i>
                <h3 class="text-lg font-bold text-yellow-800">
                    Tienes {{ count($pendingTasks) }} tarea(s) de mantenimiento pendiente(s)
                </h3>
            </div>
            <div class="space-y-3">
                @foreach($pendingTasks as $item)
                    @php($task=$item['task'])
                    @php($equipment=$item['equipment'])
                    <div class="bg-white rounded-lg p-4 border border-yellow-200 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900">
                                    <i class="fas fa-bolt text-orange-500 mr-2"></i>
                                    {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
                                </h4>
                                <p class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-map-marker-alt mr-1"></i> 
                                    {{ $equipment->entity->name }}
                                    @if($equipment->location)
                                        - {{ $equipment->location }}
                                    @endif
                                </p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-wrench mr-1"></i> {{ $task->name ?? $task->task_name }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        <i class="fas fa-clock mr-1"></i> Último: {{ $item['lastLog'] ? $item['lastLog']->performed_on_date->format('d/m/Y') : 'Nunca' }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                        <i class="fas fa-calendar mr-1"></i> Vence: {{ $item['dueDate']->format('d/m/Y') }} ({{ $item['daysLeft'] }} días)
                                    </span>
                                </div>
                            </div>
                            <button 
                                onclick="openMaintenanceModal({{ $equipment->id }}, {{ $task->id }})"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition whitespace-nowrap">
                                <i class="fas fa-check mr-2"></i> Registrar Mantenimiento
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                <p class="text-green-800 font-medium">
                    ¡Excelente! No tienes tareas de mantenimiento pendientes.
                </p>
            </div>
        </div>

        {{-- TAREAS FUTURAS (<=14 días) --}}
        @if(isset($upcomingTasks) && count($upcomingTasks) > 0)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i> Próximas (≤14 días)
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($upcomingTasks as $item)
                    @php($task=$item['task'])
                    @php($equipment=$item['equipment'])
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <h4 class="font-semibold text-gray-900 mb-1">
                            <i class="fas fa-bolt text-indigo-500 mr-2"></i>
                            {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
                        </h4>
                        <p class="text-xs text-gray-500 mb-2">{{ $equipment->entity->name }}</p>
                        <p class="text-sm text-gray-700"><i class="fas fa-wrench mr-1 text-gray-400"></i>{{ $task->name ?? $task->task_name }}</p>
                        <p class="text-xs text-gray-500 mt-1">Vence: {{ $item['dueDate']->format('d/m/Y') }} ({{ $item['daysLeft'] }} días)</p>
                        <button onclick="openMaintenanceModal({{ $equipment->id }}, {{ $task->id }})" class="mt-3 text-xs inline-flex items-center px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                            <i class="fas fa-check mr-1"></i> Registrar ahora
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif

        {{-- HISTORIAL DE MANTENIMIENTOS --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-history mr-2 text-blue-500"></i> Historial de Mantenimientos
            </h3>

            @if($maintenanceLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Equipo</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden md:table-cell">Tarea</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden lg:table-cell">Notas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden sm:table-cell">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($maintenanceLogs->take(20) as $log)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                        <i class="far fa-calendar mr-1 text-gray-400"></i>
                                        {{ $log->performed_on_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-medium text-gray-900">
                                            {{ $log->entityEquipment->custom_name ?? $log->entityEquipment->equipmentType->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $log->entityEquipment->entity->name }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell">
                                        <i class="fas fa-wrench text-blue-500 mr-1"></i>
                                        {{ $log->maintenanceTask->task_name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell">
                                        <span class="line-clamp-2">{{ $log->notes ?? 'Sin notas' }}</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm hidden sm:table-cell">
                                        @if($log->verification_status === 'verified')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i> Verificado
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-user mr-1"></i> Usuario
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-clipboard-list text-6xl text-gray-300 mb-3"></i>
                    <p>No hay registros de mantenimiento aún.</p>
                </div>
            @endif
        </div>

        {{-- EQUIPOS Y TAREAS APLICABLES --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-list-check mr-2 text-purple-500"></i> Todos los Equipos y Tareas Aplicables
            </h3>

            @if($userEquipments->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($userEquipments as $equipment)
                        @php
                            $tasksForEquipment = $applicableTasks->where('equipment_type_id', $equipment->equipment_type_id);
                        @endphp
                        @if($tasksForEquipment->count() > 0)
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                            <h4 class="font-semibold text-gray-900 mb-2">
                                <i class="fas fa-bolt text-orange-500 mr-2"></i>
                                {{ $equipment->custom_name ?? $equipment->equipmentType->name }}
                            </h4>
                            <p class="text-xs text-gray-500 mb-3">
                                {{ $equipment->entity->name }}
                                @if($equipment->location)
                                    - {{ $equipment->location }}
                                @endif
                            </p>
                            <div class="space-y-2">
                                @foreach($tasksForEquipment as $task)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-700">
                                            <i class="fas fa-wrench text-blue-500 mr-1 text-xs"></i>
                                            {{ $task->task_name }}
                                        </span>
                                        <button 
                                            onclick="openMaintenanceModal({{ $equipment->id }}, {{ $task->id }})"
                                            class="text-green-600 hover:text-green-700 font-medium text-xs">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-plug text-6xl text-gray-300 mb-3"></i>
                    <p>No tienes equipos registrados.</p>
                    <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-700 font-semibold text-sm mt-2 inline-block">
                        Ir al Dashboard para agregar equipos
                    </a>
                </div>
            @endif
        </div>

    </div>

    {{-- MODAL PARA REGISTRAR MANTENIMIENTO --}}
    <div id="maintenanceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-t-lg">
                <h3 class="text-lg font-bold">
                    <i class="fas fa-check-circle mr-2"></i> Registrar Mantenimiento
                </h3>
            </div>
            <form method="POST" action="{{ route('maintenance.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="entity_equipment_id" id="modal_equipment_id">
                <input type="hidden" name="maintenance_task_id" id="modal_task_id">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha de Realización</label>
                    <input 
                        type="date" 
                        name="performed_on_date" 
                        value="{{ date('Y-m-d') }}"
                        required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notas (opcional)</label>
                    <textarea 
                        name="notes" 
                        rows="3"
                        placeholder="Describe lo realizado, observaciones, repuestos utilizados, etc."
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button 
                        type="button" 
                        onclick="closeMaintenanceModal()"
                        class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-semibold rounded-lg transition">
                        Cancelar
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                        <i class="fas fa-save mr-2"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openMaintenanceModal(equipmentId, taskId) {
            document.getElementById('modal_equipment_id').value = equipmentId;
            document.getElementById('modal_task_id').value = taskId;
            document.getElementById('maintenanceModal').classList.remove('hidden');
            document.getElementById('maintenanceModal').classList.add('flex');
        }

        function closeMaintenanceModal() {
            document.getElementById('maintenanceModal').classList.add('hidden');
            document.getElementById('maintenanceModal').classList.remove('flex');
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('maintenanceModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMaintenanceModal();
            }
        });
    </script>
</x-app-layout>
