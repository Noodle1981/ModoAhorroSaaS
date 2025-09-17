<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solar_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->unique()->constrained('entities')->onDelete('cascade');
            $table->decimal('system_capacity_kwp', 6, 3);
            $table->date('installation_date')->nullable();
            $table->string('inverter_brand')->nullable();
            $table->string('inverter_model')->nullable();
            $table->string('panel_brand')->nullable();
            $table->string('panel_model')->nullable();
            $table->integer('number_of_panels')->nullable();
            $table->string('orientation')->nullable();
            $table->integer('tilt_degrees')->nullable();
            $table->boolean('has_storage')->default(false);
            $table->decimal('storage_capacity_kwh', 6, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_installations');
    }
};