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
        $table->string('location')->nullable()->comment('Nombre de la habitación o "Portátil"');

        $table->integer('power_watts_override'); // Lo hacemos requerido
        $table->integer('avg_daily_use_minutes_override')->nullable();
        $table->boolean('has_standby_mode')->default(false);

        $table->foreignId('replaced_by_equipment_id')->nullable()->constrained('entity_equipment')->nullOnDelete();
        $table->foreignId('is_backup_for_id')->nullable()->constrained('entity_equipment')->nullOnDelete();
        
        $table->decimal('acquisition_cost', 10, 2)->nullable(); // Costo de adquisición para ROI
        
        $table->timestamps();
        $table->softDeletes();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('entity_equipments');
    }
};