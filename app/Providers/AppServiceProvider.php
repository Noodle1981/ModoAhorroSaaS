<?php

namespace App\Providers;

use App\Models\EntityEquipment;
use App\Observers\EntityEquipmentObserver;
use Illuminate\Support\ServiceProvider;

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
    }
}