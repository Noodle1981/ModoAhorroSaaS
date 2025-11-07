<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('market_equipment_catalog', function (Blueprint $table) {
            // Renombrar average_price a estimated_price_ars para claridad
            $table->renameColumn('average_price', 'estimated_price_ars');
            
            // Consumo anual estimado en kWh (calculado o ingresado)
            $table->decimal('annual_consumption_kwh', 10, 2)->nullable()->after('power_watts');
            
            // Calificación energética (A+++, A++, A+, A, B, C, D, etc.)
            $table->string('energy_label', 10)->nullable()->after('annual_consumption_kwh');
            
            // Flag para marcar equipos recomendados (alta eficiencia)
            $table->boolean('is_recommended')->default(false)->after('is_active');
            
            // Notas adicionales (características destacables)
            $table->text('features')->nullable()->after('is_recommended');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('market_equipment_catalog', function (Blueprint $table) {
            $table->renameColumn('estimated_price_ars', 'average_price');
            $table->dropColumn(['annual_consumption_kwh', 'energy_label', 'is_recommended', 'features']);
        });
    }
};
