<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MaintenanceLogsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('maintenance_logs')->insert([
            'entity_equipment_id' => 1, // "Aire del Living"
            'maintenance_task_id' => 1, // "Limpieza de Filtros"
            'performed_on_date' => Carbon::now()->subDays(15),
            'verification_status' => 'user_reported',
            'notes' => 'Filtros estaban bastante sucios.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}