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
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->renameColumn('default_avg_daily_use_hours', 'default_avg_daily_use_minutes');
        });

        Schema::table('equipment_types', function (Blueprint $table) {
            $table->integer('default_avg_daily_use_minutes')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            $table->decimal('default_avg_daily_use_minutes', 4, 2)->nullable()->change();
        });

        Schema::table('equipment_types', function (Blueprint $table) {
            $table->renameColumn('default_avg_daily_use_minutes', 'default_avg_daily_use_hours');
        });
    }
};