<?php

// Podría estar en app/Services/InventoryAnalysisService.php
namespace App\Services;

use App\Models\Entity;
use App\Models\MarketEquipmentCatalog;  // Nuevo modelo para el catálogo de mercado
use Illuminate\Support\Collection;  // Para tipar colecciones                   
use Illuminate\Support\Facades\DB;  // Si necesitas hacer consultas complejas
use Illuminate\Support\Facades\Log; // Para logging y debugging


class ReplacementAnalysisService {


public function findReplacementOpportunities(Collection $inventoryWithCalculations, $costoUnitarioKwh)
{
    $opportunities = [];

    foreach ($inventoryWithCalculations as $userEquipment) {
        
        // Validar que existan las relaciones necesarias
        if (!$userEquipment->equipmentType || !$userEquipment->equipmentType->equipmentCategory) {
            Log::warning("Equipment sin tipo o categoría", ['equipment_id' => $userEquipment->id]);
            continue;
        }

        $calculationFactor = $userEquipment->equipmentType->equipmentCategory->calculationFactor;
        if (!$calculationFactor) {
            Log::warning("Categoría sin factor de cálculo", ['category_id' => $userEquipment->equipmentType->equipmentCategory->id]);
            continue;
        }

        // Verificar que exista la tabla de catálogo de mercado
        if (!class_exists('App\Models\MarketEquipmentCatalog')) {
            Log::info("MarketEquipmentCatalog no existe aún. Skipping replacement opportunities.");
            break; // No hay catálogo, salimos del método
        }

        // Buscamos en el catálogo de mercado equipos del MISMO TIPO pero MÁS EFICIENTES
        $potentialReplacements = MarketEquipmentCatalog::where('equipment_type_id', $userEquipment->equipment_type_id)
            ->where('power_watts', '<', $userEquipment->consumo_nominal_kw * 1000)
            ->get();

        if ($potentialReplacements->isEmpty()) {
            continue; // No hay mejores opciones en el mercado
        }

        // Elegimos el mejor reemplazo (podría ser el más barato, el más eficiente, etc.)
        $bestReplacement = $potentialReplacements->sortBy('average_price')->first();

        // --- CÁLCULO DEL AHORRO Y ROI ---
        
        // 1. Energía consumida por el equipo actual (ya la calculamos)
        $energiaActualKwh = $userEquipment->energia_secundaria_kwh ?? 0;

        // 2. Energía que consumiría el equipo nuevo HACIENDO EL MISMO TRABAJO
        $consumoNominalNuevoKW = $bestReplacement->power_watts / 1000;
        $loadFactor = $calculationFactor->load_factor ?? 1;
        $efficiency = $calculationFactor->efficiency_factor ?? 1;
        
        if ($efficiency == 0) $efficiency = 1;

        $energiaNuevaKwh = ($userEquipment->horas_uso_anual * $loadFactor * $userEquipment->quantity * $consumoNominalNuevoKW) / $efficiency;
        
        $ahorroAnualKwh = $energiaActualKwh - $energiaNuevaKwh;
        $ahorroAnualPesos = $ahorroAnualKwh * $costoUnitarioKwh;

        if ($ahorroAnualPesos > 0) {
            $costoInversion = $bestReplacement->average_price ?? 0;
            $retornoInversionAnios = $costoInversion > 0 ? $costoInversion / $ahorroAnualPesos : 0;

            // Guardamos el resultado como un "consejo"
            $opportunities[] = [
                'type' => 'reemplazo',
                'user_equipment' => $userEquipment->custom_name ?? $userEquipment->equipmentType->name ?? 'Equipo sin nombre',
                'suggestion' => "Reemplazar por {$bestReplacement->brand} {$bestReplacement->model_name}",
                'ahorro_anual_pesos' => round($ahorroAnualPesos, 2),
                'retorno_inversion_anios' => round($retornoInversionAnios, 2),
            ];
        }
    }

    return $opportunities;
} 
}