<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_production_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solar_installation_id')->constrained('solar_installations')->onDelete('cascade');
            $table->timestamp('reading_timestamp');
            $table->decimal('produced_kwh', 10, 5);
            
            $table->unique(['solar_installation_id', 'reading_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_production_readings');
    }
};