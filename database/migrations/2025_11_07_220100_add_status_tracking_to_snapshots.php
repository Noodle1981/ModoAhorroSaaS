<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_usage_snapshots', function (Blueprint $table) {
            // Estados del snapshot
            $table->enum('status', ['draft', 'confirmed', 'invalidated', 'recalculated'])
                ->default('draft')
                ->after('snapshot_date')
                ->comment('Estado: draft=creado, confirmed=usuario aceptó, invalidated=cambios detectados, recalculated=recalculado después de cambios');
            
            // Tracking de invalidación
            $table->timestamp('invalidated_at')->nullable()->after('status')
                ->comment('Cuándo se detectó el cambio que invalidó este snapshot');
            
            $table->text('invalidation_reason')->nullable()->after('invalidated_at')
                ->comment('Descripción del cambio: power_changed, equipment_added, equipment_deleted, etc.');
            
            // Contador de recálculos (ilimitado, pero para auditoría)
            $table->unsignedInteger('recalculation_count')->default(0)->after('invalidation_reason')
                ->comment('Cuántas veces se ha recalculado este snapshot');
            
            // Para mostrar equipos eliminados en histórico
            $table->boolean('is_equipment_deleted')->default(false)->after('recalculation_count')
                ->comment('true si el equipo fue dado de baja (soft delete) después de crear este snapshot');
            
            // Índices
            $table->index('status');
            $table->index(['entity_equipment_id', 'status']);
            $table->index('invalidated_at');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_usage_snapshots', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'invalidated_at',
                'invalidation_reason',
                'recalculation_count',
                'is_equipment_deleted'
            ]);
        });
    }
};
