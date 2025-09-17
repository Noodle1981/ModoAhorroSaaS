<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumption_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supply_id')->constrained('supplies')->onDelete('cascade');
            $table->timestamp('reading_timestamp');
            $table->decimal('consumed_kwh', 10, 5);
            $table->decimal('injected_kwh', 10, 5)->default(0);
            $table->string('source')->default('smart_meter');

            // Índice para búsquedas rápidas y evitar duplicados
            $table->unique(['supply_id', 'reading_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumption_readings');
    }
};