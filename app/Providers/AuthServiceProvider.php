<?php

namespace App\Providers;

// Importaciones de los Modelos
use App\Models\Entity;
use App\Models\Supply;
use App\Models\Contract;
use App\Models\Invoice;

// Importaciones de las Policies
use App\Policies\EntityPolicy;
use App\Policies\SupplyPolicy;
use App\Policies\ContractPolicy;
use App\Policies\InvoicePolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Entity::class => EntityPolicy::class,
        Supply::class => SupplyPolicy::class,
        Contract::class => ContractPolicy::class,
        Invoice::class => InvoicePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}