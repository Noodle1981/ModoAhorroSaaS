<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Models\MarketEquipmentCatalog;
use App\Models\ReplacementRecommendation;
use App\Models\SmartAlert;
use Illuminate\Support\Facades\Log;

class EquipmentReplacementService
{
    /**
     * Precio promedio del kWh en Argentina (configurable)
     */
    private float $kwh_price;

    /**
     * Umbral mínimo de ahorro para generar recomendación (%)
     */
    private float $min_savings_percentage;

    public function __construct(float $kwh_price = 150.00, float $min_savings_percentage = 15.0)
    {
        $this->kwh_price = $kwh_price;
        $this->min_savings_percentage = $min_savings_percentage;
    }

    /**
     * Analiza todos los equipos de una entidad y genera recomendaciones
     */
    public function analyzeEntityEquipment(Entity $entity): array
    {
        Log::info("🔍 Iniciando análisis de reemplazos para entidad: {$entity->name} (ID: {$entity->id})");

        $results = [
            'entity_id' => $entity->id,
            'entity_name' => $entity->name,
            'total_equipment' => 0,
            'analyzed' => 0,
            'recommendations_generated' => 0,
            'no_replacement_found' => 0,
            'insufficient_savings' => 0,
            'details' => [],
        ];

        $equipments = EntityEquipment::where('entity_id', $entity->id)->get();
        $results['total_equipment'] = $equipments->count();

        foreach ($equipments as $equipment) {
            $result = $this->analyzeEquipment($equipment);
            $results['analyzed']++;
            
            if ($result['recommendation_created']) {
                $results['recommendations_generated']++;
            } elseif ($result['reason'] === 'no_match') {
                $results['no_replacement_found']++;
            } elseif ($result['reason'] === 'insufficient_savings') {
                $results['insufficient_savings']++;
            }

            $results['details'][] = $result;
        }

        Log::info("✅ Análisis completado: {$results['recommendations_generated']} recomendaciones generadas de {$results['analyzed']} equipos");

        return $results;
    }

    /**
     * Analiza un equipo individual y genera recomendación si aplica
     */
    public function analyzeEquipment(EntityEquipment $equipment): array
    {
        $result = [
            'equipment_id' => $equipment->id,
            'equipment_name' => $equipment->custom_name ?? $equipment->equipmentType->name,
            'recommendation_created' => false,
            'reason' => null,
            'message' => null,
        ];

        // Verificar si ya tiene una recomendación activa
        $existingRecommendation = ReplacementRecommendation::where('entity_equipment_id', $equipment->id)
            ->active()
            ->first();

        if ($existingRecommendation) {
            $result['reason'] = 'already_exists';
            $result['message'] = 'Ya existe una recomendación activa para este equipo';
            return $result;
        }

        // Calcular consumo anual actual del equipo
        $currentAnnualKwh = $this->calculateAnnualConsumption($equipment);

        if ($currentAnnualKwh <= 0) {
            $result['reason'] = 'no_consumption_data';
            $result['message'] = 'No se puede calcular consumo anual (falta potencia o horas de uso)';
            Log::warning("⚠️ Equipo sin datos de consumo: {$equipment->custom_name} (ID: {$equipment->id})");
            return $result;
        }

        // Buscar equipos recomendados del mismo tipo en el catálogo
        $betterAlternatives = $this->findBetterAlternatives($equipment, $currentAnnualKwh);

        if ($betterAlternatives->isEmpty()) {
            $result['reason'] = 'no_match';
            $result['message'] = 'No se encontró equipo eficiente en el catálogo para este tipo';
            
            Log::warning("🔍 SIN REEMPLAZO ENCONTRADO - Tipo: {$equipment->equipmentType->name}, Equipo: {$equipment->custom_name} (ID: {$equipment->id})");
            Log::warning("   💡 ACCIÓN REQUERIDA: Agregar equipos de tipo '{$equipment->equipmentType->name}' al catálogo");
            
            return $result;
        }

        // Tomar la mejor alternativa (mayor ahorro)
        $bestAlternative = $betterAlternatives->first();

        // Calcular ahorros y ROI
        $savings = $bestAlternative->calculateAnnualSavings(
            new MarketEquipmentCatalog([
                'annual_consumption_kwh' => $currentAnnualKwh,
            ]),
            $this->kwh_price
        );

        // Verificar si el ahorro justifica la recomendación
        $savingsPercentage = ($savings['kwh_saved_per_year'] / $currentAnnualKwh) * 100;

        if ($savingsPercentage < $this->min_savings_percentage) {
            $result['reason'] = 'insufficient_savings';
            $result['message'] = "Ahorro insuficiente ({$savingsPercentage}% < {$this->min_savings_percentage}%)";
            return $result;
        }

        // Crear recomendación
        $recommendation = ReplacementRecommendation::create([
            'entity_equipment_id' => $equipment->id,
            'market_equipment_id' => $bestAlternative->id,
            'current_equipment_name' => $equipment->custom_name ?? $equipment->equipmentType->name,
            'current_power_watts' => $equipment->power_watts ?? 0,
            'current_annual_kwh' => $currentAnnualKwh,
            'recommended_equipment_name' => "{$bestAlternative->brand} {$bestAlternative->model_name}",
            'recommended_power_watts' => $bestAlternative->power_watts,
            'recommended_annual_kwh' => $bestAlternative->annual_consumption_kwh,
            'recommended_energy_label' => $bestAlternative->energy_label,
            'kwh_saved_per_year' => $savings['kwh_saved_per_year'],
            'money_saved_per_year' => $savings['money_saved_per_year'],
            'money_saved_per_month' => $savings['money_saved_per_month'],
            'investment_required' => $savings['investment_required'],
            'roi_months' => $savings['roi_months'],
            'kwh_price_used' => $this->kwh_price,
            'status' => 'pending',
        ]);

        // Crear alerta inteligente si el ROI es atractivo (< 24 meses)
        if ($savings['roi_months'] && $savings['roi_months'] <= 24) {
            try {
                SmartAlert::create([
                    'entity_id' => $equipment->entity_id,
                    'invoice_id' => null,
                    'type' => 'equipment_inefficiency',
                    'severity' => 'info',
                    'title' => 'Reemplazo recomendado: ROI < 24 meses',
                    'description' => sprintf(
                        'El equipo "%s" puede reemplazarse por "%s". Ahorro anual $%s, ROI %s meses.',
                        $result['equipment_name'] ?? ($equipment->custom_name ?? $equipment->equipmentType->name),
                        $bestAlternative->brand.' '.$bestAlternative->model_name,
                        number_format($savings['money_saved_per_year'], 0),
                        number_format($savings['roi_months'], 1)
                    ),
                    'data' => [
                        'recommendation_id' => $recommendation->id,
                        'kwh_saved_per_year' => $savings['kwh_saved_per_year'],
                        'money_saved_per_year' => $savings['money_saved_per_year'],
                        'roi_months' => $savings['roi_months'],
                    ],
                ]);
            } catch (\Throwable $e) {
                Log::warning('No se pudo crear SmartAlert para reemplazo: '.$e->getMessage());
            }
        }

        $result['recommendation_created'] = true;
        $result['recommendation_id'] = $recommendation->id;
        $result['message'] = "Recomendación creada: ahorro de {$savings['kwh_saved_per_year']} kWh/año, ROI {$savings['roi_months']} meses";

        Log::info("✅ Recomendación creada: {$equipment->custom_name} → {$bestAlternative->brand} {$bestAlternative->model_name} (Ahorro: {$savingsPercentage}%)");

        return $result;
    }

