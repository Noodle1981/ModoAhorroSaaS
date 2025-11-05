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

    <!-- ... (la sección de Detalles del Contrato queda igual) ... -->
    <div style="margin-top: 20px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <h3>Detalles del Contrato</h3>
        <!-- ... -->
    </div>
    
    <!-- Sección de Facturas (¡AHORA MEJORADA!) -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <div>
                <h2 style="margin: 0;">Facturas Asociadas</h2>
                <p style="color: #666; font-size: 0.9em; margin: 5px 0 0 0;">
                    💡 Las fechas corresponden al <strong>período de consumo</strong>, no a la fecha de emisión
                </p>
            </div>
            <a href="{{ route('contracts.invoices.create', $contract) }}" style="background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                + Cargar Factura
            </a>
        </div>
        
        @if($contract->invoices->isEmpty())
             <p>Este contrato aún no tiene facturas cargadas.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Período</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Consumo (kWh)</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Importe Total ($)</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contract->invoices as $invoice)
                        <tr>
                            <td style="padding: 12px; border: 1px solid #ddd;">
                                {{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">
                                {{ number_format($invoice->total_energy_consumed_kwh, 2, ',', '.') }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: right; font-weight: bold;">
                                $ {{ number_format($invoice->total_amount, 2, ',', '.') }}
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                <a href="{{ route('invoices.show', $invoice) }}">Ver</a> |
                                <a href="{{ route('invoices.edit', $invoice) }}">Editar</a>
                                <!-- Formulario de borrado de factura iría aquí -->
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</x-app-layout>