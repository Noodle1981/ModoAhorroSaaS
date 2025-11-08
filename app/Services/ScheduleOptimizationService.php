<?php

namespace App\Services;

use App\Models\EntityEquipment;

/**
 * Servicio de Optimización de Horarios.
 * Primera etapa: lógica de eficiencia para Lavarropas.
 * Próximamente se podrán añadir otros equipos (plancha, ducha eléctrica, lavado de autos, etc.).
 */
class ScheduleOptimizationService
{
    // Constantes heurísticas (podrían moverse a config/schedule_optimization.php en el futuro)
    const KILOS_ROPA_PER_CAPITA_SEMANAL = 3.5;       // kg de ropa promedio generada por persona por semana
    const FACTOR_CARGA_OPTIMA = 0.85;                // 85% de la capacidad se considera carga eficiente
    const HORA_INICIO_TARIFA_REDUCIDA_SEMANA = 18;   // Hora a partir de la cual conviene lavar en días de semana
    const CONSUMO_LAVARROPAS_KWH_POR_CICLO = 0.8;    // Consumo típico por ciclo (kWh)
    const TARIFA_PICO_KWH = 50;                      // $/kWh en pico (placeholder, se puede leer de Invoice)
    const TARIFA_REDUCIDA_KWH = 20;                  // $/kWh reducida (placeholder)

    /**
     * Calcula la recomendación de frecuencia y horario para un lavarropas.
     *
     * @param int $numeroPersonas
     * @param float $capacidadKg Capacidad nominal del lavarropas (ej: 8)
     * @param EntityEquipment|null $equipment Para incluir datos adicionales si se desea
     * @return array{carga_optima_kg:float,generacion_total_semanal_kg:float,lavados_optimos_calculados:float,frecuencia_sugerida:int,horarios:array,weekdays_recomendados:array<int>,es_uso_diario:bool,mensaje:string}
     */
    public function recommendLaundrySchedule(int $numeroPersonas, float $capacidadKg, ?EntityEquipment $equipment = null): array
    {
        // 1. Calcular carga óptima
        $cargaOptimaKg = $capacidadKg * self::FACTOR_CARGA_OPTIMA;
        // 2. Generación total semanal
        $generacionTotalSemanalKg = $numeroPersonas * self::KILOS_ROPA_PER_CAPITA_SEMANAL;
        // 3. Lavados óptimos calculados (puede ser decimal)
        $lavadosOptimosCalculados = $cargaOptimaKg > 0 ? $generacionTotalSemanalKg / $cargaOptimaKg : 0;
        // 4. Frecuencia sugerida (redondeo hacia arriba siempre que > 0)
        $frecuenciaSugerida = $lavadosOptimosCalculados > 0 ? (int)ceil($lavadosOptimosCalculados) : 1;
        if ($frecuenciaSugerida < 1) $frecuenciaSugerida = 1;
        if ($frecuenciaSugerida > 7) $frecuenciaSugerida = 7; // límite físico semanal

        // 5. Determinar días recomendados (priorizando fin de semana y horas reducidas post 18hs)
        // Representación de días de la semana: 1=Lun ... 7=Dom. Fin de semana: 6=Sáb,7=Dom.
        $weekdays = [];
        $prioridadFinDeSemana = [6,7];
        $prioridadSemanalNocturna = [2,4]; // Mar y Jue después de las 18hs (espaciados)
        $adicionales = [3,5,1]; // Mié, Vie, Lun (ordenado por impacto)

        if ($frecuenciaSugerida <= 2) {
            // 1 lavado -> Sábado; 2 lavados -> Sábado + Domingo
            $weekdays[] = 6;
            if ($frecuenciaSugerida == 2) $weekdays[] = 7;
        } elseif ($frecuenciaSugerida == 3) {
            // Sábado + (Martes y Jueves nocturnos)
            $weekdays = [6,2,4];
        } elseif ($frecuenciaSugerida == 4) {
            // Sábado, Domingo, Martes, Jueves
            $weekdays = [6,7,2,4];
        } elseif ($frecuenciaSugerida == 5) {
            // Sábado, Domingo, Martes, Jueves, Miércoles
            $weekdays = [6,7,2,4,3];
        } elseif ($frecuenciaSugerida == 6) {
            // Sábado, Domingo y casi todos los días nocturnos restantes
            $weekdays = [6,7,2,4,3,5];
        } else { // 7
            // Todos los días -> uso diario (se puede marcar is_daily_use)
            $weekdays = [1,2,3,4,5,6,7];
        }

        $esUsoDiario = $frecuenciaSugerida >= 7;

        // 6. Construir descripciones de horarios
        $horarios = [
            'prioridad_1' => 'Fin de semana (Sábado o Domingo) en cualquier horario: tarifa reducida todo el día.',
            'prioridad_2' => 'Días de semana después de las ' . self::HORA_INICIO_TARIFA_REDUCIDA_SEMANA . ':00 (tarifa reducida nocturna).',
            'evitar' => 'Evitar días de semana antes de las ' . self::HORA_INICIO_TARIFA_REDUCIDA_SEMANA . ':00 (horario pico).'
        ];

    // 6. Cálculo de ahorro potencial (comparando pico vs reducido)
    $costoSemanalPico = $frecuenciaSugerida * self::CONSUMO_LAVARROPAS_KWH_POR_CICLO * self::TARIFA_PICO_KWH;
    $costoSemanalReducido = $frecuenciaSugerida * self::CONSUMO_LAVARROPAS_KWH_POR_CICLO * self::TARIFA_REDUCIDA_KWH;
    $ahorroSemanal = $costoSemanalPico - $costoSemanalReducido;
    $ahorroMensual = $ahorroSemanal * 4; // simplificado
    $ahorroAnual = $ahorroSemanal * 52;

    // 7. Mensaje textual para IA / interfaz
    $mensaje = $this->buildMessage($numeroPersonas, $capacidadKg, $frecuenciaSugerida, $generacionTotalSemanalKg, $cargaOptimaKg, $weekdays, $ahorroMensual, $ahorroAnual);

        return [
            'carga_optima_kg' => round($cargaOptimaKg,2),
            'generacion_total_semanal_kg' => round($generacionTotalSemanalKg,2),
            'lavados_optimos_calculados' => round($lavadosOptimosCalculados,2),
            'frecuencia_sugerida' => $frecuenciaSugerida,
            'horarios' => $horarios,
            'weekdays_recomendados' => $weekdays,
            'es_uso_diario' => $esUsoDiario,
            'mensaje' => $mensaje,
            'ahorro' => [
                'semanal' => round($ahorroSemanal, 0),
                'mensual' => round($ahorroMensual, 0),
                'anual' => round($ahorroAnual, 0),
                'costo_pico_semanal' => round($costoSemanalPico, 0),
                'costo_reducido_semanal' => round($costoSemanalReducido, 0),
            ],
        ];
    }

