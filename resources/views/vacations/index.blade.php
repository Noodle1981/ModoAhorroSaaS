<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Modo Vacaciones
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <i class="fas fa-plane-departure text-blue-600 mr-3"></i>
                Modo Vacaciones
            </h1>
            <p class="text-gray-600 mt-2">
                Planificá tus ausencias y ahorrá energía automáticamente
            </p>
        </div>
        <a href="{{ route('vacations.create') }}" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-lg transition">
            <i class="fas fa-plus mr-2"></i>
            Nuevo Plan
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($plans->isEmpty())
        <!-- Estado vacío -->
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <div class="max-w-md mx-auto">
                <i class="fas fa-umbrella-beach text-gray-300 text-6xl mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                    No tenés planes de vacaciones registrados
                </h3>
                <p class="text-gray-600 mb-6">
                    Creá un plan para recibir recomendaciones personalizadas sobre qué equipos apagar antes de irte.
                </p>
                <a href="{{ route('vacations.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>
                    Crear Primer Plan
                </a>
            </div>
        </div>
    @else
        <!-- Planes Activos -->
        @php
            $activePlans = $plans->where('status', 'active');
            $pendingPlans = $plans->where('status', 'pending');
            $completedPlans = $plans->where('status', 'completed');
        @endphp

        @if($activePlans->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                    Activos Ahora
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($activePlans as $plan)
                        @include('vacations.partials.plan-card', ['plan' => $plan, 'statusColor' => 'green'])
                    @endforeach
                </div>
            </div>
        @endif

        @if($pendingPlans->isNotEmpty())
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-clock text-blue-500 mr-2"></i>
                    Próximos Viajes
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($pendingPlans as $plan)
                        @include('vacations.partials.plan-card', ['plan' => $plan, 'statusColor' => 'blue'])
                    @endforeach
                </div>
            </div>
        @endif

        @if($completedPlans->isNotEmpty())
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-history text-gray-500 mr-2"></i>
                    Viajes Completados
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($completedPlans as $plan)
                        @include('vacations.partials.plan-card', ['plan' => $plan, 'statusColor' => 'gray'])
                    @endforeach
                </div>
            </div>
        @endif
    @endif

</div>
</div>
</x-app-layout>
