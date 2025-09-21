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
    public function show(Entity $entity, InventoryAnalysisService $analysisService)
    {
        $this->authorize('view', $entity);

        $inventoryReport = $analysisService->calculateEnergyProfile($entity);
        $totalInventoryKwhYear = $inventoryReport->sum('energia_total_anual_kwh');
        
        // --- LÓGICA DEFENSIVA ---
        // 1. Buscamos la última factura.
        $lastInvoice = $entity->supplies
            ->flatMap->contracts
            ->flatMap->invoices
            ->sortByDesc('end_date')
            ->first();
        
        // 2. Preparamos el resumen con valores por defecto (null o 0).
        $summary = [
            'inventory_kwh_year' => $totalInventoryKwhYear,
            'inventory_kwh_month_avg' => $totalInventoryKwhYear > 0 ? $totalInventoryKwhYear / 12 : 0,
            'last_invoice_kwh' => null,
            'last_invoice_period' => null,
        ];

        // 3. Si la factura EXISTE, rellenamos los datos.
        if ($lastInvoice) {
            $summary['last_invoice_kwh'] = $lastInvoice->total_energy_consumed_kwh;
            $summary['last_invoice_period'] = $lastInvoice->start_date->format('d/m') . ' - ' . $lastInvoice->end_date->format('d/m/Y');
        }
        // --- FIN DE LA LÓGICA DEFENSIVA ---

        return view('entities.show', compact('entity', 'inventoryReport', 'summary'));
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