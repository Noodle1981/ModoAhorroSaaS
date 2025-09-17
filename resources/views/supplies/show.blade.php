<x-app-layout>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Suministro: {{ $supply->supply_point_identifier }}</h1>
            <p style="color: #666; font-size: 0.9em;">
                Pertenece a la entidad: 
                <a href="{{ route('entities.show', $supply->entity) }}" style="text-decoration: none; color: #007bff;">
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
            <a href="{{ route('supplies.contracts.create', $supply) }}" style="background-color: #28a745; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                + Añadir Contrato
            </a>
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
                                <!-- Formulario de borrado de contrato iría aquí -->
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-app-layout>