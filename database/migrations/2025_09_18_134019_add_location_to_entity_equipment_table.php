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
        $table->string('location')->nullable()->after('custom_name');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entity_equipment', function (Blueprint $table) {
            //
        });
    }
};
