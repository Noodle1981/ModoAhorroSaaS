<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        // Crear climate snapshot automáticamente cuando se crea una factura
        $this->syncClimateSnapshot($invoice);
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Si cambiaron las fechas del período, actualizar climate snapshot
        if ($invoice->isDirty(['start_date', 'end_date'])) {
            $this->syncClimateSnapshot($invoice);
        }
    }

    /**
     * Sincroniza datos climáticos para el período de la factura
     */
    private function syncClimateSnapshot(Invoice $invoice): void
    {
        try {
            $entity = $invoice->contract->supply->entity;
            $locality = $entity->locality;

            if (!$locality || !$locality->latitude || !$locality->longitude) {
                Log::warning("Invoice #{$invoice->id}: Entity sin localidad o coordenadas, no se puede sincronizar clima");
                return;
            }

            $weatherService = app(WeatherService::class);

            // 1. Sincronizar datos diarios de Open-Meteo API
            try {
                $stats = $weatherService->fetchAndStoreHistoricalWeather(
                    $locality,
                    $invoice->start_date->format('Y-m-d'),
                    $invoice->end_date->format('Y-m-d')
                );
                
                Log::info("Datos climáticos sincronizados para Invoice #{$invoice->id}", $stats);
            } catch (\Exception $e) {
                Log::warning("No se pudieron obtener datos de API para Invoice #{$invoice->id}: {$e->getMessage()}");
                // Continuar con estimación si falla la API
            }

            // 2. Crear ClimateSnapshot a partir de DailyWeatherLog
            $snapshot = $weatherService->createClimateSnapshot(
                $entity,
                $invoice->start_date,
                $invoice->end_date
            );

            // 3. Asociar snapshot a la factura
            $invoice->climate_snapshot_id = $snapshot->id;
            $invoice->saveQuietly(); // Evitar loop infinito del observer

            Log::info("ClimateSnapshot #{$snapshot->id} creado y asociado a Invoice #{$invoice->id}");

        } catch (\Exception $e) {
            Log::error("Error creando ClimateSnapshot para Invoice #{$invoice->id}: {$e->getMessage()}");
        }
    }
}
