<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_weather_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locality_id')->constrained('localities')->onDelete('cascade');
            $table->date('date');
            
            $table->decimal('avg_temp_celsius', 5, 2)->nullable();
            $table->decimal('min_temp_celsius', 5, 2)->nullable();
            $table->decimal('max_temp_celsius', 5, 2)->nullable();

            $table->decimal('heating_degree_days', 5, 2)->nullable()->comment('Grados día de calefacción');
            $table->decimal('cooling_degree_days', 5, 2)->nullable()->comment('Grados día de refrigeración');

            $table->unique(['locality_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_weather_logs');
    }
};