<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            // Datos del techo/espacio disponible
            $table->decimal('roof_area_m2', 8, 2)->nullable()->after('solar_heater_notes'); // Metros cuadrados totales
            $table->decimal('roof_usable_area_m2', 8, 2)->nullable()->after('roof_area_m2'); // Área sin obstáculos
            $table->integer('roof_obstacles_percent')->default(0)->after('roof_usable_area_m2'); // % ocupado por tanques, etc.
            
            // Sombreado
            $table->boolean('has_shading')->default(false)->after('roof_obstacles_percent');
            $table->integer('shading_hours_daily')->nullable()->after('has_shading'); // Horas de sombra promedio
            $table->string('shading_source')->nullable()->after('shading_hours_daily'); // árboles, edificios, etc.
            
            // Orientación e inclinación
            $table->string('roof_orientation')->nullable()->after('shading_source'); // N, S, E, O, NE, etc.
            $table->integer('roof_slope_degrees')->nullable()->after('roof_orientation'); // Inclinación en grados
            
            // Sistema solar instalado o planeado
            $table->boolean('has_solar_panels')->default(false)->after('roof_slope_degrees');
            $table->decimal('installed_solar_kwp', 8, 2)->nullable()->after('has_solar_panels'); // kWp instalados
            $table->boolean('solar_panel_interest')->default(false)->after('installed_solar_kwp');
            $table->text('solar_panel_notes')->nullable()->after('solar_panel_interest');
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn([
                'roof_area_m2', 'roof_usable_area_m2', 'roof_obstacles_percent',
                'has_shading', 'shading_hours_daily', 'shading_source',
                'roof_orientation', 'roof_slope_degrees',
                'has_solar_panels', 'installed_solar_kwp', 'solar_panel_interest', 'solar_panel_notes'
            ]);
        });
    }
};
