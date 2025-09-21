<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Services\InventoryAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $analysisService;

    /**
     * Inyectamos el servicio de análisis en el constructor para
     * tenerlo disponible en todos los métodos del controlador.
     */
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