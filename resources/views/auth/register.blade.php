<x-guest-layout>
    <h1>Registrarse</h1>
    
    <!-- Muestra los errores de validación -->
    @if ($errors->any())
        <div style="color:red; margin-bottom: 1rem;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <label for="name">Nombre:</label><br>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <!-- Email Address -->
        <div>
            <label for="email">Email:</label><br>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <!-- Password -->
        <div>
            <label for="password">Contraseña:</label><br>
            <input id="password" type="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation">Confirmar Contraseña:</label><br>
            <input id="password_confirmation" type="password" name="password_confirmation" required style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <button type="submit" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Registrarse
        </button>
    </form>
</x-guest-layout>