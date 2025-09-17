<x-app-layout>
    <h1>Cargar Nueva Factura</h1>
    <p>
        Para el contrato: <strong>{{ $contract->rate_name }}</strong> (Suministro: {{ $contract->supply->supply_point_identifier }})
    </p>
    <p>
        <a href="{{ route('contracts.show', $contract) }}">&larr; Volver al Contrato</a>
    </p>

    @if ($errors->any())
        <div style="color:red; margin-bottom: 1rem;">
            <strong>¡Ups! Hubo algunos problemas con los datos.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('contracts.invoices.store', $contract) }}" method="POST">
        @csrf
        
        <h3>Datos Principales (Obligatorios)</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
            <div>
                <label for="start_date">Período de Consumo - Inicio</label><br>
                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label for="end_date">Período de Consumo - Fin</label><br>
                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label for="total_energy_consumed_kwh">Consumo Total (kWh)</label><br>
                <input type="number" step="0.01" id="total_energy_consumed_kwh" name="total_energy_consumed_kwh" value="{{ old('total_energy_consumed_kwh') }}" required style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label for="total_amount">Importe Total de la Factura ($)</label><br>
                <input type="number" step="0.01" id="total_amount" name="total_amount" value="{{ old('total_amount') }}" required style="width: 100%; padding: 8px;">
            </div>
        </div>
        
        <h3 style="margin-top: 30px;">Datos Detallados (Opcionales)</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
             <div>
                <label for="invoice_number">Número de Factura</label><br>
                <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label for="invoice_date">Fecha de Emisión</label><br>
                <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date') }}" style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label for="cost_for_energy">Costo por Energía ($)</label><br>
                <input type="number" step="0.01" id="cost_for_energy" name="cost_for_energy" value="{{ old('cost_for_energy') }}" style="width: 100%; padding: 8px;">
            </div>
            <div>
                <label for="taxes">Impuestos ($)</label><br>
                <input type="number" step="0.01" id="taxes" name="taxes" value="{{ old('taxes') }}" style="width: 100%; padding: 8px;">
            </div>
             <div>
                <label for="other_charges">Otros Cargos ($)</label><br>
                <input type="number" step="0.01" id="other_charges" name="other_charges" value="{{ old('other_charges') }}" style="width: 100%; padding: 8px;">
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Guardar Factura
            </button>
            <a href="{{ route('contracts.show', $contract) }}" style="margin-left: 10px;">Cancelar</a>
        </div>
    </form>
</x-app-layout>