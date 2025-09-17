<x-app-layout>
    <h1>Editar Contrato para el Suministro: {{ $contract->supply->supply_point_identifier }}</h1>
    <p>
        <a href="{{ route('supplies.show', $contract->supply) }}">&larr; Volver al Suministro</a>
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

    <form action="{{ route('contracts.update', $contract) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label for="utility_company_id">Compañía Comercializadora</label><br>
                <select id="utility_company_id" name="utility_company_id" required style="width: 100%; padding: 8px;">
                    @foreach ($utilityCompanies as $company)
                        <option value="{{ $company->id }}" {{ old('utility_company_id', $contract->utility_company_id) == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="rate_name">Nombre de la Tarifa</label><br>
                <input type="text" id="rate_name" name="rate_name" value="{{ old('rate_name', $contract->rate_name) }}" required style="width: 100%; padding: 8px;">
            </div>

            <div>
                <label for="start_date">Fecha de Inicio del Contrato</label><br>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required style="width: 100%; padding: 8px;">
            </div>

            <div>
                <label for="contracted_power_kw_p1">Potencia Contratada (kW)</label><br>
                <input type="number" step="0.01" id="contracted_power_kw_p1" name="contracted_power_kw_p1" value="{{ old('contracted_power_kw_p1', $contract->contracted_power_kw_p1) }}" style="width: 100%; padding: 8px;">
            </div>
            
            <div>
                <label for="end_date">Fecha de Fin del Contrato (opcional)</label><br>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $contract->end_date ? $contract->end_date->format('Y-m-d') : '') }}" style="width: 100%; padding: 8px;">
            </div>
        </div>

        <div style="margin-top: 15px;">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $contract->is_active) ? 'checked' : '' }}>
            <label for="is_active">Marcar como contrato activo</label>
            <small style="display: block; color: #666;">(Si marcas este, cualquier otro contrato de este suministro se desactivará automáticamente).</small>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Actualizar Contrato
            </button>
            <a href="{{ route('supplies.show', $contract->supply) }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>
</x-app-layout>