<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->string('current_heater_type')->nullable()->after('details'); // electric, gas, wood, solar, none
            $table->boolean('solar_heater_interest')->default(false)->after('current_heater_type');
            $table->text('solar_heater_notes')->nullable()->after('solar_heater_interest');
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            $table->dropColumn(['current_heater_type', 'solar_heater_interest', 'solar_heater_notes']);
        });
    }
};
