<x-app-layout>
    <h1>Editar Entidad: {{ $entity->name }}</h1>

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

    <form action="{{ route('entities.update', $entity) }}" method="POST">
        @csrf
        @method('PUT') <!-- O 'PATCH' -->

        <div>
            <label for="name">Nombre de la Entidad</label><br>
            <input type="text" id="name" name="name" value="{{ old('name', $entity->name) }}" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-top: 15px;">
            <label for="type">Tipo de Entidad</label><br>
            <select id="type" name="type" required style="width: 100%; padding: 8px;">
                <option value="hogar" {{ old('type', $entity->type) == 'hogar' ? 'selected' : '' }}>Hogar</option>
                <option value="oficina" {{ old('type', $entity->type) == 'oficina' ? 'selected' : '' }}>Oficina</option>
                <option value="comercio" {{ old('type', $entity->type) == 'comercio' ? 'selected' : '' }}>Comercio</option>
            </select>
        </div>
        
        <div style="margin-top: 15px;">
            <label for="locality_id">Localidad</label><br>
            <select id="locality_id" name="locality_id" required style="width: 100%; padding: 8px;">
                @foreach ($localities as $locality)
                    <option value="{{ $locality->id }}" {{ old('locality_id', $entity->locality_id) == $locality->id ? 'selected' : '' }}>
                        {{ $locality->name }} ({{ $locality->province->name }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-top: 15px;">
            <label for="address_street">Dirección</label><br>
            <input type="text" id="address_street" name="address_street" value="{{ old('address_street', $entity->address_street) }}" style="width: 100%; padding: 8px;">
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Actualizar Entidad
            </button>
            <a href="{{ route('entities.index') }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>
</x-app-layout>