<?php

namespace App\Providers;

use App\Models\EntityEquipment;
use App\Models\Invoice;
use App\Observers\EntityEquipmentObserver;
use App\Observers\InvoiceObserver;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\InventoryExportUsage;
use App\Console\Commands\InvoicesExport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observers
        EntityEquipment::observe(EntityEquipmentObserver::class);
        Invoice::observe(InvoiceObserver::class);

        // Registrar comandos de consola
        if ($this->app->runningInConsole()) {
            $this->commands([
                InventoryExportUsage::class,
                InvoicesExport::class,
            ]);
        }
    }
}