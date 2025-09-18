<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Modo Ahorro') }}</title>

    <!-- Aquí irían tus estilos y scripts. Por ahora, lo mantenemos simple. -->
    <style>
        body { font-family: sans-serif; margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        nav { background-color: #f4f4f4; padding: 1rem; border-bottom: 1px solid #ddd; }
        nav ul { list-style: none; margin: 0; padding: 0; display: flex; gap: 1.5rem; }
        nav a { text-decoration: none; color: #333; font-weight: bold; }
        main { padding: 20px 0; }
        .user-menu { margin-left: auto; display: flex; align-items: center; gap: 1rem;}
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb; border-radius: 4px; }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <header>
            <nav>
                <ul>
                    <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('entities.index') }}">Mis Entidades</a></li>
                    <li><a href="{{ route('maintenance.index') }}">Mantenimiento</a></li>
                </ul>

                <div class="user-menu">
                    <span>Hola, {{ Auth::user()->name }}</span>
                    <!-- Formulario de Logout -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Cerrar Sesión</button>
                    </form>
                </div>
            </nav>
        </header>

        <!-- Contenido principal de la página -->
        <main>
            <div class="container">
                <!-- Mensajes de éxito (flash messages) -->
                @if (session('success'))
                    <div class="alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                
                <!-- Aquí es donde se inyectará el contenido de cada página hija -->
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>