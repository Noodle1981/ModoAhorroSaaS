<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // No necesitamos timestamps para esta tabla de catálogo.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
