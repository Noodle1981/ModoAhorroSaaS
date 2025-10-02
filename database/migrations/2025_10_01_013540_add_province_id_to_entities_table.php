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
        Schema::table('entities', function (Blueprint $table) {
            // Add province_id column after locality_id.
            // It's nullable to avoid issues with existing data.
            // The application's validation layer will enforce it as a required field for new entries.
            $table->foreignId('province_id')->after('locality_id')->nullable()->constrained('provinces');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table) {
            // Drop the foreign key constraint first, then the column.
            $table->dropForeign(['province_id']);
            $table->dropColumn('province_id');
        });
    }
};