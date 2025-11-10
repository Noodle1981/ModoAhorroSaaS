<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoicesTableSeeder extends Seeder
{
    public function run(): void
    {
        // Factura período 1: 2025-01-15 a 2025-03-20
        // Consumo: 623 kWh, Monto: $95,775.08, Tarifa: $153.73/kWh
        DB::table('invoices')->insert([
            'contract_id' => 1, // Asume que existe el contrato con ID 1
            'invoice_number' => 'FAC-2025-001',
            'invoice_date' => '2025-03-25',
            'start_date' => '2025-01-15',
            'end_date' => '2025-03-20',
            
            // Consumos (solo total, sin diferenciar períodos)
            'energy_consumed_p1_kwh' => null,
            'energy_consumed_p2_kwh' => null,
            'energy_consumed_p3_kwh' => null,
            'total_energy_consumed_kwh' => 623.000,
            
            // Costos (usando tarifa promedio para calcular)
            'cost_for_energy' => 95775.08, // Valor total de la factura
            'cost_for_power' => null,
            'taxes' => null,
            'other_charges' => null,
            'total_amount' => 95775.08,
            
            // Autoconsumo (no aplicable)
            'total_energy_injected_kwh' => null,
            'surplus_compensation_amount' => null,
            
            // Metadatos
            'file_path' => null,
            'source' => 'manual',
            'co2_footprint_kg' => null,
            
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}