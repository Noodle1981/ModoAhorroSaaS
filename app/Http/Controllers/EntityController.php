<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;
use App\Services\InventoryAnalysisService; // Asegúrate de que este 'use' esté
use Carbon\Carbon;

class EntityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Entity::class, 'entity');
    }

    public function index()
    {
        $user = Auth::user();
        $entities = collect(); // Initialize as an empty collection
        if ($user->company) {
            $entities = $user->company->entities()->latest()->get();
        }

        // Si el usuario tiene exactamente una entidad, lo redirigimos
        // directamente al "dashboard" de esa entidad (la vista show).
        if ($entities->count() === 1) {
            return redirect()->route('entities.show', $entities->first());
        }

        // Si tiene 0 o más de 1, mostramos la lista normal.
        return view('entities.index', compact('entities', 'user'));
    }

    public function create()
    {
        $user = Auth::user();
        $plan = $user->subscription->plan;
        $allowed_types = $plan->allowed_entity_types; // El modelo Plan ya lo convierte en un array

        $localities = Locality::orderBy('name')->get();
        
        return view('entities.create', [
            'localities' => $localities,
            'allowed_types' => $allowed_types
        ]);
    }

    public function store(StoreEntityRequest $request)
    {
        $data = $request->validated();
        $detailsData = $data['details'] ?? [];
        if (isset($detailsData['rooms'])) {
            $detailsData['rooms'] = array_values($detailsData['rooms']);
        }
        $data['details'] = $detailsData;

        if (!Auth::user()->company) {
            // Handle the case where the user has no company
            return redirect()->back()->with('error', 'No tienes una empresa asociada para crear entidades.');
        }

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

    // Cargamos las relaciones necesarias.
    $entity->load('supplies.contracts.invoices', 'locality', 'equipments');

    // --- LÓGICA DE ANÁLISIS DE PERÍODOS ---
    
    // 1. Obtenemos todas las facturas de la entidad, ordenadas de más reciente a más antigua.
    $allInvoices = $entity->supplies
        ->flatMap->contracts
        ->flatMap->invoices
        ->sortByDesc('end_date');
    
    $periodsAnalysis = [];

    // 2. Iteramos sobre cada factura para generar un análisis para su período.
    foreach ($allInvoices as $invoice) {
        // Calculamos cuántos días tiene el período de la factura.
        $periodDays = Carbon::parse($invoice->start_date)->diffInDays(Carbon::parse($invoice->end_date)) + 1;

        // Llamamos al servicio de análisis para obtener el consumo estimado del inventario.
        $inventoryReport = $analysisService->calculateEnergyProfileForPeriod($entity, $periodDays);
        $estimatedConsumption = $inventoryReport->sum('consumo_kwh_total_periodo');
        
        // Calculamos el porcentaje del consumo que el inventario puede explicar.
        $percentageExplained = $invoice->total_energy_consumed_kwh > 0 
            ? ($estimatedConsumption / $invoice->total_energy_consumed_kwh) * 100
            : 0;

        // Determinar estado de snapshots para esta factura
        $snapshotsForInvoice = \App\Models\EquipmentUsageSnapshot::where('invoice_id', $invoice->id)->get();
        $snapshotStatus = 'needs_first'; // Por defecto: necesita primer ajuste
        
        if ($snapshotsForInvoice->count() > 0) {
            $hasInvalidated = $snapshotsForInvoice->where('status', 'invalidated')->count() > 0;
            $allConfirmed = $snapshotsForInvoice->where('status', 'confirmed')->count() === $snapshotsForInvoice->count();
            
            if ($hasInvalidated) {
                $snapshotStatus = 'needs_readjust'; // Requiere reajuste
            } elseif ($allConfirmed) {
                $snapshotStatus = 'adjusted'; // Ajustado
            } else {
                $snapshotStatus = 'draft'; // Tiene borradores
            }
        }

        // Preparamos el resumen para este período.
        $periodsAnalysis[] = (object) [
            'invoice' => $invoice, // Pasamos la factura completa para el botón
            'period_label' => Carbon::parse($invoice->start_date)->format('d/m/Y') . ' - ' . Carbon::parse($invoice->end_date)->format('d/m/Y'),
            'real_consumption' => $invoice->total_energy_consumed_kwh,
            'estimated_consumption' => $estimatedConsumption,
            'total_amount' => $invoice->total_amount,
            'percentage_explained' => $percentageExplained,
            'snapshot_status' => $snapshotStatus, // Estado de los snapshots
        ];
    }

    // --- CÁLCULO DEL RESUMEN GENERAL ---
    $summary = null;
    if (count($periodsAnalysis) > 0) {
        $analysisCollection = collect($periodsAnalysis);
        $invoiceCount = $analysisCollection->count();

        $totalRealConsumption = $analysisCollection->sum('real_consumption');
        $totalEstimatedConsumption = $analysisCollection->sum('estimated_consumption');
        $totalAmount = $analysisCollection->sum('total_amount');

        $summary = (object) [
            'start_date' => Carbon::parse($allInvoices->last()->start_date)->format('d/m/Y'),
            'end_date' => Carbon::parse($allInvoices->first()->end_date)->format('d/m/Y'),
            'total_real_consumption' => $totalRealConsumption,
            'total_estimated_consumption' => $totalEstimatedConsumption,
            'total_amount' => $totalAmount,
            'average_percentage_explained' => $analysisCollection->avg('percentage_explained'),
            'average_real_consumption' => $totalRealConsumption / $invoiceCount,
            'average_estimated_consumption' => $totalEstimatedConsumption / $invoiceCount,
            'average_amount' => $totalAmount / $invoiceCount,
        ];
    }

    // --- SEPARACIÓN DE DATOS PARA LA VISTA ---
    
    // El primer período (el más reciente) es para el medidor inteligente.
    $meterAnalysis = array_shift($periodsAnalysis) ?? null;
    
    // El resto de los períodos son para la lista del historial.
    $historyPeriods = $periodsAnalysis;

    // --- FIN DE LA LÓGICA DE ANÁLISIS ---

    return view('entities.show', [
        'entity' => $entity,
        'summary' => $summary,
        'meterAnalysis' => $meterAnalysis,
        'periodsAnalysis' => $historyPeriods, // Renombrado para claridad en la vista
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