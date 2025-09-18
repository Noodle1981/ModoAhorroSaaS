<x-app-layout>
    <h1>Informe de Mejoras para: {{ $entity->name }}</h1>
    <p>
        <a href="{{ route('entities.show', $entity) }}">&larr; Volver a la Entidad</a>
    </p>
    <p style="margin-top: 10px;">Basado en los datos que has cargado, hemos encontrado las siguientes oportunidades para que ahorres energía y dinero.</p>

    <div style="margin-top: 30px;">
        @forelse ($opportunities as $op)
            <div style="border-bottom: 1px solid #eee; padding: 15px 0;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="background-color: #007bff; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;">
                            {{ $op['type'] }}
                        </span>
                        <strong style="margin-left: 10px;">{{ $op['user_equipment'] }}</strong>
                    </div>
                    <div style="text-align: right;">
                        <strong style="font-size: 1.2em; color: #28a745;">Ahorro Anual Estimado: ${{ number_format($op['ahorro_anual_pesos'], 2, ',', '.') }}</strong>
                    </div>
                </div>
                <p style="margin-top: 10px; color: #333;">{{ $op['suggestion'] }}</p>
                @if (isset($op['retorno_inversion_anios']))
                    <small style="color: #666;">Retorno de la inversión estimado: {{ number_format($op['retorno_inversion_anios'], 1, ',', '.') }} años.</small>
                @endif
            </div>
        @empty
            <div style="margin-top: 30px; padding: 40px; text-align: center; background-color: #f9f9f9; border-radius: 8px;">
                <h3>¡No hemos encontrado oportunidades de mejora por ahora!</h3>
                <p>Asegúrate de tener un inventario de equipos completo y al menos una factura reciente para obtener los mejores resultados.</p>
            </div>
        @endforelse
    </div>

</x-app-layout>