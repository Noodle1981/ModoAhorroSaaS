<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_equipment_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_type_id')->constrained('equipment_types');
            $table->string('brand')->nullable();
            $table->string('model_name');
            $table->integer('power_watts');
            $table->string('efficiency_rating')->nullable()->comment('A+++, Inverter, etc.');
            $table->decimal('average_price', 10, 2);
            $table->text('purchase_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_equipment_catalog');
    }
};