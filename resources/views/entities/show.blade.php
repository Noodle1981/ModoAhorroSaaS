<x-app-layout>
    <!-- ... (cabecera de la página: nombre de la entidad, botones, etc.) ... -->
    
    <!-- ======================================================= -->
    <!-- NUEVO DASHBOARD DE RESUMEN: PERÍODO ACTIVO              -->
    <!-- ======================================================= -->
    <div style="margin-top: 20px; padding: 20px; border: 1px solid #eee; border-radius: 8px; background-color: #f9f9f9;">
        <h3>Análisis del Período Activo</h3>
        
        @if($periodSummary->real_consumption === null)
            <p>Aún no has cargado ninguna factura. <a href="#">Carga tu primera factura</a> para activar el análisis.</p>
        @else
            <p style="text-align: center; font-weight: bold; margin-bottom: 20px;">
                Período analizado: {{ $periodSummary->period_label }} ({{ $periodSummary->period_days }} días)
            </p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="text-align: center; padding: 15px; background-color: white; border-radius: 4px;">
                    <span style="font-size: 0.9em; color: #666;">Consumo Real (Según Factura)</span>
                    <p style="font-size: 2em; font-weight: bold; margin: 5px 0; color: #007bff;">
                        {{ number_format($periodSummary->real_consumption, 0, ',', '.') }} kWh
                    </p>
                </div>
                <div style="text-align: center; padding: 15px; background-color: white; border-radius: 4px;">
                    <span style="font-size: 0.9em; color: #666;">Consumo Explicado (Según Inventario)</span>
                    <p style="font-size: 2em; font-weight: bold; margin: 5px 0; color: #17a2b8;">
                        {{ number_format($periodSummary->estimated_consumption, 0, ',', '.') }} kWh
                    </p>
                </div>
            </div>
            @php
                $difference = $periodSummary->real_consumption - $periodSummary->estimated_consumption;
            @endphp
            <p style="margin-top: 15px; text-align: center; color: #333;">
                Tu inventario actual explica el <strong>{{ number_format($periodSummary->estimated_consumption / $periodSummary->real_consumption * 100, 0) }}%</strong> de tu consumo real.
                <br>
                <small>Hay una diferencia de <strong>{{ number_format($difference, 0, ',', '.') }} kWh</strong>. Esto puede deberse a equipos no inventariados, imprecisiones en el uso, o la "Carga Electrónica Agregada".</small>
            </p>
        @endif
    </div>

    
    
    <!-- ... (Botón para ver informe de mejoras, etc.) ... -->
</x-app-layout>