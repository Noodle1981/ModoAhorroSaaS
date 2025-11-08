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
        Schema::create('climate_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained()->onDelete('cascade');
            
            // Período que representa (mismo rango que factura)
            $table->date('period_start');
            $table->date('period_end');
            
            // Datos climáticos del período
            $table->decimal('avg_temperature_c', 5, 2);
            $table->decimal('min_temperature_c', 5, 2);
            $table->decimal('max_temperature_c', 5, 2);
            $table->integer('days_above_30c')->default(0); // Días de calor extremo
            $table->integer('days_below_15c')->default(0); // Días de frío
            $table->decimal('total_cooling_degree_days', 6, 2)->default(0);
            $table->decimal('total_heating_degree_days', 6, 2)->default(0);
            $table->integer('avg_humidity_percent')->nullable();
            
            // Categorización del mes para insights rápidos
            $table->enum('climate_category', [
                'muy_caluroso',   // Avg > 30°C
                'caluroso',       // Avg 25-30°C
                'templado',       // Avg 18-25°C
                'fresco',         // Avg 12-18°C
                'frio'            // Avg < 12°C
            ])->nullable();
            
            // Metadata
            $table->enum('data_source', ['api', 'manual', 'estimated'])->default('api');
            $table->text('notes')->nullable(); // Usuario puede agregar contexto
            
            $table->timestamps();
            
            // Un snapshot por período por entidad
            $table->unique(['entity_id', 'period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('climate_snapshots');
    }
};
