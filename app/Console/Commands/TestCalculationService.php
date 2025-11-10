<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EntityEquipment;
use App\Models\Invoice;
use App\Services\EquipmentCalculationService;

class TestCalculationService extends Command
{
    protected $signature = 'test:calculation-service {--invoice-id=1}';
    protected $description = 'Prueba el EquipmentCalculationService con análisis de standby y reemplazo';

    protected $calculationService;

    public function __construct(EquipmentCalculationService $calculationService)
    {
        parent::__construct();
        $this->calculationService = $calculationService;
    }

    public function handle(): int
    {
        $invoiceId = $this->option('invoice-id');
        
        $this->info("=== TEST: EquipmentCalculationService ===\n");
        
        // Cargar factura
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            $this->error("Factura #{$invoiceId} no encontrada");
            return self::FAILURE;
        }
        
        $days = $invoice->start_date->diffInDays($invoice->end_date);
        $tariff = $this->calculationService->calculateAverageTariff($invoice);
        
        $this->info("Período: {$days} días");
        $this->info("Tarifa: \${$tariff}/kWh\n");
        
        // Cargar equipos
        $equipments = EntityEquipment::with(['equipmentType', 'processFactor'])->get();
        $this->info("Equipos cargados: " . $equipments->count() . "\n");
        
        // ==========================================
        // 1. ANÁLISIS DE STANDBY
        // ==========================================
        $this->info("--- ANÁLISIS DE STANDBY ---");
        $standbyAnalysis = $this->calculationService->calculateStandbySavingsPotential(
            $equipments,
            $days,
            $tariff
        );
        
        $this->line("Total kWh standby: {$standbyAnalysis['standby_kwh']}");
        $this->line("Costo standby: \${$standbyAnalysis['standby_cost']}");
        $this->line("Porcentaje del total: {$standbyAnalysis['savings_percentage']}%");
        $this->line("Equipos con standby: {$standbyAnalysis['equipment_count']}");
        
        if (!empty($standbyAnalysis['equipment_details'])) {
            $this->newLine();
            $this->table(
                ['Equipo', 'Potencia', 'Standby W', 'Horas Standby', 'kWh Standby', 'Costo', 'Ahorro Anual'],
                array_map(function($eq) {
                    return [
                        $eq['nombre'],
                        $eq['potencia_watts'] . 'W',
                        $eq['standby_watts'] . 'W',
                        $eq['horas_standby_periodo'] . 'h',
                        $eq['kwh_standby_periodo'] . ' kWh',
                        '$' . $eq['costo_standby_periodo'],
                        '$' . $eq['ahorro_anual_estimado'],
                    ];
                }, array_slice($standbyAnalysis['equipment_details'], 0, 5))
            );
        }
        
        // ==========================================
        // 2. SUGERENCIAS DE REEMPLAZO
        // ==========================================
        $this->newLine();
        $this->info("--- SUGERENCIAS DE REEMPLAZO ---");
        
        $suggestions = $this->calculationService->generateReplacementSuggestions(
            $equipments,
            $days,
            $tariff
        );
        
        $this->line("Equipos sugeridos para reemplazo: " . count($suggestions));
        
        if (!empty($suggestions)) {
            $this->newLine();
            $equipmentsForTable = $equipments; // Capturar en variable local para closure
            $this->table(
                ['ID', 'Potencia Actual', 'Potencia Nueva', 'Tipo Nuevo', 'Inversión', 'Razón'],
                array_map(function($s) use ($equipmentsForTable) {
                    $eq = $equipmentsForTable->firstWhere('id', $s['current_equipment_id']);
                    $currentPower = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts ?? 0;
                    return [
                        $s['current_equipment_id'],
                        $currentPower . 'W',
                        $s['new_power_watts'] . 'W',
                        $s['new_tipo_de_proceso'],
                        '$' . number_format($s['investment_cost'], 0, ',', '.'),
                        $s['reason'],
                    ];
                }, array_slice($suggestions, 0, 5))
            );
        }
        
        // ==========================================
        // 3. ANÁLISIS DE ROI
        // ==========================================
        if (!empty($suggestions)) {
            $this->newLine();
            $this->info("--- ANÁLISIS DE ROI ---");
            
            $replacementAnalysis = $this->calculationService->calculateReplacementAnalysis(
                $equipments,
                $suggestions,
                $days,
                $tariff
            );
            
            $this->line("Costo actual (período): \${$replacementAnalysis['total_actual_costo_periodo']}");
            $this->line("Costo nuevo (período): \${$replacementAnalysis['total_nuevo_costo_periodo']}");
            $this->line("Ahorro (período): \${$replacementAnalysis['total_ahorro_periodo']}");
            $this->line("Ahorro anual estimado: \${$replacementAnalysis['total_ahorro_anual_estimado']}");
            $this->line("Ahorro porcentaje: {$replacementAnalysis['total_ahorro_porcentaje']}%");
            $this->line("Inversión total: \${$replacementAnalysis['total_inversion']}");
            
            if ($replacementAnalysis['total_payback_años'] !== null) {
                $this->line("Payback: {$replacementAnalysis['total_payback_años']} años");
            }
            
            $this->line("Reemplazos viables: {$replacementAnalysis['viable_count']} de {$replacementAnalysis['equipment_count']}");
            
            if (!empty($replacementAnalysis['comparisons'])) {
                $this->newLine();
                $this->table(
                    ['Equipo', 'Actual kWh', 'Nuevo kWh', 'Ahorro $', 'Ahorro %', 'Inversión', 'Payback', 'Viable'],
                    array_map(function($c) {
                        return [
                            substr($c['nombre'], 0, 30),
                            $c['actual_kwh_periodo'],
                            $c['nuevo_kwh_periodo'],
                            '$' . $c['ahorro_costo_periodo'],
                            $c['ahorro_porcentaje'] . '%',
                            '$' . number_format($c['costo_inversion'], 0, ',', '.'),
                            $c['payback_años'] !== null ? $c['payback_años'] . ' años' : 'N/A',
                            $c['viable'] ? '✓' : '✗',
                        ];
                    }, array_slice($replacementAnalysis['comparisons'], 0, 10))
                );
            }
        }
        
        $this->newLine();
        $this->info("✓ Test completado");
        
        return self::SUCCESS;
    }
}
