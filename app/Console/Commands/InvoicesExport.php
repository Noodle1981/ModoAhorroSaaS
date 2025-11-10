<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;

class InvoicesExport extends Command
{
    protected $signature = 'invoices:export
        {--entity= : ID de la entidad a filtrar (opcional)}
        {--path= : Ruta relativa dentro de storage/app/ para guardar}';

    protected $description = 'Exporta facturas con tarifa promedio, kWh y período para validación cruzada.';

    public function handle(): int
    {
        $entityId = $this->option('entity');

        $query = Invoice::with(['contract.supply.entity'])
            ->whereHas('contract.supply.entity', function($q) use ($entityId) {
                if (!empty($entityId)) {
                    $q->where('id', $entityId);
                }
            })
            ->orderBy('end_date', 'desc');

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->warn('No se encontraron facturas para exportar.');
        }

        $rows = [];
        foreach ($invoices as $inv) {
            $days = $inv->start_date && $inv->end_date 
                ? $inv->start_date->diffInDays($inv->end_date) 
                : null;
            $avgTariff = $inv->total_energy_consumed_kwh > 0 
                ? $inv->total_amount / $inv->total_energy_consumed_kwh 
                : null;

            $rows[] = [
                'periodo' => $days,
                'fecha_inicio' => $inv->start_date?->toDateString(),
                'fecha_fin' => $inv->end_date?->toDateString(),
                'consumo_total_kwh' => (float)$inv->total_energy_consumed_kwh,
                'valor_factura' => (float)$inv->total_amount,
                'tarifa_promedio' => $avgTariff ? round($avgTariff, 4) : null,
            ];
        }

        // Ruta
        $relative = $this->option('path') ?: 'exports/invoices.json';
        $abs = storage_path('app/' . $relative);
        $dir = dirname($abs);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $payload = $rows;

        file_put_contents($abs, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->info("Facturas exportadas: {$abs}");
        $this->line(sprintf('Total facturas: %d', count($rows)));

        return self::SUCCESS;
    }
}
