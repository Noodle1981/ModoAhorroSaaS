<x-guest-layout>
    <h1>Crear una cuenta</h1>

    @if ($errors->any())
        <div style="color:red; margin-bottom: 1rem;">
            <strong>¡Ups! Hubo algunos problemas.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="name">Nombre</label><br>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div style="margin-top: 15px;">
            <label for="email">Email</label><br>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>
        
        <div style="margin-top: 15px;">
            <label for="province_id">Provincia</label><br>
            <select id="province_id" name="province_id" required style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
                <option value="">Selecciona una provincia</option>
                @foreach ($provinces as $province)
                    <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                        {{ $province->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-top: 15px;">
            <label for="locality_id">Localidad</label><br>
            <select id="locality_id" name="locality_id" required style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
                <option value="">Selecciona una localidad</option>
                @foreach ($localities as $locality)
                    <option value="{{ $locality->id }}" {{ old('locality_id') == $locality->id ? 'selected' : '' }}>
                        {{ $locality->name }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div style="margin-top: 15px;">
            <label for="password">Contraseña</label><br>
            <input id="password" type="password" name="password" required autocomplete="new-password" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div style="margin-top: 15px;">
            <label for="password_confirmation">Confirmar Contraseña</label><br>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div style="display: flex; align-items: center; justify-content: flex-end; margin-top: 20px;">
            <a href="{{ route('login') }}" style="text-decoration: underline; color: #666; margin-right: 20px;">
                ¿Ya tienes una cuenta?
            </a>

            <button type="submit" style="padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Registrarse
            </button>
        </div>
    </form>
</x-guest-layout>