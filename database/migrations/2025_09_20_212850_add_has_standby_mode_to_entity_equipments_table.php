<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->boolean('has_standby_mode')->default(false)->after('avg_daily_use_hours_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->dropColumn('has_standby_mode');
        });
    }
};
