<?php

namespace App\Http\Controllers;

// app/Http/Controllers/ReportController.php
use App\Services\InventoryAnalysisService;
use App\Models\Entity;  // Asegúrate de importar el modelo correcto 
use Illuminate\Http\Request;    
use Illuminate\Support\Facades\DB;
use App\Services\ReplacementAnalysisService; // Importa el servicio de reemplazo
use App\Services\TimeShiftAnalysisService; // Importa el servicio de cambio de tiempo
use App\Services\MaintenanceAnalysisService; // Importa el servicio de mantenimiento
use Illuminate\Support\Collection; // Para tipar colecciones



class ReportController extends Controller
{
    public function equipmentByProcess(Entity $entity, InventoryAnalysisService $analysisService)
    {
        $inventory = $analysisService->calculateForEntity($entity);

        // Usamos la magia de las colecciones de Laravel para agrupar y contar
        $data = $inventory->groupBy('equipmentType.equipmentCategory.name')
                           ->map(function ($group) {
                               return $group->sum('quantity');
                           });
        
        // El resultado será: ['Climatización' => 2, 'Refrigeración' => 1, ...]
        return response()->json($data);
    }

    public function improvementReport(Entity $entity, InventoryAnalysisService $analysisService)
{
    // 1. Obtenemos el costo promedio del kWh de la última factura
    $lastInvoice = $entity->supplies()->first()->contracts()->first()->invoices()->latest()->first();
    $costoUnitarioKwh = $lastInvoice->total_amount / $lastInvoice->total_energy_consumed_kwh;

    // 2. Hacemos el análisis de inventario base
    $inventory = $analysisService->calculateForEntity($entity);
    
    // 3. Llamamos a cada especialista
    $replacementOps = $analysisService->findReplacementOpportunities($inventory, $costoUnitarioKwh);
    $timeShiftOps = $analysisService->findTimeShiftOpportunities($entity, $inventory);
    $maintenanceOps = $analysisService->findMaintenanceOpportunities($inventory, $costoUnitarioKwh);
    
    // 4. Unimos todos los consejos en una sola lista
    $allOpportunities = array_merge($replacementOps, $timeShiftOps, $maintenanceOps);
    
    // 5. Ordenamos por el mayor ahorro potencial
    usort($allOpportunities, fn($a, $b) => $b['ahorro_anual_pesos'] <=> $a['ahorro_anual_pesos']);
    
    // 6. Pasamos la lista a la vista
    return view('reports.improvements', [
        'entity' => $entity,
        'opportunities' => $allOpportunities,
    ]);
}
}