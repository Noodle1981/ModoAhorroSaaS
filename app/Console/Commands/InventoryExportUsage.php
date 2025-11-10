<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Entity;
use App\Models\EntityEquipment;
use App\Services\EquipmentCalculationService;

class InventoryExportUsage extends Command
{
    protected $signature = 'inventory:export-usage
        {--entity= : ID de la entidad a filtrar (opcional)}
        {--format=json : Formato de salida: json o csv}
        {--path= : Ruta relativa dentro de storage/app/ para guardar}
        {--include-standby : Incluir cálculo de consumo standby estimado}
        {--full : Exportar estructura completa con factores de cálculo, ubicación y datos anidados para Python}
        {--invoice-id= : ID de factura para calcular consumo por período en lugar de anual}
        {--days= : Número de días del período para calcular consumo (alternativa a --invoice-id)}';

    protected $description = 'Exporta el inventario con uso derivado usando EquipmentCalculationService.';

    protected $calculationService;

    public function __construct(EquipmentCalculationService $calculationService)
    {
        parent::__construct();
        $this->calculationService = $calculationService;
    }

    public function handle(): int
    {
        $entityId = $this->option('entity');
        $format = strtolower($this->option('format') ?: 'json');
        $includeStandby = (bool)$this->option('include-standby');
        $fullMode = (bool)$this->option('full');
        $invoiceId = $this->option('invoice-id');
        $daysOverride = $this->option('days');

        if (!in_array($format, ['json', 'csv'])) {
            $this->error('Formato no soportado. Use json o csv.');
            return self::FAILURE;
        }
        
        // ============================================
        // Determinar período y tarifa usando Service
        // ============================================
        $periodDays = 365; // Default: anual
        $periodLabel = 'año';
        $averageTariff = 0.2; // Fallback
        
        if ($daysOverride) {
            $periodDays = (int)$daysOverride;
            $periodLabel = 'periodo';
        } elseif ($invoiceId) {
            $invoice = \App\Models\Invoice::find($invoiceId);
            if ($invoice && $invoice->start_date && $invoice->end_date) {
                $periodDays = $invoice->start_date->diffInDays($invoice->end_date);
                $periodLabel = 'periodo';
                
                // Usar service para calcular tarifa
                $averageTariff = $this->calculationService->calculateAverageTariff($invoice);
                
                $this->info("Calculando consumo para período de {$periodDays} días (factura #{$invoiceId})");
                $this->info("Tarifa promedio: \${$averageTariff}");
            } else {
                $this->warn("Factura #{$invoiceId} no encontrada o sin fechas. Usando cálculo anual.");
            }
        }

        // Cargar equipos con relaciones necesarias
        $query = EntityEquipment::with(['entity', 'equipmentType', 'processFactor']);
        if (!empty($entityId)) {
            $query->where('entity_id', $entityId);
        }

        $equipments = $query->get();
        if ($equipments->isEmpty()) {
            $this->warn('No se encontraron equipos para exportar.');
        }

        // ============================================
        // Usar Service para calcular consumos
        // ============================================
        $rows = [];
        $totalKw = 0.0;
        $totalWatts = 0.0;

        foreach ($equipments as $eq) {
            $type = $eq->equipmentType;
            $category = $type?->equipmentCategory;

            // Potencia nominal
            $powerWatts = $eq->power_watts_override ?? $type->default_power_watts ?? 0;
            $kw = $powerWatts / 1000.0;

            // ============================================
            // Usar EquipmentCalculationService para todos los cálculos
            // ============================================
            $calculation = $this->calculationService->calculateEquipmentConsumption($eq, $periodDays, $averageTariff);
            
            // Extraer valores calculados
            $hoursPerDay = $calculation['horas_uso'] / $periodDays;
            $activeKwhPeriod = $calculation['kwh_activo'];
            $standbyKwhPeriod = $includeStandby ? $calculation['kwh_standby'] : 0;
            $totalKwhPeriod = $includeStandby ? $calculation['kwh_total'] : $activeKwhPeriod;
            $costoPeriodo = $calculation['costo'];
            
            // Standby watts para export (estimación)
            $standbyWatts = 0;
            if ($eq->has_standby_mode ?? false) {
                $standbyWatts = max(0.5, min(8.0, $powerWatts * 0.03));
            }

            // Construir row según modo
            if ($fullMode && $format === 'json') {
                // Modo completo para validación Python
                $row = [
                    'nombre' => $type?->name,
                    'tipo_de_proceso' => $eq->tipo_de_proceso,
                    'categoria' => $category?->name,
                    'ubicacion' => $eq->location,
                    'cantidad' => (int)($eq->quantity ?? 1),
                    'potencia_watts' => (int)$powerWatts,
                    'potencia_kw' => (float)$kw,
                    'horas_por_dia' => round($hoursPerDay, 2),
                    'dias_periodo' => $periodDays,
                    'horas_periodo' => round($calculation['horas_uso'], 2),
                    'tiene_standby' => (bool)($eq->has_standby_mode ?? false),
                    'standby_watts' => $standbyWatts,
                    'factor_carga' => (float)($eq->factor_carga ?? 1.0),
                    'factor_eficiencia' => (float)($eq->eficiencia ?? 1.0),
                    "kwh_activo_{$periodLabel}" => $activeKwhPeriod,
                    "kwh_standby_{$periodLabel}" => $standbyKwhPeriod,
                    "kwh_total_{$periodLabel}" => $totalKwhPeriod,
                    "costo_monetario_{$periodLabel}" => $costoPeriodo,
                    'tarifa_promedio' => round($averageTariff, 4),
                ];
                
                $rows[] = $row;
            } else {
                // Modo plano (backward compatible) - también usa service
                $rows[] = [
                    'id' => $eq->id,
                    'entity_id' => $eq->entity_id,
                    'entity' => $eq->entity?->name,
                    'type' => $type?->name,
                    'category' => $category?->name,
                    'tipo_de_proceso' => $eq->tipo_de_proceso,
                    'quantity' => (int)($eq->quantity ?? 1),
                    'power_watts' => (float)$powerWatts,
                    'nominal_kw' => (float)$kw,
                    'hours_per_day' => round($hoursPerDay, 2),
                    'period_days' => $periodDays,
                    'hours_per_period' => round($calculation['horas_uso'], 2),
                    'load_factor' => (float)($eq->factor_carga ?? 1.0),
                    'efficiency_factor' => (float)($eq->eficiencia ?? 1.0),
                    "active_kwh_{$periodLabel}" => $activeKwhPeriod,
                    'standby_power_watts' => $standbyWatts ?: null,
                    "standby_kwh_{$periodLabel}" => $standbyKwhPeriod ?: null,
                    "total_kwh_{$periodLabel}" => $totalKwhPeriod,
                    "cost_{$periodLabel}" => $costoPeriodo,
                ];
            }

            $totalKw += $kw * (int)($eq->quantity ?? 1);
            $totalWatts += $powerWatts * (int)($eq->quantity ?? 1);
        }

        // Ruta de salida
        $relative = $this->option('path');
        if (empty($relative)) {
            if ($fullMode && $format === 'json') {
                $relative = 'exports/equipments.json';
            } else {
                $relative = 'exports/inventory_usage.' . $format;
            }
        }

        $abs = storage_path('app/' . $relative);
        $dir = dirname($abs);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Escribir
        if ($format === 'json') {
            // JSON simple: solo array de equipos
            file_put_contents($abs, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            $csv = $this->toCsv($rows);
            file_put_contents($abs, $csv);
        }
        $this->info("Archivo exportado: {$abs}");
        $this->line(sprintf('Potencia total instalada: %.2f kW (%.2f W)', $totalKw, $totalWatts));
        $this->line(sprintf('Filas exportadas: %d', count($rows)));

        return self::SUCCESS;
    }

    private function toCsv(array $rows): string
    {
        if (empty($rows)) {
            return '';
        }
        $headers = array_keys($rows[0]);
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $headers);
        foreach ($rows as $r) {
            $line = [];
            foreach ($headers as $h) {
                $line[] = $r[$h] ?? '';
            }
            fputcsv($out, $line);
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);
        return $csv ?: '';
    }
}
