<x-guest-layout>
    <h1 class="text-2xl font-bold mb-6 text-center">Crear una cuenta</h1>

    <!-- Bloque de Errores -->
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>¡Ups! Hubo algunos problemas.</strong>
            <ul class="mt-3 list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="space-y-4">
            <!-- Nombre -->
            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nombre Completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
            </div>

            <!-- ============================================= -->
            <!-- === NUEVO CAMPO AÑADIDO: CUIT/CUIL (Tax ID) === -->
            <!-- ============================================= -->
            <div>
                <label for="tax_id" class="block font-medium text-sm text-gray-700">CUIT / CUIL</label>
                <input id="tax_id" type="text" name="tax_id" value="{{ old('tax_id') }}" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300" placeholder="Ej: 20-12345678-9">
                <p class="text-xs text-gray-500 mt-1">Este dato es necesario para la facturación. Será el identificador de tu compañía.</p>
            </div>
            
            <!-- Provincia -->
            <div>
                <label for="province_id" class="block font-medium text-sm text-gray-700">Provincia</label>
                <select id="province_id" name="province_id" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                    <option value="">Selecciona una provincia</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Localidad (Se podría poblar con JS) -->
            <div>
                <label for="locality_id" class="block font-medium text-sm text-gray-700">Localidad</label>
                <select id="locality_id" name="locality_id" required class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
                    <option value="">Selecciona una localidad</option>
                    @foreach ($localities as $locality)
                        <option value="{{ $locality->id }}" {{ old('locality_id') == $locality->id ? 'selected' : '' }}>
                            {{ $locality->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <!-- Contraseña -->
            <div>
                <label for="password" class="block font-medium text-sm text-gray-700">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
            </div>

            <!-- Confirmar Contraseña -->
            <div>
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Confirmar Contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="block mt-1 w-full rounded-md shadow-sm border-gray-300">
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <a href="{{ route('login') }}" class="underline text-sm text-gray-600 hover:text-gray-900">
                ¿Ya tienes una cuenta?
            </a>

            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border rounded-md font-semibold text-xs text-white uppercase hover:bg-gray-700">
                Registrarse
            </button>
        </div>
    </form>
</x-guest-layout>