    /**
     * Calcula el consumo anual de un equipo en kWh
     */
    private function calculateAnnualConsumption(EntityEquipment $equipment): float
    {
        // Si el equipo tiene un patrón de uso, usarlo
        if ($equipment->usagePattern) {
            $avgDailyHours = $equipment->usagePattern->avg_daily_hours ?? 0;
        } else {
            // Usar horas por defecto según tipo de equipo (esto es un estimado)
            $avgDailyHours = $this->getDefaultUsageHours($equipment);
        }

        if (!$equipment->power_watts || $avgDailyHours <= 0) {
            return 0;
        }

        // Consumo anual = (Potencia en W / 1000) × Horas diarias × 365 días
        return ($equipment->power_watts / 1000) * $avgDailyHours * 365;
    }

    /**
     * Obtiene horas de uso por defecto según tipo de equipo
     */
    private function getDefaultUsageHours(EntityEquipment $equipment): float
    {
        $typeName = strtolower($equipment->equipmentType->name ?? '');

        // Mapeo básico de horas de uso por tipo
        if (str_contains($typeName, 'heladera') || str_contains($typeName, 'refrigerador')) {
            return 24; // Heladeras funcionan 24/7
        } elseif (str_contains($typeName, 'aire') || str_contains($typeName, 'ac')) {
            return 8; // AC promedio 8 horas/día
        } elseif (str_contains($typeName, 'lavarropas') || str_contains($typeName, 'lavadora')) {
            return 1; // 1 hora/día promedio
        } elseif (str_contains($typeName, 'tv') || str_contains($typeName, 'televisor')) {
            return 5; // TV promedio 5 horas/día
        } elseif (str_contains($typeName, 'horno') || str_contains($typeName, 'microondas')) {
            return 0.5; // 30 min/día
        }

        return 4; // Default 4 horas/día
    }

    /**
     * Busca alternativas más eficientes en el catálogo
     */
    private function findBetterAlternatives(EntityEquipment $equipment, float $currentAnnualKwh)
    {
        return MarketEquipmentCatalog::where('equipment_type_id', $equipment->equipment_type_id)
            ->where('is_active', true)
            ->where('is_recommended', true)
            ->whereNotNull('annual_consumption_kwh')
            ->where('annual_consumption_kwh', '<', $currentAnnualKwh) // Debe consumir menos
            ->orderBy('annual_consumption_kwh', 'asc') // Ordenar por menor consumo
            ->get();
    }
}
