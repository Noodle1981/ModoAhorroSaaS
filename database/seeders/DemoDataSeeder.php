<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Supply;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\ConsumptionReading;
use App\Models\EntityEquipment;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys=off;');

        // Limpiamos las tablas de datos de usuario para evitar duplicados
        User::truncate();
        Company::truncate();
        Entity::truncate();
        Supply::truncate();
        Contract::truncate();
        Invoice::truncate();
        ConsumptionReading::truncate();
        EntityEquipment::truncate();

        // 1. Creamos la Compañía y el Usuario de Demo
        $company = Company::create([
            'name' => 'Empresa Demo',
            'province_id' => 1, // Asume Buenos Aires
            'locality_id' => 1, // Asume CABA
        ]);

        $user = User::create([
            'name' => 'Usuario Demo',
            'email' => 'demo@modoahorro.com',
            'password' => Hash::make('password'),
            'company_id' => $company->id,
        ]);

        // 2. Creamos una Entidad (una casa)
        $entity = Entity::create([
            'company_id' => $company->id,
            'province_id' => 1,
            'locality_id' => 1,
            'name' => 'Casa Principal',
            'entity_type' => 'hogar',
            'address' => 'Av. Corrientes 1234',
            'city' => 'CABA',
            'postal_code' => '1043',
            'country' => 'Argentina',
            'area' => 120,
            'occupants' => 4,
        ]);

        // 3. Creamos un Suministro y un Contrato
        $supply = Supply::create([
            'entity_id' => $entity->id,
            'utility_company_id' => 1, // Asume Edenor
            'supply_id_number' => '123456789',
        ]);

        $contract = Contract::create([
            'supply_id' => $supply->id,
            'rate_id' => 1, // Asume Tarifa 1 Residencial
            'start_date' => Carbon::now()->subYear(),
        ]);

        // 4. Creamos Facturas y Consumos de ejemplo
        for ($i = 3; $i >= 1; $i--) {
            $invoiceDate = Carbon::now()->subMonths($i);
            $invoice = Invoice::create([
                'contract_id' => $contract->id,
                'invoice_number' => 'INV-00'.$i,
                'amount' => rand(8000, 15000),
                'billing_start_date' => $invoiceDate->copy()->startOfMonth(),
                'billing_end_date' => $invoiceDate->copy()->endOfMonth(),
                'payment_due_date' => $invoiceDate->copy()->addDays(15),
            ]);

            ConsumptionReading::create([
                'invoice_id' => $invoice->id,
                'reading_date' => $invoice->billing_end_date,
                'kwh_consumed' => rand(250, 400),
                'period_name' => 'total_periodo',
            ]);
        }

        // 5. Creamos un inventario de equipos para la casa
        EntityEquipment::create(['entity_id' => $entity->id, 'equipment_type_id' => 1, 'custom_name' => 'Aire del Living', 'quantity' => 1]); // Aire Acondicionado
        EntityEquipment::create(['entity_id' => $entity->id, 'equipment_type_id' => 4, 'custom_name' => 'Heladera', 'quantity' => 1]); // Refrigerador
        EntityEquipment::create(['entity_id' => $entity->id, 'equipment_type_id' => 6, 'custom_name' => 'Lavadora', 'quantity' => 1]); // Lavadora
        EntityEquipment::create(['entity_id' => $entity->id, 'equipment_type_id' => 13, 'custom_name' => 'TV 55 pulgadas', 'quantity' => 1]); // TV
        EntityEquipment::create(['entity_id' => $entity->id, 'equipment_type_id' => 16, 'custom_name' => 'Lámparas Cocina', 'quantity' => 5]); // Luz LED

        DB::statement('PRAGMA foreign_keys=on;');
    }
