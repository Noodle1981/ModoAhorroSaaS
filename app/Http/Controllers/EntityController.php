<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;

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
        $provinces = Province::orderBy('name')->get();
        return view('entities.create', compact('localities', 'provinces'));
    }

   public function store(StoreEntityRequest $request)
{
    // Obtenemos todos los datos validados
    $validatedData = $request->validated();
    
    
    unset($validatedData['province_id']);

    // Procesamos el JSON 'details' como ya lo hacíamos
    $detailsData = $validatedData['details'] ?? [];
    if (isset($detailsData['rooms'])) {
        $detailsData['rooms'] = array_values($detailsData['rooms']);
    }
    $validatedData['details'] = $detailsData;

    // Creamos la entidad con los datos limpios
    Auth::user()->company->entities()->create($validatedData);

    return redirect()->route('entities.index')
                     ->with('success', 'Entidad creada exitosamente.');
}

    /**
     * (SHOW) Muestra los detalles de una entidad específica y su análisis.
     */
 // En app/Http/Controllers/EntityController.php

public function show(Entity $entity, InventoryAnalysisService $analysisService)
{
    $this->authorize('view', $entity);

    // Cargar relaciones necesarias de una vez para optimizar
    $entity->load([
        'supplies.contracts.invoices' => function ($query) {
            $query->withSum('snapshots as adjusted_consumption', 'calculated_kwh_period')
                  ->orderBy('end_date', 'desc');
        },
        'entityEquipments.equipmentType'
    ]);

    // --- LÓGICA DE PERÍODO ACTIVO ---
    $allInvoices = $entity->supplies->flatMap->contracts->flatMap->invoices->unique('id');
    $lastInvoice = $allInvoices->first();
    
    $inventoryReportForPeriod = collect();
    $periodSummary = [
        'period_days' => 0,
        'period_label' => 'N/A',
        'real_consumption' => null,
        'estimated_consumption' => 0,
    ];

    if ($lastInvoice) {
        $inventoryReportForPeriod = $analysisService->calculateEnergyProfileForPeriod($entity, $lastInvoice);
        $periodSummary = [
            'period_days' => $lastInvoice->start_date->diffInDays($lastInvoice->end_date) + 1,
            'period_label' => Carbon::parse($lastInvoice->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($lastInvoice->end_date)->format('d/m/Y'),
            'real_consumption' => $lastInvoice->total_energy_consumed_kwh,
            'estimated_consumption' => $inventoryReportForPeriod->sum('consumo_kwh_total_periodo'),
        ];
    }
    
    // --- LÓGICA PARA OBTENER CONTRATO ACTIVO ---
    $activeContract = $entity->supplies->flatMap->contracts->where('is_active', true)->first() 
                    ?? $entity->supplies->flatMap->contracts->first();

    // Obtenemos TODOS los equipos, incluyendo los eliminados, para el historial.
    // Calculamos también el promedio de uso real desde los snapshots.
    $allEntityEquipments = $entity->entityEquipments()
        ->withTrashed()
        ->with('equipmentType.equipmentCategory')
        ->withAvg('usageSnapshots', 'avg_daily_use_minutes')
        ->latest()
        ->get();

    return view('entities.show', [
        'entity' => $entity,
        'allEntityEquipments' => $allEntityEquipments,
        'periodSummary' => (object) $periodSummary,
        'allInvoices' => $allInvoices,
        'activeContract' => $activeContract,
    ]);
}

    public function edit(Entity $entity)
    {
        $localities = Locality::with('province')->orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        return view('entities.edit', compact('entity', 'localities', 'provinces'));
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