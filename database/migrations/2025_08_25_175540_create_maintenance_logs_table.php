<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_equipment_id')->constrained('entity_equipment')->onDelete('cascade');
            $table->foreignId('maintenance_task_id')->constrained('maintenance_tasks')->onDelete('cascade');
            $table->date('performed_on_date');
            $table->string('verification_status')->default('user_reported');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};