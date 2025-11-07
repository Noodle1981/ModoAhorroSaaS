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
        Schema::create('replacement_recommendations', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('entity_equipment_id')->constrained('entity_equipment')->onDelete('cascade');
            $table->foreignId('market_equipment_id')->constrained('market_equipment_catalog')->onDelete('cascade');
            
            // Datos del equipo actual (snapshot al momento de crear recomendación)
            $table->string('current_equipment_name');
            $table->integer('current_power_watts');
            $table->decimal('current_annual_kwh', 10, 2);
            
            // Datos del equipo recomendado (snapshot)
            $table->string('recommended_equipment_name');
            $table->integer('recommended_power_watts');
            $table->decimal('recommended_annual_kwh', 10, 2);
            $table->string('recommended_energy_label', 10)->nullable();
            
            // Cálculos de ahorro y ROI
            $table->decimal('kwh_saved_per_year', 10, 2);
            $table->decimal('money_saved_per_year', 10, 2);
            $table->decimal('money_saved_per_month', 10, 2);
            $table->decimal('investment_required', 12, 2);
            $table->decimal('roi_months', 8, 2)->nullable();
            $table->decimal('kwh_price_used', 8, 2)->default(150.00); // Precio kWh usado en cálculo
            
            // Estado de la recomendación
            $table->enum('status', ['pending', 'accepted', 'rejected', 'in_recovery', 'completed'])->default('pending');
            
            // Tracking de inversión
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->date('recovery_start_date')->nullable(); // Fecha inicio recupero
            $table->date('estimated_recovery_date')->nullable(); // Fecha estimada finalización ROI
            
            // Soft deletes para mantener historial
            $table->softDeletes();
            $table->timestamps();
            
            // Índices
            $table->index(['entity_equipment_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('replacement_recommendations');
    }
};
