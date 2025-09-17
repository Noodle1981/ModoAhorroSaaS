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
    Schema::create('calculation_factors', function (Blueprint $table) {
        $table->id();
        // El nombre del método, que coincidirá con el de equipment_categories
        $table->string('method_name')->unique(); 
        $table->decimal('load_factor', 5, 2)->comment('Factor de Carga');
        $table->decimal('efficiency_factor', 5, 2)->comment('Factor de Eficiencia');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculation_factors');
    }
};
