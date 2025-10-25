@props(['analysis' => null])

@if ($analysis)
    @php
        $percentage = $analysis->percentage_explained ?? 0;
        if ($percentage > 95) {
            $status = 'Calibrado';
            $statusColor = '#28a745'; // Green
        } elseif ($percentage > 70) {
            $status = 'Necesita Ajuste';
            $statusColor = '#ffc107'; // Yellow
        } else {
            $status = 'Desajuste';
            $statusColor = '#dc3545'; // Red
        }
    @endphp

    <style>
        .digital-meter {
            --meter-color: {{ $statusColor }};
            background-color: #1a202c;
            color: #edf2f7;
            border-radius: 12px;
            padding: 25px;
            font-family: 'Orbitron', sans-serif;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            border: 2px solid #4a5568;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3), inset 0 0 15px rgba(0,0,0,0.5);
        }
        .meter-section {
            background-color: #2d3748;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .meter-label {
            font-size: 0.9em;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meter-value {
            font-size: 2.8em;
            font-weight: bold;
            color: var(--meter-color);
            text-shadow: 0 0 8px var(--meter-color);
            line-height: 1.2;
            transition: color 0.5s ease;
        }
        .meter-unit {
            font-size: 1em;
            color: #a0aec0;
        }
        .status-section {
            grid-column: span 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #2d3748;
            padding: 15px 20px;
            border-radius: 8px;
        }
        .status-text {
            font-size: 1.2em;
            font-weight: bold;
        }
        .status-indicator {
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background-color: var(--meter-color);
            box-shadow: 0 0 12px var(--meter-color);
            transition: background-color 0.5s ease, box-shadow 0.5s ease;
        }
    </style>

    <div class="digital-meter">
        <!-- Live Power Section -->
        <div class="meter-section">
            <div class="meter-label">Consumo Actual</div>
            <div>
                <span id="live-power" class="meter-value">0.000</span>
                <span class="meter-unit">kW</span>
            </div>
        </div>

        <!-- Period Consumption Section -->
        <div class="meter-section">
            <div class="meter-label">Consumo del Período</div>
            <div>
                <span class="meter-value">{{ number_format($analysis->real_consumption, 0, ',', '.') }}</span>
                <span class="meter-unit">kWh</span>
            </div>
        </div>

        <!-- Status Section -->
        <div class="status-section">
            <div class="status-text">Estado: {{ $status }}</div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="status-indicator"></div>
                <span>{{ number_format($percentage, 0) }}% Explicado</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const livePowerElement = document.getElementById('live-power');
            if (livePowerElement) {
                const basePower = ({{ $analysis->real_consumption }} / (30 * 24)) * 0.8; // Average kW
                
                setInterval(() => {
                    const fluctuation = (Math.random() - 0.5) * (basePower * 0.4);
                    const newPower = basePower + fluctuation;
                    const finalPower = Math.max(newPower, basePower * 0.1);
                    
                    livePowerElement.textContent = finalPower.toFixed(3);
                }, 2500);
            }
        });
    </script>
@endif
