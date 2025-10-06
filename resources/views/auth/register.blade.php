<x-guest-layout>
    <h1>Crear una cuenta</h1>

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

    <form method="POST" action="{{ route('register') }}" x-data="registerForm()" x-init="init()">
        @csrf

        <div>
            <label for="name">Nombre Completo:</label><br>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div>
            <label for="email">Email:</label><br>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div>
            <label for="tax_id">CUIT / CUIL:</label><br>
            <input id="tax_id" type="text" name="tax_id" value="{{ old('tax_id') }}" required placeholder="Ej: 20-12345678-9" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
            <p style="font-size: 0.75rem; color: #666; margin-top: -10px; margin-bottom: 15px;">Será el identificador de tu compañía.</p>
        </div>

        <div style="margin-bottom: 15px;">
            <label for="is_particular">
                <input id="is_particular" type="checkbox" name="is_particular" value="true" x-model="isParticular">
                <span>Es Particular (el nombre de la empresa será su CUIT)</span>
            </label>
        </div>

        <div x-show="!isParticular" x-transition>
            <label for="company_name">Nombre de la Empresa:</label><br>
            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div>
            <label for="province_id">Provincia:</label><br>
            <select id="province_id" name="province_id" required x-model="provinceId" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
                <option value="">Selecciona una provincia</option>
                @foreach ($provinces as $province)
                    <option value="{{ $province->id }}" {{ old('province_id') == $province->id ? 'selected' : '' }}>
                        {{ $province->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="locality_id">Localidad:</label><br>
            <select id="locality_id" name="locality_id" required x-ref="localitySelect" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
                <option value="">Selecciona una localidad</option>
            </select>
        </div>

        <div>
            <label for="password">Contraseña:</label><br>
            <input id="password" type="password" name="password" required autocomplete="new-password" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div>
            <label for="password_confirmation">Confirmar Contraseña:</label><br>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" style="width: 100%; padding: 8px; margin-top: 5px; margin-bottom: 15px;">
        </div>

        <div style="margin-top: 1rem; margin-bottom: 1rem;">
            <label for="terms">
                <input id="terms" name="terms" type="checkbox" required>
                <span>Acepto registrarme para una <strong>Suscripción Gratuita</strong>.</span>
            </label>
        </div>

        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 1rem;">
            <a href="{{ route('login') }}" style="text-decoration: underline; font-size: 0.875rem; color: #333;">
                ¿Ya tienes una cuenta?
            </a>

            <button type="submit" style="margin-left: 1rem; padding: 10px 20px; background-color: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Registrarse
            </button>
        </div>
    </form>

<script>
    function registerForm() {
        return {
            isParticular: {{ old('is_particular', 'true') === 'true' }},
            provinces: @json($provinces),
            localities: @json($localities),
            provinceId: '{{ old('province_id') }}',
            localityId: '{{ old('locality_id') }}',

            init() {
                this.filterLocalities();
                this.$watch('provinceId', () => {
                    this.localityId = '';
                    this.filterLocalities();
                });
            },
            
            filterLocalities() {
                const localitySelect = this.$refs.localitySelect;
                localitySelect.innerHTML = '<option value="">Selecciona una localidad</option>';
                
                if (!this.provinceId) return;

                const filtered = this.localities.filter(locality => locality.province_id == this.provinceId);
                
                filtered.forEach(locality => {
                    const option = document.createElement('option');
                    option.value = locality.id;
                    option.textContent = locality.name;
                    if (this.localityId == locality.id) {
                        option.selected = true;
                    }
                    localitySelect.appendChild(option);
                });
            }
        }
    }
</script>
</x-guest-layout>