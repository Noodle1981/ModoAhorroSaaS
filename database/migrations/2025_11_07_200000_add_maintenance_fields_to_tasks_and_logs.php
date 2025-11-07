<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_tasks','maintenance_type')) {
                $table->string('maintenance_type')->nullable()->after('recommended_season');
            }
            if (!Schema::hasColumn('maintenance_tasks','variable_interval_json')) {
                $table->json('variable_interval_json')->nullable()->after('maintenance_type');
            }
        });

        Schema::table('maintenance_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_logs','action_type')) {
                $table->string('action_type')->nullable()->after('verification_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_tasks','variable_interval_json')) {
                $table->dropColumn('variable_interval_json');
            }
            if (Schema::hasColumn('maintenance_tasks','maintenance_type')) {
                $table->dropColumn('maintenance_type');
            }
        });

        Schema::table('maintenance_logs', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_logs','action_type')) {
                $table->dropColumn('action_type');
            }
        });
    }
};
