<x-guest-layout>
    <h1>Iniciar sesión</h1>
    
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

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div>
            <label for="email">Email:</label><br>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div>
            <label for="password">Contraseña:</label><br>
            <input id="password" type="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="remember_me">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Recordarme</span>
            </label>
        </div>
        
        <button type="submit" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
            Entrar
        </button>
    </form>
</x-guest-layout>