<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solar_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('type', 20); // panel | heater
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('status', 20)->default('new'); // new|contacted|closed
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solar_leads');
    }
};
