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
            $table->renameColumn('avg_daily_use_hours_override', 'avg_daily_use_minutes_override');
        });

        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->integer('avg_daily_use_minutes_override')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->decimal('avg_daily_use_minutes_override', 4, 2)->nullable()->change();
        });

        Schema::table('entity_equipment', function (Blueprint $table) {
            $table->renameColumn('avg_daily_use_minutes_override', 'avg_daily_use_hours_override');
        });
    }
};