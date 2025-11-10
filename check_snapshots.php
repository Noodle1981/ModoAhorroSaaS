<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use App\Models\EquipmentUsageSnapshot;
use Carbon\Carbon;

echo "=== VERIFICACIÓN DE SNAPSHOTS ===\n\n";

// Buscar la factura del período 15/01/2025 - 20/03/2025
$invoice = Invoice::whereDate('start_date', '2025-01-15')
    ->whereDate('end_date', '2025-03-20')
    ->first();

if (!$invoice) {
    echo "❌ No se encontró la factura del período 15/01/2025 - 20/03/2025\n";
    echo "Buscando facturas cercanas...\n\n";
    
    $nearInvoices = Invoice::whereBetween('start_date', ['2025-01-01', '2025-02-01'])
        ->get();
    
    foreach ($nearInvoices as $inv) {
        echo "Factura ID {$inv->id}: {$inv->start_date} - {$inv->end_date}\n";
    }
    exit(1);
}

echo "✓ Factura encontrada:\n";
echo "  ID: {$invoice->id}\n";
echo "  Período: {$invoice->start_date} - {$invoice->end_date}\n";
echo "  Consumo real: {$invoice->total_energy_consumed_kwh} kWh\n";
echo "  Monto total: \${$invoice->total_amount}\n\n";

// Buscar snapshots para esta factura (incluye soft-deleted)
$snapshots = EquipmentUsageSnapshot::where('invoice_id', $invoice->id)
    ->withTrashed()
    ->get();

echo "=== SNAPSHOTS PARA ESTA FACTURA ===\n";
echo "Total de snapshots encontrados: {$snapshots->count()}\n\n";

if ($snapshots->count() === 0) {
    echo "❌ No hay snapshots guardados para esta factura.\n";
    echo "   Esto significa que el usuario aún no ha guardado ajustes.\n";
    echo "   La vista de entidad seguirá mostrando el cálculo desde inventario actual.\n\n";
    echo "SOLUCIÓN: El usuario debe ir a la página de ajuste (/invoices/{$invoice->id}/snapshots/create)\n";
    echo "          y guardar los valores ajustados.\n";
} else {
    echo "✓ Snapshots encontrados:\n\n";
    
    $activeSnapshots = $snapshots->whereNull('deleted_at');
    $deletedSnapshots = $snapshots->whereNotNull('deleted_at');
    
    echo "--- SNAPSHOTS ACTIVOS ({$activeSnapshots->count()}) ---\n";
    foreach ($activeSnapshots as $snapshot) {
        echo "  • ID: {$snapshot->id}\n";
        echo "    Equipo: {$snapshot->equipment_type_name}\n";
        echo "    Consumo calculado: {$snapshot->calculated_kwh_period} kWh\n";
        echo "    Minutos diarios: {$snapshot->avg_daily_use_minutes}\n";
        echo "    Estado: {$snapshot->status}\n";
        echo "    Creado: {$snapshot->created_at}\n\n";
    }
    
    if ($deletedSnapshots->count() > 0) {
        echo "--- SNAPSHOTS ELIMINADOS ({$deletedSnapshots->count()}) ---\n";
        foreach ($deletedSnapshots as $snapshot) {
            echo "  • ID: {$snapshot->id} (eliminado: {$snapshot->deleted_at})\n";
            echo "    Equipo: {$snapshot->equipment_type_name}\n";
            echo "    Consumo calculado: {$snapshot->calculated_kwh_period} kWh\n\n";
        }
    }
    
    $totalKwhFromSnapshots = $activeSnapshots->sum('calculated_kwh_period');
    $percentageExplained = ($totalKwhFromSnapshots / $invoice->total_energy_consumed_kwh) * 100;
    
    echo "=== RESUMEN DESDE SNAPSHOTS ===\n";
    echo "Total kWh (snapshots activos): {$totalKwhFromSnapshots} kWh\n";
    echo "Consumo real (factura): {$invoice->total_energy_consumed_kwh} kWh\n";
    echo "Nivel de acierto: " . number_format($percentageExplained, 1) . "%\n\n";
    
    if ($percentageExplained > 110) {
        echo "⚠️  Los snapshots aún muestran sobreestimación.\n";
        echo "    Probablemente necesitas ajustar más los minutos de uso diario.\n";
    } elseif ($percentageExplained < 90) {
        echo "⚠️  Los snapshots muestran subestimación.\n";
        echo "    Probablemente necesitas aumentar los minutos de uso diario.\n";
    } else {
        echo "✓ Los snapshots están bien calibrados (90-110%).\n";
    }
}

echo "\n=== FIN DE VERIFICACIÓN ===\n";
