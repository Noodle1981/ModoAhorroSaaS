<?php


namespace App\Http\Controllers;
use App\Http\Requests\StoreEntityEquipmentRequest;
use App\Http\Requests\UpdateEntityEquipmentRequest;
use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
class EntityEquipmentController extends Controller
{
    public function create(Request $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);

        $type = $request->query('type', 'fixed'); // 'fixed' o 'portable'. Default a 'fixed'.

        // Filtra las categorías y tipos según si es fijo o portátil
        $categories = EquipmentCategory::with(['equipmentTypes' => function ($query) use ($type) {
            $query->where('is_portable', $type === 'portable');
        }])->orderBy('name')->get()->filter(fn ($category) => $category->equipmentTypes->isNotEmpty());
        
        $locations = [];
        if ($type === 'fixed') {
            $roomsData = $entity->details['rooms'] ?? [];
            $locations = collect($roomsData)->pluck('name')->filter()->unique()->all();
        }

        return view('equipment.create', compact('entity', 'categories', 'locations', 'type'));
    }

    public function store(StoreEntityEquipmentRequest $request, Entity $entity)
    {
        $this->authorize('create', [EntityEquipment::class, $entity]);
        
        $validatedData = $request->validated();
        
        // Si es portátil, forzamos la ubicación a NULL (o a "Portátil" si lo prefieres)
        $equipmentType = \App\Models\EquipmentType::find($validatedData['equipment_type_id']);
        if ($equipmentType && $equipmentType->is_portable) {
            $validatedData['location'] = null; // O 'Portátil'
        }

        $entity->entityEquipments()->create($validatedData);

        return redirect()->route('entities.show', $entity)->with('success', 'Equipo añadido con éxito.');
    }
    
    public function edit(EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);

        $categories = EquipmentCategory::with('equipmentTypes')->orderBy('name')->get();
        
        $roomsData = $equipment->entity->details['rooms'] ?? [];
        $locations = collect($roomsData)->pluck('name')->filter()->unique()->all();

        // Determinamos el tipo para la vista
        $type = $equipment->equipmentType->is_portable ? 'portable' : 'fixed';

        return view('equipment.edit', compact('equipment', 'categories', 'locations', 'type'));
    }

    public function update(UpdateEntityEquipmentRequest $request, EntityEquipment $equipment)
    {
        $this->authorize('update', $equipment);
        
        $validatedData = $request->validated();
        
        $equipmentType = \App\Models\EquipmentType::find($validatedData['equipment_type_id']);
        if ($equipmentType && $equipmentType->is_portable) {
            $validatedData['location'] = null; // O 'Portátil'
        }

        $equipment->update($validatedData);
        return redirect()->route('entities.show', $equipment->entity)->with('success', 'Equipo actualizado.');
    }

    public function show(Entity $entity, InventoryAnalysisService $analysisService)
{
    // 1. Autorización
    $this->authorize('view', $entity);

    // 2. Carga ansiosa de relaciones para optimizar consultas
    $entity->load([
        'locality.province',
        'supplies.contracts.invoices.usageSnapshots', // Carga todo el árbol de facturación
        'entityEquipments' => function ($query) {
            $query->withTrashed()->with('equipmentType.equipmentCategory', 'usageSnapshots'); // Incluye equipos borrados
        },
    ]);

    // 3. Obtener todas las facturas de la entidad
    $allInvoices = $entity->supplies->flatMap->contracts->flatMap->invoices->sortByDesc('end_date');

    // 4. Calcular el "Consumo Ajustado" para cada factura
    $allInvoices->each(function ($invoice) {
        $adjustedConsumption = $invoice->usageSnapshots->sum('calculated_kwh_period');
        $invoice->adjusted_consumption = $adjustedConsumption;
    });

    // 5. Encontrar el contrato activo
    $activeContract = $entity->supplies->flatMap->contracts->where('is_active', true)->first();
    
    // 6. Preparar el resumen del período activo (basado en la última factura)
    $lastInvoice = $allInvoices->first();
    $periodSummary = (object) [
        'period_label' => 'N/A',
        'period_days' => 0,
        'real_consumption' => 0,
        'estimated_consumption' => 0,
    ];

    if ($lastInvoice) {
        $periodDays = $lastInvoice->start_date->diffInDays($lastInvoice->end_date) + 1;
        $inventoryForPeriod = $analysisService->calculateEnergyProfileForPeriod($entity, $periodDays);
        
        $periodSummary->period_label = $lastInvoice->start_date->format('d/m') . ' - ' . $lastInvoice->end_date->format('d/m/Y');
        $periodSummary->period_days = $periodDays;
        $periodSummary->real_consumption = $lastInvoice->total_energy_consumed_kwh;
        // El consumo estimado lo recalculamos aquí para el dashboard, pero el "ajustado" histórico viene de los snapshots
        $periodSummary->estimated_consumption = $lastInvoice->adjusted_consumption;
    }

    // 7. Preparar los datos del inventario de equipos
    $allEntityEquipments = $entity->entityEquipments;
    
    // Calcular el uso promedio real para cada equipo a partir de sus snapshots
    $allEntityEquipments->each(function ($equipment) {
        // Usamos la función de agregación de Laravel para calcular el promedio directamente en la relación
        $avgMinutes = $equipment->usageSnapshots()->avg('avg_daily_use_minutes');
        $equipment->usage_snapshots_avg_avg_daily_use_minutes = $avgMinutes;
    });

    // 8. Pasar todas las variables a la vista
    return view('entities.show', compact(
        'entity',
        'activeContract',
        'allInvoices',
        'periodSummary',
        'allEntityEquipments'
    ));
}

    public function destroy(EntityEquipment $equipment)
    {
        $this->authorize('delete', $equipment);
        $entity = $equipment->entity;
        $equipment->delete();
        return redirect()->route('entities.show', $entity)->with('success', 'Equipo eliminado.');
    }

    // He quitado preDestroy y la lógica de reemplazo para simplificar.
    // Eso lo podemos añadir después como un flujo separado.
}