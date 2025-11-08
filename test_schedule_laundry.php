<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ScheduleOptimizationService;
use App\Models\EntityEquipment;

$equipmentId = 307; // ajustar si es distinto
$eq = EntityEquipment::find($equipmentId);
if(!$eq){
    echo "❌ Equipo $equipmentId no encontrado\n"; exit(1);
}

$service = new ScheduleOptimizationService();
// Parámetros de prueba (personas, capacidad kg)
$result = $service->recommendLaundrySchedule(4, 8, $eq);

echo "✅ Recomendación para equipo ID $equipmentId (".($eq->custom_name ?? $eq->equipmentType->name).")\n";
echo "Frecuencia sugerida: {$result['frecuencia_sugerida']} lavados/semana\n";
echo "Días recomendados: ".implode(',', $result['weekdays_recomendados'])."\n";
echo "Carga óptima kg: {$result['carga_optima_kg']} | Generación semanal kg: {$result['generacion_total_semanal_kg']}\n";
echo "Ahorro semanal potencial: $".$result['ahorro']['semanal']." | Mensual: $".$result['ahorro']['mensual']." | Anual: $".$result['ahorro']['anual']."\n";
echo "Costo pico semanal: $".$result['ahorro']['costo_pico_semanal']." vs reducido: $".$result['ahorro']['costo_reducido_semanal']."\n";
echo "Mensaje: \n".$result['mensaje']."\n";
