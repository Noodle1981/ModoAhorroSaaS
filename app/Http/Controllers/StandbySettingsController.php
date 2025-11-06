<?php

namespace App\Http\Controllers;

use App\Models\EquipmentCategory;
use App\Models\EntityEquipment;
use Illuminate\Http\Request;

class StandbySettingsController extends Controller
{
    /**
     * Muestra el panel de gestión de standby: categorías + equipos propios del usuario
     */
    public function index()
    {
        $user = auth()->user();

        // Traer categorías con conteo de tipos
        $categories = EquipmentCategory::withCount('equipmentTypes')->orderBy('name')->get();

        // Traer equipos del usuario con tipo y categoría para agrupar
        $equipments = EntityEquipment::whereHas('entity', function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })
        ->with('equipmentType.equipmentCategory', 'entity')
        ->get()
        ->groupBy(function($e) {
            return $e->equipmentType->equipmentCategory->name ?? 'Sin categoría';
        });

        return view('standby.index', compact('categories', 'equipments'));
    }

    /**
     * Actualiza el supports_standby de una categoría
     */
    public function updateCategory(Request $request, EquipmentCategory $category)
    {
        $validated = $request->validate([
            'supports_standby' => ['required', 'boolean']
        ]);

        $category->update($validated);

        return redirect()->route('standby.index')->with('success', "Categoría \"{$category->name}\" actualizada");
    }

    /**
     * Actualiza el has_standby_mode de varios equipos de golpe (bulk)
     */
    public function bulkUpdateEquipment(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'equipment_ids' => ['required', 'array'],
            'equipment_ids.*' => ['exists:entity_equipment,id'],
            'has_standby_mode' => ['required', 'boolean']
        ]);

        EntityEquipment::whereIn('id', $validated['equipment_ids'])
            ->whereHas('entity', function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
            ->update(['has_standby_mode' => $validated['has_standby_mode']]);

        $count = count($validated['equipment_ids']);
        return redirect()->route('standby.index')->with('success', "Actualizados {$count} equipos");
    }
}
