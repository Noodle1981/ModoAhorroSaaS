<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Este método es el punto de entrada para el comando `db:seed`.
     * El orden de las llamadas es CRÍTICO para respetar las dependencias de la base de datos.
     */
    public function run(): void
    {
        $this->call([
            // --- GRUPO 1: Cimientos (Catálogos sin dependencias externas) ---
            // Estas tablas son la base de todo. Se pueden poblar en cualquier orden dentro de este grupo.
            PlansTableSeeder::class,
            ProvincesTableSeeder::class,
            EquipmentCategoriesTableSeeder::class,
            AppSettingsSeeder::class,
            UtilityCompaniesSeeder::class,
            CarbonIntensityFactorsTableSeeder::class, // Lo ponemos aquí ya que es un catálogo global.
            RecommendationsTableSeeder::class,      // Es un catálogo, puede ir aquí.

            // --- GRUPO 2: Estructura (Dependen del Grupo 1) ---
            // Ahora poblamos los catálogos que dependen de los cimientos.
            LocalitiesTableSeeder::class,    // Necesita que existan las Provincias.
            EquipmentTypesTableSeeder::class,// Necesita que existan las Categorías de Equipos.
            
            // --- GRUPOS 3, 4, 5, 6 y 7 (Datos de Prueba Vivos) ---
            // A partir de aquí, poblamos con datos de ejemplo que simulan el uso real.
            // Es MUY recomendable usar Factories para esto, pero para empezar lo hacemos con seeders manuales.
            
            CompaniesTableSeeder::class,     // Creamos una compañía de prueba.
            UsersTableSeeder::class,         // Creamos un usuario para esa compañía.
            SubscriptionsTableSeeder::class, // Le asignamos una suscripción a la compañía.
            UserSettingsSeeder::class,     // Añadimos una configuración para nuestro usuario.

            EntitiesTableSeeder::class,      // Creamos una entidad (casa) para nuestra compañía.
            SuppliesTableSeeder::class,      // Creamos un suministro para esa entidad.
            
            RatesTableSeeder::class,         // Creamos tarifas de ejemplo.
            RatePricesTableSeeder::class,    // Asignamos precios a esas tarifas.
            
            ContractsTableSeeder::class,     // Creamos un contrato para el suministro.
            InvoicesTableSeeder::class,      // Creamos una factura para ese contrato.

            EntityEquipmentTableSeeder::class, // Añadimos un equipo (Aire Ac.) a nuestra entidad.
            
            // --- Seeders "Opcionales" o "Relativos" ---
            // Estos seeders crean datos para las funcionalidades avanzadas.
            // Se asume que se aplican a los datos de prueba creados arriba (ej: entity_id = 1).
            
            ConsumptionReadingsTableSeeder::class,   // Simula lecturas de un medidor inteligente.
            SolarInstallationsTableSeeder::class,    // Simula que nuestra entidad de prueba tiene paneles.
            SolarProductionReadingsTableSeeder::class, // Registra la producción de esos paneles.
            MaintenanceTasksTableSeeder::class,        // Crea una tarea de mantenimiento para nuestro tipo de Aire Ac.
            MaintenanceLogsTableSeeder::class,         // Registra un mantenimiento hecho a nuestro Aire Ac.
            EquipmentUsagePatternsTableSeeder::class,  // Registra un hábito de uso para nuestro Aire Ac.
            MarketEquipmentCatalogTableSeeder::class,  // Añade un equipo de mercado para poder recomendarlo.
            DailyWeatherLogsTableSeeder::class,        // Añade datos climáticos para la localidad de nuestra entidad.
        ]);
        
        // ** EL FUTURO: Usando Factories (Mucho más potente) **
        // Cuando te sientas cómodo, puedes reemplazar la mayoría de los seeders de prueba con esto:
        /*
        \App\Models\Company::factory(5)
            ->has(\App\Models\User::factory()->count(2))
            ->has(\App\Models\Entity::factory()->count(3)
                ->has(\App\Models\Supply::factory()->count(1)
                    ->has(\App\Models\Contract::factory()->count(1)
                        ->has(\App\Models\Invoice::factory()->count(12))
                    )
                )
                ->has(\App\Models\EntityEquipment::factory()->count(5))
            )
            ->create();
        */
    }
}