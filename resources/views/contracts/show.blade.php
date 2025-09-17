<x-app-layout>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Contrato: {{ $contract->rate_name }}</h1>
            <p style="color: #666; font-size: 0.9em;">
                Para el suministro 
                <a href="{{ route('supplies.show', $contract->supply) }}" style="text-decoration: none; color: #007bff;">
                    {{ $contract->supply->supply_point_identifier }}
                </a>
                en la entidad '{{ $contract->supply->entity->name }}'
            </p>
        </div>
        <a href="{{ route('contracts.edit', $contract) }}" style="background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            Editar Contrato
        </a>
    </div>

    <div style="margin-top: 20px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <h3>Detalles del Contrato</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <p><strong>Compañía:</strong> {{ $contract->utilityCompany->name }}</p>
            <p><strong>Nº Contrato:</strong> {{ $contract->contract_identifier ?? 'No especificado' }}</p>
            <p><strong>Fecha de Inicio:</strong> {{ $contract->start_date->format('d/m/Y') }}</p>
            <p><strong>Fecha de Fin:</strong> {{ $contract->end_date ? $contract->end_date->format('d/m/Y') : 'Vigente' }}</p>
            <p><strong>Potencia Contratada:</strong> {{ $contract->contracted_power_kw_p1 ?? 'N/A' }} kW</p>
            <p><strong>Estado:</strong> 
                @if($contract->is_active)
                    <span style="color: #28a745; font-weight: bold;">Activo</span>
                @else
                    <span style="color: #6c757d;">Inactivo</span>
                @endif
            </p>
        </div>
    </div>
    
    <!-- Sección de Facturas -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Facturas Asociadas</h2>
            <a href="{{ route('contracts.invoices.create', $contract) }}" style="background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                + Cargar Factura
            </a>
        </div>
        
        @if($contract->invoices->isEmpty())
             <p>Este contrato aún no tiene facturas cargadas.</p>
        @else
            <p>Aquí se mostrará la lista de facturas...</p>
            <!-- La tabla de facturas la construiremos en el siguiente paso -->
        @endif
    </div>

</x-app-layout>