    /**
     * Construye el mensaje explicativo para mostrar / enviar a IA.
     */
    protected function buildMessage(int $personas, float $capacidadKg, int $frecuencia, float $generacionTotalKg, float $cargaOptimaKg, array $weekdays, float $ahorroMensual = 0, float $ahorroAnual = 0): string
    {
        $mapDias = [1=>'Lunes',2=>'Martes',3=>'Miércoles',4=>'Jueves',5=>'Viernes',6=>'Sábado',7=>'Domingo'];
        $diasTexto = implode(', ', array_map(fn($d) => $mapDias[$d], $weekdays));
        $texto = "Analizando tu hogar (".$personas." persona(s)) y tu lavarropas (".$capacidadKg." kg de capacidad, carga óptima ~".round($cargaOptimaKg,2)." kg), la generación estimada semanal de ropa es de ".round($generacionTotalKg,2)." kg. Para maximizar eficiencia y evitar medias cargas, te recomiendo realizar ".$frecuencia." lavado(s) por semana. Días sugeridos: ".$diasTexto.". Prioriza fines de semana y días de semana después de las ".self::HORA_INICIO_TARIFA_REDUCIDA_SEMANA.":00 para aprovechar tarifa reducida.";
        if ($ahorroMensual > 0 || $ahorroAnual > 0) {
            $texto .= " Ahorro mensual estimado: $".round($ahorroMensual,0).". Ahorro anual estimado: $".round($ahorroAnual,0).".";
        }
        return $texto;
    }
}
