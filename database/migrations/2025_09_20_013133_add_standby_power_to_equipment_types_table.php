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
        // Potencia en modo de espera, en Watts. Nulo si no aplica.
        $table->decimal('standby_power_watts', 8, 2)->nullable()->after('default_power_watts');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_types', function (Blueprint $table) {
            //
        });
    }
};
