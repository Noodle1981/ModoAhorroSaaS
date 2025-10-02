<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Services\InventoryAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ReplacementAnalysisService;
use App\Models\MarketEquipmentCatalog;

class ReportController extends Controller
{
    protected $analysisService;

    public function __construct(InventoryAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Muestra el informe de oportunidades de mejora para una entidad específica.
     * Este método es llamado por una RUTA WEB y devuelve una VISTA.
     */
    public function improvements(Entity $entity)
    {
        // 1. Autorización
        $this->authorize('view', $entity);

        // 2. Verificación de datos de facturación
        $lastInvoice = $entity->supplies
            ->flatMap->contracts
            ->flatMap->invoices
            ->sortByDesc('end_date')
            ->first();

        $hasBillingData = $lastInvoice && $lastInvoice->total_energy_consumed_kwh > 0;

        // 3. Llamada al servicio para obtener oportunidades
        $opportunities = $this->analysisService->findAllOpportunities($entity);

        // 4. Pasamos todos los datos a la vista
        return view('reports.improvements', compact('entity', 'opportunities', 'hasBillingData'));
    }

    /**
     * Muestra el informe de análisis de reemplazo de un equipo.
     */
    public function replacementReport(EntityEquipment $equipment)
    {
        // Asegurarnos de que el equipo que se pasa es el *antiguo*.
        // Cargamos la relación con su reemplazo.
        $oldEquipment = $equipment->load('replacement.equipmentType');
        $newEquipment = $oldEquipment->replacement;

        // Si no hay un equipo de reemplazo, es un error.
        if (!$newEquipment) {
            abort(404, 'Análisis de reemplazo no disponible para este equipo.');
        }

        $this->authorize('view', $oldEquipment->entity);

        // Calcular consumos anuales para ambos
        $oldConsumption = $this->analysisService->getAnnualConsumptionForEquipment($oldEquipment);
        $newConsumption = $this->analysisService->getAnnualConsumptionForEquipment($newEquipment);

        return view('reports.replacement', [
            'oldEquipment' => $oldEquipment,
            'newEquipment' => $newEquipment,
            'oldConsumption' => $oldConsumption,
            'newConsumption' => $newConsumption,
        ]);
    }

    /**
     * Muestra el informe de análisis de reemplazo para todo el inventario de una entidad.
     */
    public function fullReplacementReport(
        Entity $entity,
        InventoryAnalysisService $inventoryService,
        ReplacementAnalysisService $replacementService
    ) {
        $this->authorize('view', $entity);

        // TODO: Obtener este valor dinámicamente del contrato activo de la entidad.
        // Esto requeriría una lógica para encontrar el contrato activo, su tarifa asociada,
        // y el precio de la energía vigente para esa tarifa.
        // Por ahora, usamos un valor promedio para el desarrollo.
        $costoUnitarioKwh = 0.18;

        // 1. Obtener el perfil de consumo anualizado del inventario.
        // Este método ya carga las relaciones necesarias.
        $inventoryWithCalculations = $inventoryService->getAnnualEnergyProfile($entity);

        // 2. Encontrar las oportunidades de reemplazo rentables.
        $opportunities = $replacementService->findReplacementOpportunities($inventoryWithCalculations, $costoUnitarioKwh);
        $opportunitiesMap = collect($opportunities)->keyBy('user_equipment');

        // 3. Preparar los datos para el informe final.
        // Necesitamos iterar sobre el inventario original para incluir TODOS los equipos.
        $reportData = $inventoryWithCalculations->map(function ($equipment) use ($opportunitiesMap) {
            $equipmentName = $equipment->custom_name ?? $equipment->equipmentType->name;
            $status = '';
            $details = null;

            if ($opportunitiesMap->has($equipmentName)) {
                // Caso 1: Es una oportunidad de reemplazo clara.
                $status = 'Reemplazar Equipo';
                $details = $opportunitiesMap->get($equipmentName);
            } else {
                // Caso 2: No es una oportunidad clara. Verificamos si es eficiente o no hay comparativas.
                $hasComparatives = MarketEquipmentCatalog::where('equipment_type_id', $equipment->equipment_type_id)->exists();
                if ($hasComparatives) {
                    // Hay equipos para comparar, pero ninguno es lo suficientemente bueno.
                    $status = 'Equipo Eficiente';
                } else {
                    // No existen equipos del mismo tipo en nuestro catálogo de mercado.
                    $status = 'No hay Equipos Comparativos';
                }
            }

            return (object)[
                'equipment' => $equipment,
                'status' => $status,
                'details' => $details,
            ];
        });

        return view('reports.full_replacement', [
            'entity' => $entity,
            'reportData' => $reportData,
        ]);
    }

    /**
     * Devuelve datos JSON para un gráfico de distribución de equipos por proceso.
     * Este método debería ser llamado por una RUTA DE API.
     */
    public function equipmentByProcess(Entity $entity)
    {
        $this->authorize('view', $entity);
        
        $inventory = $this->analysisService->calculateEnergyProfile($entity);

        $data = $inventory->groupBy('equipmentType.equipmentCategory.name')
                           ->map(fn ($group) => $group->sum('quantity'));
        
        return response()->json($data);
    }
}