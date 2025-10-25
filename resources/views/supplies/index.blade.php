<x-app-layout>
    <a href="{{ route('entities.show', $entity) }}" style="text-decoration: none; color: #007bff; margin-bottom: 1rem; display: inline-block;">&larr; Volver a la Entidad</a>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Suministros de {{ $entity->name }}</h1>
        <a href="{{ route('entities.supplies.create', $entity) }}" style="background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            + Añadir Nuevo Suministro
        </a>
    </div>

    @if($supplies->isEmpty())
        <div style="border: 1px solid #ddd; padding: 20px; text-align: center; border-radius: 8px; background-color: #f9f9f9;">
            <p>Aún no has añadido ningún suministro para esta entidad.</p>
        </div>
    @else
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">CUPS</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tarifa</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Potencia Contratada</th>
                    <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($supplies as $supply)
                    @php
                        $activeContract = $supply->contracts->firstWhere('is_active', true);
                    @endphp
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd;"><a href="{{ route('supplies.show', $supply) }}" style="text-decoration: none; color: #007bff; font-weight: bold;">{{ $supply->supply_point_identifier }}</a></td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $activeContract->rate_name ?? 'N/A' }}</td>
                        <td style="padding: 12px; border: 1px solid #ddd;">{{ $activeContract->contracted_power_kw_p1 ?? 'N/A' }} kW</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                            <a href="{{ route('supplies.edit', $supply) }}">Editar</a> |
                            <form action="{{ route('supplies.destroy', $supply) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este suministro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: red; background: none; border: none; cursor: pointer; padding: 0; font-size: inherit; text-decoration: underline;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-app-layout>
