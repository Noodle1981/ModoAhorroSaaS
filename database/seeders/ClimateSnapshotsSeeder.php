<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Entity;

class ClimateSnapshotsSeeder extends Seeder
{
    public function run(): void
    {
        $entity = Entity::first();
        
        if (!$entity) {
            $this->command->warn('No hay entidades. Ejecuta SampleHouseCasaSeeder primero.');
            return;
        }

        $invoices = Invoice::whereHas('contract.supply', function($q) use ($entity) {
            $q->where('entity_id', $entity->id);
        })->get();

        $this->command->info("🌡️  Sincronizando climate snapshots para {$invoices->count()} facturas...");
        $this->command->info("Conectando con Open-Meteo API...\n");

        foreach ($invoices as $invoice) {
            $this->command->info("📅 Factura #{$invoice->id}: {$invoice->start_date->format('d/m/Y')} - {$invoice->end_date->format('d/m/Y')}");
            
            try {
                $entity = $invoice->contract->supply->entity;
                $weatherService = app(\App\Services\WeatherService::class);
                
                // Crear/actualizar snapshot manualmente
                $snapshot = $weatherService->createClimateSnapshot(
                    $entity,
                    $invoice->start_date,
                    $invoice->end_date
                );
                
                // Asociar a factura
                $invoice->climate_snapshot_id = $snapshot->id;
                $invoice->saveQuietly();
                
                $snap = $snapshot;
                $this->command->info("   ✅ {$snap->getClimateCategoryLabel()} - Temp avg: {$snap->avg_temperature_c}°C");
                $this->command->info("   📊 CDD: {$snap->total_cooling_degree_days} | HDD: {$snap->total_heating_degree_days}");
                $this->command->info("   🔥 Días >30°C: {$snap->days_above_30c} | ❄️  Días <15°C: {$snap->days_below_15c}");
                
            } catch (\Exception $e) {
                $this->command->error("   ❌ Error: " . $e->getMessage());
            }
            
            $this->command->newLine();
        }

        $this->command->info("🎉 Proceso completado!");
        $this->command->info("💡 Los datos climáticos se actualizan automáticamente cada vez que se crea/modifica una factura.");
    }
}
