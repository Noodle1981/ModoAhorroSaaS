<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rate_id')->constrained('rates')->onDelete('cascade');
            $table->decimal('price_energy_p1', 10, 6)->nullable()->comment('Precio kWh en Punta');
            $table->decimal('price_energy_p2', 10, 6)->nullable()->comment('Precio kWh en Llano');
            $table->decimal('price_energy_p3', 10, 6)->nullable()->comment('Precio kWh en Valle');
            $table->decimal('price_power_p1', 10, 6)->nullable()->comment('Precio Potencia en Punta');
            $table->decimal('price_power_p2', 10, 6)->nullable()->comment('Precio Potencia en Llano');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_prices');
    }
};