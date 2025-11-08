<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_usage_snapshots', function (Blueprint $table) {
            $table->boolean('is_daily_use')->nullable()->after('avg_daily_use_minutes');
            $table->tinyInteger('usage_days_per_week')->nullable()->after('is_daily_use');
            $table->json('usage_weekdays')->nullable()->after('usage_days_per_week');
            $table->integer('minutes_per_session')->nullable()->after('usage_weekdays');
            $table->string('frequency_source', 20)->nullable()->after('minutes_per_session')->comment('inherited|adjusted|optimized');
        });
    }

    public function down(): void
    {
        Schema::table('equipment_usage_snapshots', function (Blueprint $table) {
            $table->dropColumn(['is_daily_use', 'usage_days_per_week', 'usage_weekdays', 'minutes_per_session', 'frequency_source']);
        });
    }
};
