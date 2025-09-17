<x-app-layout>
    <h1>Añadir Nueva Entidad</h1>

    <!-- Muestra los errores de validación -->
    @if ($errors->any())
        <div style="color:red; margin-bottom: 1rem;">
            <strong>¡Ups! Hubo algunos problemas con tu entrada.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('entities.store') }}" method="POST">
        @csrf

        <div>
            <label for="name">Nombre de la Entidad (ej: "Casa", "Oficina Centro")</label><br>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-top: 15px;">
            <label for="type">Tipo de Entidad</label><br>
            <select id="type" name="type" required style="width: 100%; padding: 8px;">
                <option value="">Selecciona un tipo</option>
                <option value="hogar" {{ old('type') == 'hogar' ? 'selected' : '' }}>Hogar</option>
                <option value="oficina" {{ old('type') == 'oficina' ? 'selected' : '' }}>Oficina</option>
                <option value="comercio" {{ old('type') == 'comercio' ? 'selected' : '' }}>Comercio</option>
            </select>
        </div>
        
        <div style="margin-top: 15px;">
            <label for="locality_id">Localidad</label><br>
            <select id="locality_id" name="locality_id" required style="width: 100%; padding: 8px;">
                <option value="">Selecciona una localidad</option>
                @foreach ($localities as $locality)
                    <option value="{{ $locality->id }}" {{ old('locality_id') == $locality->id ? 'selected' : '' }}>
                        {{ $locality->name }} ({{ $locality->province->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-top: 15px;">
            <label for="address_street">Dirección</label><br>
            <input type="text" id="address_street" name="address_street" value="{{ old('address_street') }}" style="width: 100%; padding: 8px;">
        </div>

        <!-- Aquí irían los campos para el JSON 'details', que podrían aparecer/desaparecer con JS según el 'type' seleccionado -->
        
        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Guardar Entidad
            </button>
            <a href="{{ route('entities.index') }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>
</x-app-layout>