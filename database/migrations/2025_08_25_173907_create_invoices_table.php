<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade');
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            
            // Consumos
            $table->decimal('energy_consumed_p1_kwh', 10, 3)->nullable();
            $table->decimal('energy_consumed_p2_kwh', 10, 3)->nullable();
            $table->decimal('energy_consumed_p3_kwh', 10, 3)->nullable();
            $table->decimal('total_energy_consumed_kwh', 10, 3);
            
            // Costos
            $table->decimal('cost_for_energy', 10, 2)->nullable();
            $table->decimal('cost_for_power', 10, 2)->nullable();
            $table->decimal('taxes', 10, 2)->nullable();
            $table->decimal('other_charges', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2);

            // Autoconsumo (Solar)
            $table->decimal('total_energy_injected_kwh', 10, 3)->nullable();
            $table->decimal('surplus_compensation_amount', 10, 2)->nullable();
            
            // Metadatos
            $table->string('file_path')->nullable();
            $table->string('source')->default('manual');
            $table->decimal('co2_footprint_kg', 10, 3)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};