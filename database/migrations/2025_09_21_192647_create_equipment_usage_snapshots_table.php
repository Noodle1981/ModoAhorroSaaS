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
        Schema::create('equipment_usage_snapshots', function (Blueprint $table) {
            // --- Claves Principales ---
            $table->id();

            // Relación con el equipo del inventario al que pertenece esta "foto".
            // Si se borra el equipo del inventario, se borran sus snapshots.
            $table->foreignId('entity_equipment_id')->constrained('entity_equipment')->onDelete('cascade');

            // Relación con la factura que define este período.
            // Si se borra la factura, se borran los snapshots asociados.
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');

            // --- Datos del Período ---
            // Copiamos las fechas de la factura para facilitar las consultas.
            $table->date('start_date');
            $table->date('end_date');

            // --- "La Foto" de los Datos de Uso ---
            // Este es el valor clave que el usuario confirma o ajusta para este período.
            $table->integer('avg_daily_use_minutes');

            // Guardamos también la potencia que tenía el equipo en ese momento, por si cambia en el futuro.
            $table->integer('power_watts');

            // Y si tenía el modo standby activo.
            $table->boolean('has_standby_mode')->default(false);
            
            // --- Resultados Pre-calculados (Para Optimización) ---
            // Guardamos el resultado del cálculo para no tener que hacerlo cada vez que se genera un informe.
            $table->decimal('calculated_kwh_period', 10, 3)->comment('Consumo total (activo + standby) calculado para este equipo en este período.');

            // --- Timestamps ---
            $table->timestamps();

            // --- Índices para Búsquedas Rápidas ---
            // Evita que se pueda crear más de un snapshot para el mismo equipo y la misma factura.
            $table->unique(['entity_equipment_id', 'invoice_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_usage_snapshots');
    }
};