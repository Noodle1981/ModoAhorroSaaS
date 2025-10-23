<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ================================================================
        // SOLO EJECUTAMOS LOS SEEDERS DE CATÁLOGO INDISPENSABLES
        // Estos son los datos que TU aplicación necesita para funcionar,
        // no los datos de un usuario de prueba.
        // ================================================================

        $this->call([
            // --- Catálogos Base ---
            CompaniesTableSeeder::class,           // Necesario para el registro de usuarios.
            PlansTableSeeder::class,               // Necesario para que el registro asigne el plan "Gratuito".
            ProvincesTableSeeder::class,           // Necesario para los dropdowns de ubicación.
            LocalitiesTableSeeder::class,          // Necesario para los dropdowns de ubicación.
            EquipmentCategoriesTableSeeder::class, // Necesario para el catálogo de equipos.
            EquipmentTypesTableSeeder::class,      // Necesario para el catálogo de equipos.
            UtilityCompaniesTableSeeder::class,    // Necesario para el catálogo de compañías eléctricas.
            CalculationFactorsTableSeeder::class,  // Necesario para que los cálculos funcionen.
            
            // --- Catálogos Opcionales pero Útiles ---
            MarketEquipmentCatalogTableSeeder::class, // Para que el sistema pueda hacer recomendaciones de reemplazo.
            MaintenanceTasksTableSeeder::class,     // Para que el sistema pueda sugerir mantenimientos.
            RecommendationsTableSeeder::class,      // Catálogo de textos de recomendaciones.
            CarbonIntensityFactorsTableSeeder::class, // Para el cálculo de huella de carbono.
            AppSettingsSeeder::class,               // Configuraciones globales.
        ]);

        // ===================================================================
        // HEMOS ELIMINADO TODAS LAS LLAMADAS A SEEDERS DE DATOS DE PRUEBA
        // COMO Companies, Users, Entities, Supplies, Contracts, Invoices, etc.
        // AHORA LA BASE DE DATOS EMPEZARÁ LIMPIA DE DATOS DE USUARIO.
        // ===================================================================
    }
}