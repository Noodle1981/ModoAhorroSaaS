<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            // activated_at ya existe en la tabla
            // Solo agregamos los campos faltantes
            
            $table->date('replaced_at')->nullable()
                ->comment('Fecha en que el equipo fue reemplazado');
            
            $table->foreignId('replaced_by_id')->nullable()
                ->constrained('entity_equipment')
                ->nullOnDelete()
                ->comment('ID del equipo que reemplazó a este');
            
            $table->timestamp('power_last_changed_at')->nullable()
                ->comment('Última vez que cambió power_watts_override');
            
            $table->timestamp('usage_last_changed_at')->nullable()
                ->comment('Última vez que cambió avg_daily_use_minutes_override');
            
            // Índices
            $table->index('activated_at');
            $table->index(['entity_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->dropColumn([
                'replaced_at',
                'replaced_by_id',
                'power_last_changed_at',
                'usage_last_changed_at'
            ]);
        });
    }
};
