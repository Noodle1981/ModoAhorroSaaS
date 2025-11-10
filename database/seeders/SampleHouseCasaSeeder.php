<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\Entity;
use App\Models\Locality;
use App\Models\EquipmentType;
use App\Models\EntityEquipment;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;

class SampleHouseCasaSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Crear/obtener compañía de prueba
        $company = Company::updateOrCreate(
            ['tax_id' => '30-12345678-9'],
            [
                'name' => 'Casa Demo - Familia Pérez',
                'address' => 'Carlos Gardel Casa 27 B° Enoe Bravo',
                'phone' => '264-123-4567',
            ]
        );

        // 1b) Crear usuario de prueba asociado a esta compañía
        $user = User::updateOrCreate(
            ['email' => 'demo@modoahorro.com'],
            [
                'company_id' => $company->id,
                'name' => 'Usuario Demo',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 1c) Asignar plan si no tiene
        if (!$company->subscription) {
            $plan = Plan::where('name', 'Gratuito')->first();
            if ($plan) {
                Subscription::create([
                    'company_id' => $company->id,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                ]);
            }
        }

        $this->command->info("✅ Usuario demo creado: demo@modoahorro.com / password");
        $this->command->info("✅ Usuario demo creado: demo@modoahorro.com / password");

        // 2) Resolver localidad
        $locality = Locality::where('name', 'Santa Lucía')->first();
        if (!$locality) {
            $this->command->warn('No existe la Localidad "Santa Lucía". Ejecuta LocalitiesTableSeeder primero.');
            return;
        }

        // 3) Crear/actualizar la Entidad
        $details = [
            'rooms' => array_map(function ($idx, $name) {
                return ['id' => $idx + 1, 'name' => $name];
            }, array_keys($rooms = [
                'cocina / comedor', 'Living', 'Baño', 'Habitación Mamá', 'Habitación Papá',
                'Habitación Omar', 'Hall', 'Fondo', 'Vereda', 'Garage', 'Lavadero'
            ]), $rooms),
            'occupants' => 4,
            'area_m2' => 450,
            'mixed_use' => false,
        ];

        $entity = Entity::updateOrCreate(
            [
                'company_id' => $company->id,
                'name' => 'Casa',
            ],
            [
                'locality_id' => $locality->id,
                'type' => 'hogar',
                'address_street' => 'Carlos Gardel Casa 27 B° Enoe Bravo',
                'address_postal_code' => '5411',
                'details' => $details,
            ]
        );

        // 4) Helper para obtener o crear tipos
        $type = function (string $name, int $categoryId = 8, int $power = null, int $minutes = null) {
            $et = EquipmentType::where('name', $name)->first();
            if ($et) return $et;
            // Crear tipo ad-hoc si no existe
            return EquipmentType::create([
                'category_id' => $categoryId,
                'name' => $name,
                'is_portable' => true,
                'default_power_watts' => $power ?? 100,
                'default_avg_daily_use_minutes' => $minutes ?? 10,
                'standby_power_watts' => 0,
            ]);
        };

        $findType = fn(string $name) => EquipmentType::where('name', $name)->first();

        // 4) Definición de equipos por ambiente (minutos override, potencia override y tipo_de_proceso)
        $equipmentByRoom = [
            'cocina / comedor' => [
                ['name' => 'Aire Acondicionado Split 3000 frigorías', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Motor'],
                ['name' => 'Ventilador de Techo', 'minutes' => 300, 'qty' => 1, 'tipo_de_proceso' => 'Motor'], // 5 hs
                ['name' => 'Microondas 20L', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Magnetrón'], // ~1h (0.33 en Python = 20min, ajustado a 60 para facilitar)
                ['name' => 'Lámpara LED 12W', 'minutes' => 180, 'qty' => 3, 'tipo_de_proceso' => 'Electroluminiscencia'], // 3 luminarias 3 hs
                ['name' => 'Tubo LED 18W', 'minutes' => 600, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'], // 10 hs
            ],
            'Living' => [
                ['name' => 'TV LED 32"', 'minutes' => 480, 'qty' => 1, 'tipo_de_proceso' => 'Electrónico'], // 8 hs
                ['name' => 'Lámpara LED 12W', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Ventilador de Techo', 'minutes' => 180, 'qty' => 1, 'tipo_de_proceso' => 'Motor'], // 3 hs
                ['name' => 'Router WiFi', 'minutes' => 1440, 'qty' => 1, 'tipo_de_proceso' => 'Electrónico'], // 24 hs
            ],
            'Baño' => [
                ['name' => 'Lámpara LED 12W', 'minutes' => 180, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Afeitadora Eléctrica', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Motor', 'fallback' => ['category' => 8, 'power' => 10]],
                ['name' => 'Secador de Pelo', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Motor & Resistencia'],
            ],
            'Habitación Mamá' => [
                ['name' => 'Lámpara LED 12W', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Lámpara de Escritorio LED', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Ventilador de Techo', 'minutes' => 300, 'qty' => 1, 'tipo_de_proceso' => 'Motor'],
            ],
            'Habitación Papá' => [
                ['name' => 'Lámpara LED 12W', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Lámpara de Escritorio LED', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Ventilador de Techo', 'minutes' => 300, 'qty' => 1, 'tipo_de_proceso' => 'Motor'],
                ['name' => 'TV LED 43"', 'minutes' => 480, 'qty' => 1, 'tipo_de_proceso' => 'Electrónico'],
            ],
            'Habitación Omar' => [
                ['name' => 'PC de Escritorio (Gaming)', 'minutes' => 180, 'qty' => 1, 'tipo_de_proceso' => 'Electrónico'],
                ['name' => 'Monitor LED 27"', 'minutes' => 180, 'qty' => 2, 'tipo_de_proceso' => 'Electrónico'],
                ['name' => 'Tira LED 5 metros', 'minutes' => 180, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Aire Acondicionado Portátil', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Motor'],
                ['name' => 'Lámpara LED 12W', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Lámpara de Escritorio LED', 'minutes' => 240, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
            ],
            'Hall' => [
                ['name' => 'Lámpara LED 12W', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
            ],
            'Fondo' => [
                ['name' => 'Reflector LED 50W', 'minutes' => 60, 'qty' => 1, 'power_override' => 15, 'tipo_de_proceso' => 'Electroluminiscencia'],
            ],
            'Vereda' => [
                ['name' => 'Lámpara Halógena 50W', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
            ],
            'Garage' => [
                ['name' => 'Lámpara LED 12W', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Electroluminiscencia'],
                ['name' => 'Cortadora de Fiambre', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Motor', 'fallback' => ['category' => 4, 'power' => 250]],
                ['name' => 'Heladera con Freezer (Cíclica)', 'minutes' => 1440, 'qty' => 1, 'tipo_de_proceso' => 'Motor'],
            ],
            'Lavadero' => [
                ['name' => 'Lavarropas Automático 8kg', 'minutes' => 60, 'qty' => 1, 'tipo_de_proceso' => 'Motor & Resistencia', 'frequency' => [
                    'is_daily_use' => false,
                    'usage_days_per_week' => 2,
                    'usage_weekdays' => [3,6],
                    'minutes_per_session' => 120,
                ]],
            ],
            'Portátiles' => [
                ['name' => 'Cargador de Celular', 'minutes' => 90, 'qty' => 3, 'tipo_de_proceso' => 'Electrónico'],
                ['name' => 'Notebook 14"', 'minutes' => 300, 'qty' => 1, 'tipo_de_proceso' => 'Electrónico'],
            ],
        ];

        // 5) Limpiar inventario previo de esta entidad para idempotencia
        EntityEquipment::where('entity_id', $entity->id)->delete();

        // 6) Crear equipos
        foreach ($equipmentByRoom as $room => $items) {
            foreach ($items as $item) {
                $et = $findType($item['name']);
                if (!$et && isset($item['fallback'])) {
                    $et = $type($item['name'], $item['fallback']['category'], $item['fallback']['power'], $item['minutes'] ?? null);
                }
                if (!$et) {
                    $this->command->warn('No se encontró tipo: ' . $item['name'] . ' - se omite.');
                    continue;
                }

                // Determinar si este equipo debe tener standby habilitado por defecto (solo TVs en este ejemplo)
                $name = $et->name;
                $isTv = str_contains(strtolower($name), 'tv');

                $payload = [
                    'entity_id' => $entity->id,
                    'equipment_type_id' => $et->id,
                    'quantity' => $item['qty'] ?? 1,
                    'custom_name' => null,
                    'power_watts_override' => $item['power_override'] ?? null,
                    'avg_daily_use_minutes_override' => $item['minutes'] ?? null,
                    'location' => $room,
                    'tipo_de_proceso' => $item['tipo_de_proceso'] ?? null,
                    // Solo TV en standby por defecto; el resto false
                    'has_standby_mode' => $isTv ? true : false,
                ];

                // Si el item tiene configuración de frecuencia, agregarla
                if (isset($item['frequency'])) {
                    $freq = $item['frequency'];
                    $payload['is_daily_use'] = $freq['is_daily_use'];
                    $payload['usage_days_per_week'] = $freq['usage_days_per_week'];
                    $payload['usage_weekdays'] = $freq['usage_weekdays'];
                    $payload['minutes_per_session'] = $freq['minutes_per_session'];
                    // Derivar promedio diario si no se definió explícitamente
                    if ($payload['avg_daily_use_minutes_override'] === null && !$payload['is_daily_use']) {
                        $derived = ($payload['minutes_per_session'] * $payload['usage_days_per_week']) / 7;
                        $payload['avg_daily_use_minutes_override'] = (int)round($derived);
                    }
                }

                EntityEquipment::create($payload);
            }
        }

        $this->command->info('✅ Seeder de ejemplo "Casa" ejecutado. Entidad ID: ' . $entity->id);
    }
}
