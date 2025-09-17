<x-app-layout>
    <h1>Editar Suministro de: {{ $supply->entity->name }}</h1>

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

    <form action="{{ route('supplies.update', $supply) }}" method="POST">
        @csrf
        @method('PUT') <!-- O 'PATCH', le dice a Laravel que es una actualización -->

        <div>
            <label for="type">Tipo de Suministro</label><br>
            <!-- Deshabilitamos el tipo porque no debería cambiarse una vez creado -->
            <select id="type" name="type" required style="width: 100%; padding: 8px; background-color: #e9ecef;" disabled>
                <option value="electricity" {{ $supply->type == 'electricity' ? 'selected' : '' }}>Electricidad</option>
                <option value="gas" {{ $supply->type == 'gas' ? 'selected' : '' }}>Gas</option>
                <option value="water" {{ $supply->type == 'water' ? 'selected' : '' }}>Agua</option>
            </select>
            <small>El tipo de suministro no se puede modificar.</small>
        </div>
        
        <div style="margin-top: 15px;">
            <label for="supply_point_identifier">Identificador del Suministro (NIS, CUPS, etc.)</label><br>
            <input type="text" id="supply_point_identifier" name="supply_point_identifier" value="{{ old('supply_point_identifier', $supply->supply_point_identifier) }}" required style="width: 100%; padding: 8px;">
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Actualizar Suministro
            </button>
            <a href="{{ route('entities.show', $supply->entity) }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>
</x-app-layout>