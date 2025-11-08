<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('entity_equipment')) {
            // Entorno de prueba sin tabla base: evitamos fallo de migración
            return;
        }
        Schema::table('entity_equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('entity_equipment', 'optimization_profile')) {
                $table->json('optimization_profile')->nullable()->after('minutes_per_session');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('entity_equipment')) {
            return;
        }
        Schema::table('entity_equipment', function (Blueprint $table) {
            if (Schema::hasColumn('entity_equipment', 'optimization_profile')) {
                $table->dropColumn('optimization_profile');
            }
        });
    }
};
