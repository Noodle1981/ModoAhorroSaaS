<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->boolean('is_daily_use')->default(true)->after('avg_daily_use_minutes_override');
            $table->tinyInteger('usage_days_per_week')->nullable()->after('is_daily_use')->comment('Número de días/semana que se usa (si no es diario)');
            $table->json('usage_weekdays')->nullable()->after('usage_days_per_week')->comment('Listado de días específicos (1=Lunes..7=Domingo)');
            $table->integer('minutes_per_session')->nullable()->after('usage_weekdays')->comment('Duración típica de una sesión/ciclo (ej: un lavado)');
        });
    }

    public function down(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->dropColumn(['is_daily_use', 'usage_days_per_week', 'usage_weekdays', 'minutes_per_session']);
        });
    }
};
