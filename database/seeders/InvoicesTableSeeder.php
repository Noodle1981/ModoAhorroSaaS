<?php



namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoicesTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('invoices')->insert([
            'contract_id' => 1, // Asume que existe el contrato con ID 1
            'invoice_number' => 'FAC-001-00012345',
            'invoice_date' => '2024-06-15',
            'start_date' => '2024-05-01',
            'end_date' => '2024-05-31',
            'total_energy_consumed_kwh' => 250.5,
            'total_amount' => 25000.75,
            'source' => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}