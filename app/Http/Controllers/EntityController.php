<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;
use App\Services\InventoryAnalysisService; // Asegúrate de que este 'use' esté

class EntityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Entity::class, 'entity');
    }

    public function index()
    {
        $entities = Auth::user()->company->entities()->latest()->get();
        return view('entities.index', compact('entities'));
    }

    public function create()
    {
        $localities = Locality::orderBy('name')->get();
        return view('entities.create', compact('localities'));
    }

    public function store(StoreEntityRequest $request)
    {
        $data = $request->validated();
        $detailsData = $data['details'] ?? [];
        if (isset($detailsData['rooms'])) {
            $detailsData['rooms'] = array_values($detailsData['rooms']);
        }
        $data['details'] = $detailsData;
        Auth::user()->company->entities()->create($data);
        return redirect()->route('entities.index')->with('success', 'Entidad creada exitosamente.');
    }

    /**
     * (SHOW) Muestra los detalles de una entidad específica y su análisis.
     */
 // En app/Http/Controllers/EntityController.php

public function show(Entity $entity, InventoryAnalysisService $analysisService)
{
    $this->authorize('view', $entity);

    // --- LÓGICA DE PERÍODO ACTIVO ---
    
    // 1. Buscamos la última factura.
    $lastInvoice = $entity->supplies
        ->flatMap->contracts
        ->flatMap->invoices
        ->sortByDesc('end_date')
        ->first();
    
    $inventoryReportForPeriod = collect(); // Creamos una colección vacía por defecto
    $periodSummary = [
        'period_days' => 0,
        'period_label' => 'N/A',
        'real_consumption' => null,
        'estimated_consumption' => 0,
    ];

    // 2. Si la factura EXISTE, hacemos el análisis para su período.
    if ($lastInvoice) {
        // Calculamos cuántos días tiene el período de la factura
        $periodDays = Carbon\Carbon::parse($lastInvoice->start_date)->diffInDays($lastInvoice->end_date) + 1;

        // Llamamos a nuestro nuevo método del servicio para este período
        $inventoryReportForPeriod = $analysisService->calculateEnergyProfileForPeriod($entity, $periodDays);
        
        // Preparamos el resumen para la vista
        $periodSummary = [
            'period_days' => $periodDays,
            'period_label' => $lastInvoice->start_date->format('d/m') . ' - ' . $lastInvoice->end_date->format('d/m/Y'),
            'real_consumption' => $lastInvoice->total_energy_consumed_kwh,
            'estimated_consumption' => $inventoryReportForPeriod->sum('consumo_kwh_total_periodo'),
        ];
    }
    
    // --- FIN DE LA LÓGICA DE PERÍODO ---

    return view('entities.show', [
        'entity' => $entity,
        'periodSummary' => (object) $periodSummary, // Lo convertimos a objeto para un acceso más fácil
    ]);
}

    public function edit(Entity $entity)
    {
        $localities = Locality::with('province')->orderBy('name')->get();
        return view('entities.edit', compact('entity', 'localities'));
    }

    public function update(UpdateEntityRequest $request, Entity $entity)
    {
        $data = $request->validated();
        $detailsData = $data['details'] ?? [];
        if (isset($detailsData['rooms'])) {
            $detailsData['rooms'] = array_values($detailsData['rooms']);
        }
        $entity->update($data + ['details' => $detailsData]);
        return redirect()->route('entities.index')->with('success', 'Entidad actualizada exitosamente.');
    }

    public function destroy(Entity $entity)
    {
        $entity->delete();
        return redirect()->route('entities.index')->with('success', 'Entidad eliminada exitosamente.');
    }
}