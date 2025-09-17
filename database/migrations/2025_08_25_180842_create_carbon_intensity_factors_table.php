<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carbon_intensity_factors', function (Blueprint $table) {
            $table->id();
            $table->string('region')->nullable()->default('Argentina')->comment('Sub-región si aplicara');
            $table->string('energy_type')->default('electricity');
            $table->decimal('factor', 10, 6)->comment('kgCO2e por unidad');
            $table->string('unit')->comment('kgCO2e/kWh, kgCO2e/m3');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->string('source')->nullable()->comment('Fuente oficial del dato');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carbon_intensity_factors');
    }
};