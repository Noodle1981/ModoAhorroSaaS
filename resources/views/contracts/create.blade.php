<x-app-layout>
    <h1>Añadir Contrato al Suministro: {{ $supply->supply_point_identifier }}</h1>
    <p>
        <a href="{{ route('supplies.show', $supply) }}">&larr; Volver al Suministro</a>
    </p>

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

    <form action="{{ route('supplies.contracts.store', $supply) }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label for="utility_company_id">Compañía Comercializadora</label><br>
                <select id="utility_company_id" name="utility_company_id" required style="width: 100%; padding: 8px;">
                    <option value="">Selecciona una compañía</option>
                    @foreach ($utilityCompanies as $company)
                        <option value="{{ $company->id }}" {{ old('utility_company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="rate_name">Nombre de la Tarifa (ej: T1-R2)</label><br>
                <input type="text" id="rate_name" name="rate_name" value="{{ old('rate_name') }}" required style="width: 100%; padding: 8px;">
            </div>

            <div>
                <label for="start_date">Fecha de Inicio del Contrato</label><br>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required style="width: 100%; padding: 8px;">
            </div>

            <div>
                <label for="contracted_power_kw_p1">Potencia Contratada (kW)</label><br>
                <input type="number" step="0.01" id="contracted_power_kw_p1" name="contracted_power_kw_p1" value="{{ old('contracted_power_kw_p1') }}" style="width: 100%; padding: 8px;">
            </div>
        </div>

        <div style="margin-top: 15px;">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" checked>
            <label for="is_active">Marcar como contrato activo</label>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Guardar Contrato
            </button>
            <a href="{{ route('supplies.show', $supply) }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>
</x-app-layout>