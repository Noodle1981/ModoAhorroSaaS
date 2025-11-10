<?php

namespace App\Console\Commands;

use App\Models\EntityEquipment;
use App\Models\ProcessFactor;
use Illuminate\Console\Command;

class RecalculateEquipmentFactors extends Command
{
    protected $signature = 'equipment:recalculate-factors';
    protected $description = 'Recalcula factor_carga y eficiencia para todos los equipos según su tipo_de_proceso';

    public function handle(): int
    {
        $equipments = EntityEquipment::whereNotNull('tipo_de_proceso')->get();
        
        $this->info("Recalculando factores para {$equipments->count()} equipos...");
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($equipments as $equipment) {
            // Buscar los factores según tipo_de_proceso
            $factor = ProcessFactor::where('tipo_de_proceso', $equipment->tipo_de_proceso)->first();
            
            if ($factor) {
                $equipment->factor_carga = $factor->factor_carga;
                $equipment->eficiencia = $factor->eficiencia;
                
                // Recalcular energías
                $powerWatts = $equipment->power_watts_override ?? $equipment->equipmentType?->default_power_watts ?? 0;
                $quantity = $equipment->quantity ?? 1;
                $avgDailyUseMinutes = $equipment->avg_daily_use_minutes_override ?? (($equipment->equipmentType?->default_avg_daily_use_hours ?? 0) * 60);
                $horasPorDia = $avgDailyUseMinutes / 60.0;

                $equipment->energia_consumida_wh = ($horasPorDia * $equipment->factor_carga * $quantity * $powerWatts) / $equipment->eficiencia;
                $equipment->energia_util_consumida_wh = $horasPorDia * $equipment->factor_carga * $quantity * $powerWatts * $equipment->eficiencia;

                $equipment->saveQuietly();
                $updated++;
                
                $this->line("✓ {$equipment->equipmentType?->name} - Factor: {$factor->factor_carga}, Eficiencia: {$factor->eficiencia}");
            } else {
                $skipped++;
                $this->warn("✗ {$equipment->equipmentType?->name} - No se encontró factor para '{$equipment->tipo_de_proceso}'");
            }
        }
        
        $this->newLine();
        $this->info("Recálculo completado:");
        $this->line("  - Actualizados: {$updated}");
        $this->line("  - Omitidos: {$skipped}");
        
        return self::SUCCESS;
    }
}
