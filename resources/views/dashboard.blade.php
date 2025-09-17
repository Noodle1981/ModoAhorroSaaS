<x-app-layout>
    
    <h1>Dashboard Principal</h1>
    <p>¡Bienvenido a tu panel de control de Modo Ahorro!</p>
    <p>Desde aquí podrás gestionar tus entidades, revisar tus consumos y obtener recomendaciones para ahorrar energía.</p>

    <div style="margin-top: 2rem;">
        <a href="{{ route('entities.create') }}" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            + Añadir Nueva Entidad
        </a>
    </div>

</x-app-layout>