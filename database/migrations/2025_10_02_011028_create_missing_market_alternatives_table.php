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
        Schema::create('missing_market_alternatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id')->unique()->constrained('equipment_types')->onDelete('cascade');
            $table->integer('search_count')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missing_market_alternatives');
    }
};
