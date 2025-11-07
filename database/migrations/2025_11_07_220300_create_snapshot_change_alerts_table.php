<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshot_change_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Trigger del cambio
            $table->foreignId('entity_equipment_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Equipo que cambió y causó la invalidación');
            
            $table->foreignId('equipment_history_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Referencia al cambio específico en equipment_history');
            
            // Detalles de la alerta
            $table->string('alert_type')->comment('power_changed, equipment_added, equipment_deleted, recalculated');
            $table->text('message')->comment('Mensaje legible para el usuario');
            $table->json('affected_snapshots')->comment('Array de IDs de snapshots invalidados');
            
            // Estado
            $table->enum('status', ['pending', 'acknowledged', 'resolved'])->default('pending');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['entity_id', 'status']);
            $table->index(['company_id', 'created_at']);
            $table->index('alert_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshot_change_alerts');
    }
};
