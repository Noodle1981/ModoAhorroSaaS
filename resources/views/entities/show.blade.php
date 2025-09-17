<x-app-layout>
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>{{ $entity->name }}</h1>
            <a href="{{ route('entities.index') }}">&larr; Volver a Mis Entidades</a>
        </div>
        <a href="{{ route('entities.edit', $entity) }}" style="background-color: #ffc107; color: black; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
            Editar Entidad
        </a>
    </div>

    <!-- Sección de Suministros -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Suministros Energéticos</h2>
            <a href="{{ route('entities.supplies.create', $entity) }}" style="background-color: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">
                + Añadir Suministro
            </a>
        </div>

        @if($entity->supplies->isEmpty())
            <p>Esta entidad aún no tiene suministros registrados. Añade tu medidor de luz o gas para empezar a cargar contratos y facturas.</p>
        @else
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Tipo</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Identificador (NIS/CUPS)</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entity->supplies as $supply)
                        <tr>
                            <td style="padding: 12px; border: 1px solid #ddd;">{{ ucfirst($supply->type) }}</td>
                            <td style="padding: 12px; border: 1px solid #ddd;">
                                <a href="{{ route('supplies.show', $supply) }}" style="text-decoration: none; color: #007bff; font-weight: bold;">
                                    {{ $supply->supply_point_identifier }}
                                </a>
                            </td>
                            <td style="padding: 12px; border: 1px solid #ddd; text-align: center;">
                                <a href="{{ route('supplies.edit', $supply) }}">Editar</a> |
                                <form action="{{ route('supplies.destroy', $supply) }}" method="POST" style="display: inline;" onsubmit="return confirm('¿Estás seguro? Se eliminarán también todos sus contratos y facturas.');">
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
    </div>

    <!-- Aquí iría la sección de Equipos, que haremos después -->
    <div style="margin-top: 30px; padding: 20px; border: 1px solid #eee; border-radius: 8px;">
        <h2>Inventario de Equipos</h2>
        <p>Próximamente aquí podrás gestionar los equipos de esta entidad...</p>
    </div>
</x-app-layout>