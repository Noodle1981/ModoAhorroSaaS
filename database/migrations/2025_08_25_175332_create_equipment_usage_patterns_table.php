<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_usage_patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_equipment_id')->constrained('entity_equipment')->onDelete('cascade');
            $table->integer('day_of_week')->nullable()->comment('1 para Lunes, 7 para Domingo. Null si es un hábito diario.');
            $table->time('start_time');
            $table->integer('duration_minutes');
            $table->string('season')->default('all_year')->comment('summer, winter, all_year');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_usage_patterns');
    }
};