<x-app-layout>
    @php
        // Buscamos el contrato activo para el botón de "Añadir Factura"
        $activeContract = $supply->contracts->where('is_active', true)->first();
        // Unificamos todas las facturas de todos los contratos de este suministro
        $allInvoices = $supply->contracts->flatMap->invoices->sortByDesc('end_date');
    @endphp

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Suministro: {{ $supply->supply_point_identifier }}</h1>
            <p style="color: #555; font-size: 1.1em; margin-top: 4px;">
                Pertenece a la entidad: 
                <a href="{{ route('entities.show', $supply->entity) }}" style="text-decoration: none; color: #007bff; font-weight: bold;">
                    {{ $supply->entity->name }}
                </a>
            </p>
        </div>
        <a href="{{ route('supplies.edit', $supply) }}" style="background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            Editar Suministro
        </a>
    </div>

    <!-- Sección de Contratos -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Contratos Asociados</h2>
            <div style="display: flex;">
                <a href="{{ route('supplies.contracts.create', $supply) }}" style="background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                    + Añadir Contrato
                </a>
                @if ($activeContract)
                    <a href="{{ route('contracts.invoices.create', $activeContract) }}" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; margin-left: 10px;">
                        + Añadir Factura
                    </a>
                @endif
            </div>
        </div>

        @if($supply->contracts->isEmpty())
            <div style="border: 1px solid #ddd; padding: 20px; text-align: center; border-radius: 8px; background-color: #f9f9f9;">
                <p>Este suministro aún no tiene contratos registrados. Añade un contrato para poder cargar tus facturas.</p>
            </div>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Compañía</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tarifa</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Fecha Inicio</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Estado</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($supply->contracts as $contract)
                        <tr>
                            <td style="padding: 12px; border: 1px solid #ddd;">{{ $contract->utilityCompany->name ?? 'N/A' }}</td>
                            <td style="padding: 12px; border: 1px solid #ddd;">
                                <a href="{{ route('contracts.show', $contract) }}" style="text-decoration: none; color: #007bff; font-weight: bold;">
                                    {{ $contract->rate_name }}
                                </a>
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd;">{{ $contract->start_date->format('d/m/Y') }}</td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                @if($contract->is_active)
                                    <span style="background-color: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">Activo</span>
                                @else
                                    <span style="background-color: #6c757d; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">Inactivo</span>
                                @endif
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                <a href="{{ route('contracts.edit', $contract) }}">Editar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Historial de Facturas -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <h2 style="margin-bottom: 20px;">Historial de Facturas</h2>

        @if($allInvoices->isEmpty())
            <div style="border: 1px solid #ddd; padding: 20px; text-align: center; border-radius: 8px; background-color: #f9f9f9;">
                <p>Aún no se han cargado facturas para este suministro.</p>
                @if ($activeContract)
                    <p style="margin-top: 10px;">
                        <a href="{{ route('contracts.invoices.create', $activeContract) }}" style="text-decoration: none; color: #007bff; font-weight: bold;">
                            ¡Carga la primera ahora!
                        </a>
                    </p>
                @endif
            </div>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Periodo</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Consumo</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Importe Total</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Estado</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allInvoices as $invoice)
                        <tr>
                            <td style="padding: 12px; border: 1px solid #ddd;">{{ $invoice->start_date->format('d/m/Y') }} - {{ $invoice->end_date->format('d/m/Y') }}</td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">{{ number_format($invoice->total_energy_consumed_kwh, 0, ',', '.') }} kWh</td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">{{ number_format($invoice->total_amount, 2, ',', '.') }} €</td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                <span style="background-color: #17a2b8; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                <a href="{{ route('invoices.show', $invoice) }}">Ver Detalles</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>
