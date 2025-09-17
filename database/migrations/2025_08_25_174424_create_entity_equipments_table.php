<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('equipment_type_id')->constrained('equipment_types');
            
            $table->integer('quantity')->default(1);
            $table->string('custom_name')->nullable();
            
            // Campos para sobreescribir los defaults del catálogo
            $table->integer('power_watts_override')->nullable();
            $table->decimal('avg_daily_use_hours_override', 4, 2)->nullable();

            // Campos para historial y relaciones especiales
            $table->foreignId('replaced_by_equipment_id')->nullable()->constrained('entity_equipment')->nullOnDelete();
            $table->foreignId('is_backup_for_id')->nullable()->constrained('entity_equipment')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes(); // Para el borrado suave (archivado)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_equipment');
    }
};