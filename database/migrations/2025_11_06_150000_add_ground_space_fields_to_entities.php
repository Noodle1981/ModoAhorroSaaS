<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            // Espacio en terreno/patio para instalación solar
            $table->decimal('ground_area_m2', 8, 2)->nullable()->after('solar_panel_notes'); // Metros cuadrados disponibles
            $table->string('ground_location')->nullable()->after('ground_area_m2'); // front, back, side
            $table->boolean('ground_has_trees')->default(false)->after('ground_location'); // Árboles que dan sombra
            $table->integer('ground_shade_percent')->nullable()->after('ground_has_trees'); // % de sombra del terreno
            $table->text('ground_notes')->nullable()->after('ground_shade_percent'); // Notas adicionales
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn([
                'ground_area_m2',
                'ground_location',
                'ground_has_trees',
                'ground_shade_percent',
                'ground_notes'
            ]);
        });
    }
};
