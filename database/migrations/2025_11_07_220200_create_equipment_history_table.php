<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // ¿Qué cambió?
            $table->string('change_type')->comment('power_changed, usage_changed, type_changed, category_changed, activated, deleted, replaced');
            
            // Valores antes/después (JSON para flexibilidad)
            $table->json('before_values')->nullable()->comment('Estado anterior del equipo');
            $table->json('after_values')->comment('Estado nuevo del equipo');
            
            // Metadata
            $table->text('change_description')->nullable()->comment('Descripción legible del cambio');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            
            // Índices
            $table->index('entity_equipment_id');
            $table->index(['company_id', 'created_at']);
            $table->index('change_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_history');
    }
};
