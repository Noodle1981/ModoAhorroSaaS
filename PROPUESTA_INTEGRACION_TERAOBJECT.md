# 🚀 ModoAhorroSaaS + Teraobject IoT - Propuesta de Integración

## 📊 RESUMEN EJECUTIVO

**ModoAhorroSaaS** es una plataforma SaaS de gestión energética inteligente que analiza el consumo eléctrico comparando facturas reales con inventarios de equipos. La **integración con Teraobject IoT** transformaría el MVP actual en una solución de **monitoreo y optimización energética en tiempo real** con capacidades de Machine Learning y Gemelos Digitales.

---

## 🎯 PROPUESTA DE VALOR CONJUNTA

### Para el Usuario Final:
✅ **Monitoreo en tiempo real** del consumo con hardware Teraobject
✅ **Análisis predictivo** basado en patrones históricos + clima
✅ **Recomendaciones inteligentes** de productos con ROI calculado
✅ **Marketplace integrado** para comprar equipos eficientes
✅ **Gemelo Digital** de su instalación eléctrica

### Para Teraobject:
✅ **Plataforma SaaS** que da valor agregado a sus medidores
✅ **Canal de ventas B2C** para sus dispositivos IoT
✅ **Datos de consumo** para entrenar modelos de ML
✅ **Marketplace** con comisión por venta de equipos
✅ **Caso de uso real** para demostrar capacidades de gemelos digitales

---

## 🔌 ARQUITECTURA DE INTEGRACIÓN

```
┌─────────────────────────────────────────────────────────────┐
│                    ModoAhorroSaaS (Backend)                  │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Laravel    │  │   Services   │  │   Database   │      │
│  │  Controllers │  │   Analysis   │  │  PostgreSQL  │      │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘      │
│         │                 │                  │               │
└─────────┼─────────────────┼──────────────────┼───────────────┘
          │                 │                  │
          ↓                 ↓                  ↓
┌─────────────────────────────────────────────────────────────┐
│              🔗 CAPA DE INTEGRACIÓN (APIs)                   │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │ Teraobject   │  │   Weather    │  │  Marketplace │      │
│  │ IoT Gateway  │  │     API      │  │     API      │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
└─────────────────────────────────────────────────────────────┘
          ↓                 ↓                  ↓
┌──────────────┐  ┌──────────────┐  ┌──────────────┐
│ Medidores    │  │   AEMET /    │  │ Mercado Libre│
│ Teraobject   │  │ OpenWeather  │  │   Amazon     │
│  (Tiempo     │  │  (Clima)     │  │  (Productos) │
│   Real)      │  │              │  │              │
└──────────────┘  └──────────────┘  └──────────────┘
```

---

## 🎨 NUEVAS FUNCIONALIDADES CON INTEGRACIÓN

### 1. 📡 **MEDICIÓN EN TIEMPO REAL** (Teraobject IoT)

#### ¿Qué tenemos AHORA?
- ❌ Solo datos de facturas mensuales/bimensuales
- ❌ Usuario estima minutos de uso manualmente
- ❌ No hay visibilidad del consumo actual

#### ¿Qué tendríamos con TERAOBJECT?
```javascript
// API Teraobject - Lecturas cada 15 minutos
GET https://api.teraobject.com/v1/devices/{device_id}/readings
Response:
{
  "timestamp": "2025-11-05T14:15:00Z",
  "power_w": 3450.5,        // Potencia instantánea
  "energy_kwh": 52.3,       // Acumulado del día
  "voltage_v": 230.2,
  "current_a": 15.0,
  "power_factor": 0.95,
  "temperature_c": 24.5     // Del sensor del medidor
}
```

#### Implementación en ModoAhorroSaaS:
```php
// app/Services/TeraobjectService.php
class TeraobjectService {
    public function getRealTimeReading(Supply $supply): array
    {
        $deviceId = $supply->teraobject_device_id;
        $response = Http::withToken(config('services.teraobject.api_key'))
            ->get("https://api.teraobject.com/v1/devices/{$deviceId}/readings/latest");
        
        return $response->json();
    }
    
    public function syncDailyReadings(Supply $supply): void
    {
        // Sincronizar últimas 24h de lecturas cada 15 min
        $readings = $this->getReadingsRange($supply, now()->subDay(), now());
        
        foreach ($readings as $reading) {
            ConsumptionReading::updateOrCreate([
                'supply_id' => $supply->id,
                'reading_timestamp' => $reading['timestamp'],
            ], [
                'consumed_kwh' => $reading['energy_kwh'],
                'source' => 'teraobject_iot',
                'metadata' => json_encode([
                    'power_w' => $reading['power_w'],
                    'voltage_v' => $reading['voltage_v'],
                    'current_a' => $reading['current_a'],
                ])
            ]);
        }
    }
}
```

#### Dashboard en Tiempo Real:
```blade
{{-- Vista de monitoreo live con Alpine.js --}}
<div x-data="liveMonitor({{ $supply->id }})" x-init="startPolling()">
    <div class="grid grid-cols-4 gap-4">
        <!-- Potencia Instantánea -->
        <div class="bg-yellow-100 p-4 rounded-lg">
            <h3 class="text-sm text-gray-600">Potencia Actual</h3>
            <p class="text-3xl font-bold text-yellow-600" x-text="currentPower + ' W'"></p>
        </div>
        
        <!-- Consumo del Día -->
        <div class="bg-blue-100 p-4 rounded-lg">
            <h3 class="text-sm text-gray-600">Consumo Hoy</h3>
            <p class="text-3xl font-bold text-blue-600" x-text="todayEnergy + ' kWh'"></p>
        </div>
        
        <!-- Gráfico Tiempo Real -->
        <div class="col-span-2">
            <canvas id="realtimeChart"></canvas>
        </div>
    </div>
</div>

<script>
function liveMonitor(supplyId) {
    return {
        currentPower: 0,
        todayEnergy: 0,
        
        async startPolling() {
            setInterval(async () => {
                const data = await fetch(`/api/supplies/${supplyId}/live-reading`)
                    .then(r => r.json());
                this.currentPower = data.power_w;
                this.todayEnergy = data.energy_kwh;
                updateChart(data); // Chart.js
            }, 15000); // Cada 15 segundos
        }
    }
}
</script>
```

---

### 2. 🌦️ **CORRELACIÓN CON CLIMA** (Weather API)

#### Integración con AEMET (España) o OpenWeatherMap

```php
// app/Services/WeatherService.php
class WeatherService {
    public function getWeatherForEntity(Entity $entity): array
    {
        $locality = $entity->locality;
        
        // AEMET API o OpenWeatherMap
        $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
            'lat' => $locality->latitude,
            'lon' => $locality->longitude,
            'appid' => config('services.weather.api_key'),
            'units' => 'metric'
        ]);
        
        return $response->json();
    }
    
    public function logDailyWeather(Entity $entity): void
    {
        $weather = $this->getWeatherForEntity($entity);
        
        DailyWeatherLog::create([
            'entity_id' => $entity->id,
            'date' => now()->toDateString(),
            'temperature_avg_c' => $weather['main']['temp'],
            'temperature_min_c' => $weather['main']['temp_min'],
            'temperature_max_c' => $weather['main']['temp_max'],
            'humidity_percent' => $weather['main']['humidity'],
            'weather_condition' => $weather['weather'][0]['main'], // Clear, Rain, etc
            'wind_speed_kmh' => $weather['wind']['speed'] * 3.6,
        ]);
    }
}

// app/Services/CorrelationAnalysisService.php
class CorrelationAnalysisService {
    public function analyzeTemperatureVsConsumption(Entity $entity, int $days = 30): array
    {
        $data = DB::table('daily_weather_logs')
            ->join('consumption_readings', function($join) {
                $join->on('daily_weather_logs.entity_id', '=', 'consumption_readings.entity_id')
                     ->whereRaw('DATE(consumption_readings.reading_timestamp) = daily_weather_logs.date');
            })
            ->where('daily_weather_logs.entity_id', $entity->id)
            ->where('daily_weather_logs.date', '>=', now()->subDays($days))
            ->select([
                'daily_weather_logs.date',
                'daily_weather_logs.temperature_avg_c',
                DB::raw('SUM(consumption_readings.consumed_kwh) as daily_consumption')
            ])
            ->groupBy('daily_weather_logs.date', 'daily_weather_logs.temperature_avg_c')
            ->get();
        
        // Calcular correlación de Pearson
        $correlation = $this->calculatePearsonCorrelation(
            $data->pluck('temperature_avg_c'),
            $data->pluck('daily_consumption')
        );
        
        return [
            'correlation_coefficient' => $correlation,
            'interpretation' => $this->interpretCorrelation($correlation),
            'chart_data' => $data,
            'recommendations' => $this->generateWeatherRecommendations($correlation)
        ];
    }
}
```

#### Dashboard de Correlación Clima:
```blade
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-4">
        <i class="fas fa-cloud-sun text-blue-500 mr-2"></i>
        Impacto del Clima en tu Consumo
    </h3>
    
    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <p class="text-sm text-gray-600">Correlación Temp vs Consumo</p>
            <p class="text-2xl font-bold {{ $correlation > 0.7 ? 'text-red-600' : 'text-green-600' }}">
                {{ number_format($correlation * 100, 0) }}%
            </p>
            <p class="text-xs text-gray-500">{{ $interpretation }}</p>
        </div>
        <div>
            <canvas id="tempVsConsumption"></canvas>
        </div>
    </div>
    
    @if($correlation > 0.7)
        <div class="bg-orange-50 border-l-4 border-orange-400 p-3 rounded">
            <p class="text-sm text-orange-800">
                <strong>⚠️ Alta correlación detectada:</strong> 
                Tu consumo aumenta significativamente con temperaturas altas. 
                Considera mejorar el aislamiento o actualizar tu sistema de climatización.
            </p>
        </div>
    @endif
</div>
```

---

### 3. 🤖 **RECOMENDACIONES CON MARKETPLACE** (API de Productos)

#### Integración con Mercado Libre / Amazon

```php
// app/Services/MarketplaceService.php
class MarketplaceService {
    public function searchProducts(string $query, string $category): array
    {
        // Mercado Libre API
        $response = Http::get('https://api.mercadolibre.com/sites/MLM/search', [
            'q' => $query,
            'category' => $category,
            'sort' => 'price_asc',
            'limit' => 10
        ]);
        
        return collect($response->json()['results'])->map(function($product) {
            return [
                'id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'currency' => $product['currency_id'],
                'thumbnail' => $product['thumbnail'],
                'permalink' => $product['permalink'],
                'seller' => $product['seller']['nickname'] ?? 'N/A',
                'condition' => $product['condition'], // new, used
                'free_shipping' => $product['shipping']['free_shipping'] ?? false,
            ];
        })->toArray();
    }
    
    public function findReplacementProducts(EntityEquipment $equipment): array
    {
        $type = $equipment->equipmentType;
        
        // Buscar productos más eficientes
        $query = "{$type->name} bajo consumo eficiente";
        $products = $this->searchProducts($query, $this->mapToMercadoLibreCategory($type));
        
        // Filtrar por potencia menor a la actual
        $currentPower = $equipment->power_watts_override ?? $type->default_power_watts;
        
        return array_filter($products, function($product) use ($currentPower) {
            // Extraer potencia del título (regex: "200W", "1000 watts")
            preg_match('/(\d+)\s?W/i', $product['title'], $matches);
            if (isset($matches[1])) {
                return (int)$matches[1] < $currentPower;
            }
            return false;
        });
    }
}

// app/Services/RecommendationEngineService.php
class RecommendationEngineService {
    public function generateSmartRecommendations(Entity $entity): array
    {
        $recommendations = [];
        
        // 1. Equipos con mayor consumo
        $topConsumers = $entity->equipments()
            ->with('equipmentType')
            ->get()
            ->map(function($eq) {
                $power = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
                $minutes = $eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes;
                $monthlyKwh = ($power / 1000) * ($minutes / 60) * 30;
                $eq->monthly_kwh = $monthlyKwh;
                return $eq;
            })
            ->sortByDesc('monthly_kwh')
            ->take(5);
        
        foreach ($topConsumers as $equipment) {
            // Buscar productos en el mercado
            $products = app(MarketplaceService::class)->findReplacementProducts($equipment);
            
            if (!empty($products)) {
                $bestProduct = collect($products)->sortBy('price')->first();
                
                // Calcular ahorro
                preg_match('/(\d+)\s?W/i', $bestProduct['title'], $matches);
                $newPower = $matches[1] ?? 0;
                
                if ($newPower > 0) {
                    $currentPower = $equipment->power_watts_override ?? $equipment->equipmentType->default_power_watts;
                    $minutes = $equipment->avg_daily_use_minutes_override ?? $equipment->equipmentType->default_avg_daily_use_minutes;
                    
                    $currentMonthlyKwh = ($currentPower / 1000) * ($minutes / 60) * 30;
                    $newMonthlyKwh = ($newPower / 1000) * ($minutes / 60) * 30;
                    $savingsKwh = $currentMonthlyKwh - $newMonthlyKwh;
                    
                    // Asumir €0.15/kWh
                    $savingsPerMonth = $savingsKwh * 0.15;
                    $paybackMonths = $bestProduct['price'] / $savingsPerMonth;
                    
                    $recommendations[] = [
                        'type' => 'replacement',
                        'priority' => $this->calculatePriority($savingsPerMonth, $paybackMonths),
                        'current_equipment' => $equipment,
                        'suggested_product' => $bestProduct,
                        'savings' => [
                            'kwh_per_month' => round($savingsKwh, 2),
                            'euros_per_month' => round($savingsPerMonth, 2),
                            'euros_per_year' => round($savingsPerMonth * 12, 2),
                        ],
                        'investment' => [
                            'price' => $bestProduct['price'],
                            'payback_months' => round($paybackMonths, 1),
                        ]
                    ];
                }
            }
        }
        
        // Ordenar por prioridad (mayor ahorro, menor payback)
        return collect($recommendations)->sortByDesc('priority')->take(10)->values()->toArray();
    }
    
    private function calculatePriority(float $savingsPerMonth, float $paybackMonths): float
    {
        // Prioridad alta: mucho ahorro y payback < 24 meses
        return ($savingsPerMonth * 10) / max($paybackMonths, 1);
    }
}
```

#### Vista de Recomendaciones con Marketplace:
```blade
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-bold mb-4">
        <i class="fas fa-shopping-cart text-green-500 mr-2"></i>
        Recomendaciones Inteligentes con ROI
    </h3>
    
    <div class="space-y-4">
        @foreach($recommendations as $rec)
            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition">
                <div class="flex gap-4">
                    <!-- Imagen del producto -->
                    <img src="{{ $rec['suggested_product']['thumbnail'] }}" 
                         class="w-24 h-24 object-cover rounded" 
                         alt="Producto">
                    
                    <div class="flex-1">
                        <!-- Equipo actual -->
                        <div class="mb-2">
                            <span class="text-sm text-gray-500">Reemplazar:</span>
                            <p class="font-semibold text-gray-800">
                                {{ $rec['current_equipment']->custom_name ?? $rec['current_equipment']->equipmentType->name }}
                            </p>
                        </div>
                        
                        <!-- Producto sugerido -->
                        <div class="mb-3">
                            <span class="text-sm text-gray-500">Por:</span>
                            <p class="font-semibold text-blue-600">
                                {{ $rec['suggested_product']['title'] }}
                            </p>
                            <p class="text-lg font-bold text-green-600">
                                ${{ number_format($rec['suggested_product']['price'], 2) }}
                                @if($rec['suggested_product']['free_shipping'])
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">Envío gratis</span>
                                @endif
                            </p>
                        </div>
                        
                        <!-- Ahorro -->
                        <div class="grid grid-cols-3 gap-3 text-sm">
                            <div class="bg-green-50 p-2 rounded">
                                <p class="text-xs text-gray-600">Ahorro/mes</p>
                                <p class="font-bold text-green-600">€{{ $rec['savings']['euros_per_month'] }}</p>
                            </div>
                            <div class="bg-blue-50 p-2 rounded">
                                <p class="text-xs text-gray-600">Ahorro/año</p>
                                <p class="font-bold text-blue-600">€{{ $rec['savings']['euros_per_year'] }}</p>
                            </div>
                            <div class="bg-purple-50 p-2 rounded">
                                <p class="text-xs text-gray-600">ROI</p>
                                <p class="font-bold text-purple-600">{{ $rec['investment']['payback_months'] }} meses</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Acción -->
                    <div class="flex flex-col gap-2">
                        <a href="{{ $rec['suggested_product']['permalink'] }}" 
                           target="_blank"
                           class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                            <i class="fas fa-external-link-alt mr-2"></i> Ver Producto
                        </a>
                        <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition">
                            <i class="fas fa-cart-plus mr-2"></i> Agregar al Carrito
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
```

---

### 4. 🏭 **GEMELO DIGITAL** (Digital Twin Concept)

#### ⚠️ IMPORTANTE: Versión Simplificada para MVP

**Gemelo Digital NO significa** simulación física compleja, renderizado 3D ni IA avanzada.

**Gemelo Digital SÍ significa**: Tomar los datos que **ya tienes** y presentarlos como una "réplica virtual":
- 📊 Vista consolidada de equipos + consumo + clima
- ⚡ Comparar consumo real vs estimado (ya lo haces)
- 🔮 Simulador = cambiar valores y recalcular (solo matemáticas)
- 🎯 "Optimizaciones" = mostrar las recomendaciones que ya generas

**En resumen**: Es más **marketing term** que tecnología compleja. Básicamente es tu dashboard actual + un simulador simple.

#### ¿Qué es un Gemelo Digital en este contexto?

Un **gemelo digital** es una **réplica virtual en tiempo real** de la instalación eléctrica del usuario, que:
- 📊 Refleja el estado actual de todos los equipos
- ⚡ Sincroniza consumo en tiempo real con medidores Teraobject
- 🔮 Permite simular cambios antes de implementarlos
- 🎯 Optimiza configuraciones automáticamente

#### Implementación del Gemelo Digital (VERSIÓN SIMPLE):

```php
// app/Services/DigitalTwinService.php
class DigitalTwinService {
    /**
     * 🟢 VERSIÓN SIMPLE: Solo consolida datos que ya tienes
     */
    public function createDigitalTwin(Entity $entity): array
    {
        // Reutilizar servicios existentes
        $inventoryService = app(InventoryAnalysisService::class);
        $replacementService = app(ReplacementAnalysisService::class);
        
        return [
            'entity_id' => $entity->id,
            'name' => $entity->name,
            
            // Ya lo tienes en el controller
            'equipments' => $entity->equipments()->with('equipmentType')->get(),
            
            // Ya lo calculas en InventoryAnalysisService
            'consumption_profile' => [
                'estimated_annual_kwh' => $inventoryService->getAnnualEnergyProfile($entity)->sum('consumo_kwh_total_periodo'),
                'real_consumption' => $this->getLastInvoiceConsumption($entity),
            ],
            
            // Ya lo calculas en ReplacementAnalysisService
            'optimization_suggestions' => $replacementService->findAllOpportunities($entity),
            
            // Timestamp para decir "actualizado ahora"
            'last_updated' => now(),
        ];
    }
    
    /**
     * 🟢 VERSIÓN SIMPLE del simulador: Solo cambias valores y recalculas
     */
    public function simulate(Entity $entity, array $changes): array
    {
        $original = $this->createDigitalTwin($entity);
        
        // Clonar equipos para simular cambio
        $simulatedEquipments = $entity->equipments()->get()->map(function($eq) use ($changes) {
            // Si hay cambio para este equipo, aplicarlo
            if (isset($changes['replace_equipment_' . $eq->id])) {
                $newPower = $changes['replace_equipment_' . $eq->id]['new_power_watts'];
                $eq->power_watts_override = $newPower;
            }
            return $eq;
        });
        
        // Recalcular consumo con nuevos valores
        $newConsumption = $simulatedEquipments->sum(function($eq) {
            $power = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
            $minutes = $eq->avg_daily_use_minutes_override ?? $eq->equipmentType->default_avg_daily_use_minutes;
            return ($power / 1000) * ($minutes / 60) * 30 * 12; // Anual
        });
        
        $savings = $original['consumption_profile']['estimated_annual_kwh'] - $newConsumption;
        $savingsEuros = $savings * 0.15; // €0.15/kWh
        
        return [
            'original_consumption_kwh' => $original['consumption_profile']['estimated_annual_kwh'],
            'simulated_consumption_kwh' => $newConsumption,
            'savings_kwh' => $savings,
            'savings_euros_year' => $savingsEuros,
        ];
    }
    
    private function getLastInvoiceConsumption(Entity $entity): float
    {
        $supplyIds = $entity->supplies->pluck('id');
        
        $lastInvoice = \App\Models\Invoice::whereHas('contract', function($q) use ($supplyIds) {
            $q->whereIn('supply_id', $supplyIds);
        })->orderBy('end_date', 'desc')->first();
        
        return $lastInvoice?->total_energy_consumed_kwh ?? 0;
    }
}

// 🔴 OPCIONAL (solo si quieres impresionar): Métodos avanzados
// Puedes implementarlos DESPUÉS si el CEO se interesa

/*
private function buildElectricalModel(Entity $entity): array {...}
private function buildEquipmentsModel(Entity $entity): array {...}
private function buildConsumptionProfile(Entity $entity): array {...}
private function getRealTimeState(Entity $entity): array {...}
*/
```

#### 💡 Lo que realmente importa para el CEO:

1. **Dashboard bonito** ✅ Ya lo tienes
2. **Inventario de equipos** ✅ Ya lo tienes
3. **Comparación consumo real vs estimado** ✅ Ya lo calculas
4. **Recomendaciones con ROI** ✅ ReplacementAnalysisService
5. **Llamarlo "Gemelo Digital"** ← Solo cambiar el nombre

**El 90% del trabajo ya está hecho**. Solo falta empaquetar los datos existentes con un nombre fancy.

#### Dashboard del Gemelo Digital:
```blade
<div class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg shadow-lg p-6 border-2 border-purple-300">
    <h2 class="text-2xl font-bold text-purple-900 mb-4">
        <i class="fas fa-project-diagram mr-2"></i> Gemelo Digital de {{ $entity->name }}
    </h2>
    
    <div class="grid grid-cols-3 gap-4 mb-6">
        <!-- Estado Actual -->
        <div class="bg-white rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Estado en Tiempo Real</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-xs">Potencia:</span>
                    <span class="font-bold text-yellow-600">{{ $twin['real_time_state']['current_power_w'] }} W</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs">Tensión:</span>
                    <span class="font-bold">{{ $twin['real_time_state']['voltage_v'] }} V</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs">Hoy:</span>
                    <span class="font-bold text-blue-600">{{ $twin['real_time_state']['today_consumption_kwh'] }} kWh</span>
                </div>
            </div>
        </div>
        
        <!-- Equipos Activos -->
        <div class="bg-white rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Equipos ({{ count($twin['equipments']) }})</h3>
            <div class="space-y-1">
                @foreach(array_slice($twin['equipments'], 0, 5) as $eq)
                    <div class="flex items-center justify-between text-xs">
                        <span class="truncate">{{ $eq['name'] }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-medium
                            {{ $eq['estimated_state'] == 'on' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ strtoupper($eq['estimated_state']) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Clima -->
        <div class="bg-white rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-600 mb-2">Contexto Climático</h3>
            <div class="text-center">
                <p class="text-4xl font-bold text-orange-500">{{ $twin['climate_context']['current_temperature_c'] }}°C</p>
                <p class="text-xs text-gray-500 mt-1">{{ $twin['climate_context']['condition'] }}</p>
                <p class="text-xs text-gray-500">Humedad: {{ $twin['climate_context']['humidity_percent'] }}%</p>
            </div>
        </div>
    </div>
    
    <!-- Visualización 3D (Placeholder para Three.js) -->
    <div class="bg-white rounded-lg p-4 mb-4">
        <h3 class="text-sm font-semibold text-gray-600 mb-2">Visualización de Instalación</h3>
        <div id="digital-twin-3d" class="w-full h-64 bg-gray-100 rounded flex items-center justify-center">
            <p class="text-gray-400">Modelo 3D de la instalación (Three.js)</p>
        </div>
    </div>
    
    <!-- Simulador -->
    <div class="bg-white rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-600 mb-3">
            <i class="fas fa-flask mr-1"></i> Simulador: ¿Qué pasaría si...?
        </h3>
        <div class="space-y-2">
            <button class="w-full text-left px-3 py-2 bg-blue-50 hover:bg-blue-100 rounded text-sm transition">
                <i class="fas fa-exchange-alt mr-2"></i> Reemplazar heladera por modelo eficiente
                <span class="float-right text-green-600 font-semibold">-€45/mes</span>
            </button>
            <button class="w-full text-left px-3 py-2 bg-blue-50 hover:bg-blue-100 rounded text-sm transition">
                <i class="fas fa-sun mr-2"></i> Agregar paneles solares 5 kWp
                <span class="float-right text-green-600 font-semibold">-€120/mes</span>
            </button>
            <button class="w-full text-left px-3 py-2 bg-blue-50 hover:bg-blue-100 rounded text-sm transition">
                <i class="fas fa-clock mr-2"></i> Mover consumo a tarifa valle (22h-12h)
                <span class="float-right text-green-600 font-semibold">-€30/mes</span>
            </button>
        </div>
    </div>
</div>
```

---

## 💰 MODELO DE NEGOCIO CONJUNTO

### Flujos de Ingreso:

#### 1. **Venta de Hardware** (Teraobject)
- 💵 **Precio**: €150-300 por medidor inteligente
- 🎁 **Bundle**: "Medidor + 1 año de ModoAhorroSaaS Premium"
- 📊 **Proyección**: 1,000 dispositivos/mes = €150k-300k

#### 2. **Suscripciones SaaS** (ModoAhorroSaaS)
- 🏠 **Plan Hogar**: €9.99/mes (sin medidor, solo facturas)
- ⚡ **Plan Smart**: €19.99/mes (con medidor Teraobject)
- 🏢 **Plan Profesional**: €49.99/mes (múltiples entidades + análisis avanzado)
- 📊 **Proyección**: 5,000 usuarios activos = €100k-150k/mes

#### 3. **Comisiones de Marketplace**
- 🛒 **Comisión**: 5-10% por venta de producto recomendado
- 💡 **Ejemplo**: Usuario compra heladera de €800 → €40-80 de comisión
- 📊 **Proyección**: 100 ventas/mes = €4k-8k adicionales

#### 4. **Datos y Analytics** (B2B)
- 📈 **Venta de insights agregados** (anonimizados) a:
  - Distribuidoras eléctricas
  - Fabricantes de equipos
  - Gobiernos (políticas energéticas)
- 💵 **Modelo**: €5k-20k por reporte

---

## 🎯 HOJA DE RUTA DE IMPLEMENTACIÓN

### **FASE 1: MVP Mejorado** (4 semanas) ✅ YA TENEMOS ESTO
- [x] Dashboard general y por entidad
- [x] Gestión de equipos con inventario
- [x] Ajuste de snapshots con Alpine.js
- [x] Análisis de consumo vs inventario
- [ ] Testing básico

### **FASE 2: Integración Teraobject** (6 semanas)
- [ ] API client para Teraobject IoT Gateway
- [ ] Sincronización automática de lecturas cada 15 min
- [ ] Dashboard en tiempo real con Chart.js
- [ ] Alertas de consumo anormal
- [ ] Almacenamiento eficiente de time-series data

### **FASE 3: Clima + Correlación** (3 semanas)
- [ ] Integración Weather API (AEMET/OpenWeather)
- [ ] DailyWeatherLog automático vía cron job
- [ ] Análisis de correlación temperatura vs consumo
- [ ] Gráficos de dispersión y líneas de tendencia
- [ ] Predicción de consumo basada en pronóstico climático

### **FASE 4: Marketplace + Recomendaciones** (4 semanas)
- [ ] Integración Mercado Libre API
- [ ] Motor de recomendaciones con cálculo de ROI
- [ ] Vista de productos con comparación
- [ ] Sistema de "Carrito de Compra" para ahorro
- [ ] Tracking de conversiones y comisiones

### **FASE 5: Gemelo Digital** (6 semanas)
- [ ] DigitalTwinService con modelo completo
- [ ] Estimación de estado de equipos en tiempo real
- [ ] Simulador "¿Qué pasaría si...?"
- [ ] Visualización 3D con Three.js (opcional)
- [ ] Optimizaciones automáticas por IA

### **FASE 6: Machine Learning** (8 semanas)
- [ ] Modelo de predicción de consumo (LSTM)
- [ ] Detección de anomalías automática
- [ ] Clasificación automática de equipos por curva de carga
- [ ] Recomendaciones personalizadas por ML
- [ ] Pipeline de entrenamiento con datos históricos

---

## 🚀 PITCH PARA EL CEO DE TERAOBJECT

### Elevator Pitch (30 segundos):

> "**ModoAhorroSaaS** transforma los medidores inteligentes de Teraobject en una plataforma completa de **optimización energética**. No solo medimos el consumo, lo **explicamos equipo por equipo**, correlacionamos con el **clima**, recomendamos **productos eficientes con ROI calculado**, y creamos un **gemelo digital** de la instalación. Es como tener un **ingeniero energético 24/7** por €19.99/mes."

### Puntos Clave para la Demo:

1. **Problema Actual** (2 min)
   - Usuario recibe factura alta pero no sabe POR QUÉ
   - Medidores solo muestran números, no dan contexto
   - No hay recomendaciones accionables

2. **Solución ModoAhorroSaaS + Teraobject** (5 min)
   - 🏠 Dashboard en tiempo real con datos del medidor
   - ⚡ Análisis equipo por equipo (inventario vs consumo real)
   - 🌡️ Correlación con clima (aire acondicionado consume más en verano)
   - 🛒 Recomendaciones de productos con ROI
   - 🤖 Gemelo digital para simular cambios

3. **Caso de Uso Real** (3 min)
   - María tiene factura de €180/mes
   - ModoAhorroSaaS detecta que su heladera de 15 años consume 40% del total
   - Recomienda modelo eficiente de €650 en Mercado Libre
   - ROI: €65/mes de ahorro → Payback 10 meses
   - María ahorra €780/año después del payback

4. **Modelo de Negocio** (2 min)
   - Bundle: Medidor Teraobject + ModoAhorroSaaS
   - Precio competitivo vs competencia
   - Comisiones de marketplace
   - Datos agregados para B2B

5. **Roadmap Técnico** (3 min)
   - MVP ya funcional (mostrar demo real)
   - Integración API Teraobject: 6 semanas
   - Gemelo digital + ML: 14 semanas más
   - Total: 5 meses para producto completo

---

## 📊 COMPETIDORES Y DIFERENCIACIÓN

| Feature | ModoAhorroSaaS + Teraobject | Nest/Ecobee | Sense | Emporia Vue |
|---------|------------------------------|-------------|-------|-------------|
| Medición en tiempo real | ✅ | ✅ | ✅ | ✅ |
| Inventario de equipos | ✅ | ❌ | ❌ | ❌ |
| Análisis equipo por equipo | ✅ | Parcial | Parcial | Parcial |
| Correlación con clima | ✅ | ❌ | ❌ | ❌ |
| Recomendaciones + Marketplace | ✅ | ❌ | ❌ | ❌ |
| Gemelo Digital | ✅ | ❌ | ❌ | ❌ |
| Simulador de ahorro | ✅ | ❌ | ❌ | ❌ |
| Precio/mes | €19.99 | €30+ | €25+ | €15 |
| **DIFERENCIACIÓN** | 🏆 Único en mercado | Solo termostato | Solo detección | Solo medición |

---

## ✅ CHECKLIST PARA LA DEMO CON EL CEO

### Antes de la Reunión:
- [ ] Preparar datos de demo realistas (Casa con 30 equipos)
- [ ] Seedear facturas de 6 meses
- [ ] Tener dashboard general cargado y bonito
- [ ] Preparar vista de ajuste de snapshots (feature estrella)
- [ ] Mockup de integración Teraobject (Postman con ejemplos)
- [ ] Mockup de recomendaciones con productos reales de Mercado Libre
- [ ] Slides de presentación (Pitch + Roadmap + Financiero)

### Durante la Demo (15 min):
1. ⏱️ **0-3 min**: Dashboard general (métricas compactas, bonito)
2. ⏱️ **3-6 min**: Dashboard de entidad (medidor animado, suministros)
3. ⏱️ **6-10 min**: Ajuste de snapshots ⭐ (agrupar por ubicación, Alpine.js reactivo)
4. ⏱️ **10-12 min**: Mockup integración Teraobject (lecturas en tiempo real)
5. ⏱️ **12-15 min**: Mockup recomendaciones marketplace (ROI calculado)

### Preguntas que Probablemente Hará:
- ❓ "¿Cómo se integra con nuestros medidores?" → **API REST, webhook o MQTT**
- ❓ "¿Cuánto tiempo toma la integración?" → **6 semanas para MVP integrado**
- ❓ "¿Qué pasa si el usuario no tiene medidor?" → **Funciona solo con facturas (plan básico)**
- ❓ "¿Cómo monetizamos?" → **Hardware + SaaS + Marketplace + Datos B2B**
- ❓ "¿Cuál es el gemelo digital?" → **Réplica virtual en tiempo real para simulaciones**

---

## 🎉 CONCLUSIÓN

La integración de **ModoAhorroSaaS + Teraobject IoT** crea un ecosistema completo:

✅ **Hardware** (Teraobject) + **Software** (ModoAhorroSaaS) = Solución 360°  
✅ **Tiempo real** + **Análisis profundo** = Insights accionables  
✅ **Recomendaciones** + **Marketplace** = Monetización adicional  
✅ **Gemelo digital** + **ML** = Tecnología de vanguardia  

**Es el momento perfecto para esta propuesta**:
- 🌍 Crisis energética en Europa → Usuarios buscan ahorrar
- 📱 IoT maduro → Hardware accesible y confiable
- 🤖 IA/ML democratizado → Fácil de implementar
- 🛒 E-commerce integrado → Usuarios compran online

---

**Próximo paso**: Agendar 30 min con el CEO de Teraobject para mostrar el MVP y discutir partnership. 🚀


P## ✅ ARQUITECTURA DE SNAPSHOTS IMPLEMENTADA

### 📊 Resumen de Decisiones de Diseño

Todas las decisiones tomadas basadas en los casos extremos identificados:

1. **Edición durante confirmación de snapshot**
   - ✅ Cambios de potencia/categoría → Invalidan todo el contexto
   - ✅ Cambios de tiempo de uso → **FREEZADOS** en vista equipamiento (solo editables en snapshots)
   - ✅ Observer detecta cambios automáticamente

2. **Equipos nuevos a mitad de período**
   - ✅ Modal pregunta: "¿Este equipo es nuevo o existía antes?"
   - ✅ Si existía → Crear snapshots retroactivos + alertas
   - ✅ Si es nuevo → Prorratear días (implementación futura Fase 2)

3. **Eliminación de equipos**
   - ✅ Dos opciones:
     - **Hard Delete**: Nunca existió (error) → Elimina todo
     - **Soft Delete**: Existió pero ya no está → `deleted_at`, snapshots con `is_equipment_deleted=true`
   - ✅ Histórico completo en `equipment_history`

4. **Recálculos ilimitados**
   - ✅ Sin límite (usuario puede editar N veces)
   - ✅ Estado `recalculated` con contador `recalculation_count`
   - ✅ Cada recálculo registrado en `snapshot_change_alerts`

5. **Consumo Real vs Estimado**
   - ✅ Facturas = Real (única fuente de verdad)
   - ✅ Snapshots = Estimado calculado
   - ✅ Diferencia = Ajuste manual o equipos faltantes

---

### 🏗️ Componentes Implementados

#### ✅ **Migraciones Ejecutadas**

1. **`entity_equipment` - Campos de lifecycle:**
   - `activated_at`: Fecha de instalación
   - `replaced_at`: Fecha de reemplazo
   - `replaced_by_id`: FK al equipo que reemplazó
   - `power_last_changed_at`: Última modificación de potencia
   - `usage_last_changed_at`: Última modificación de uso
   - `deleted_at`: Soft delete (ya existía)

2. **`equipment_usage_snapshots` - Estados y tracking:**
   - `status`: `draft`, `confirmed`, `invalidated`, `recalculated`
   - `invalidated_at`: Timestamp de detección de cambio
   - `invalidation_reason`: Descripción del cambio
   - `recalculation_count`: Contador de recálculos
   - `is_equipment_deleted`: Marca si equipo fue dado de baja

3. **`equipment_history` - Auditoría completa:**
   - `change_type`: `power_changed`, `usage_changed`, `type_changed`, `activated`, `deleted`, `replaced`
   - `before_values`: JSON con estado anterior
   - `after_values`: JSON con estado nuevo
   - `change_description`: Descripción legible
   - `changed_by_user_id`: FK al usuario que hizo el cambio

4. **`snapshot_change_alerts` - Alertas de invalidación:**
   - `alert_type`: Tipo de cambio detectado
   - `message`: Mensaje para el usuario
   - `affected_snapshots`: Array JSON de IDs invalidados
   - `status`: `pending`, `acknowledged`, `resolved`

#### ✅ **Observer Implementado**

**`EntityEquipmentObserver`** detecta automáticamente:
- ✅ Creación de equipo → Invalidar snapshots confirmados
- ✅ Cambio de potencia → Actualizar `power_last_changed_at` + invalidar
- ✅ Cambio de uso → Actualizar `usage_last_changed_at` + invalidar
- ✅ Cambio de tipo → Invalidar (cambia categoría completa)
- ✅ Soft delete → Marcar `is_equipment_deleted=true` en snapshots
- ✅ Registrar TODO en `equipment_history`
- ✅ Crear `snapshot_change_alerts` para notificar al usuario

---

### 📋 Próximos Pasos (Fase 1B)

1. ✅ Migraciones - COMPLETADO
2. ✅ Observer - COMPLETADO
3. ✅ Modelos (`EquipmentHistory`, `SnapshotChangeAlert`) - COMPLETADO
4. ⏳ **Controlador: SnapshotController**
   - `reviewChanges()`: Vista de snapshots invalidados
   - `recalculate()`: Recalcular snapshot individual
   - `recalculateAll()`: Recalcular múltiples períodos
5. ⏳ **Vista: `snapshots/review-changes.blade.php`**
   - Tabla de cambios detectados (before/after)
   - Botón "Recalcular" (sin opción ignorar)
   - Histórico de recálculos
6. ⏳ **Vista: Banner en `entities/show.blade.php`**
   - Alerta persistente de snapshots invalidados
   - Link a `/snapshots/review-changes`
7. ⏳ **Vista: Freezar campo tiempo de uso**
   - Deshabilitar `avg_daily_use_minutes_override` en vista equipos
   - Solo editable en snapshots

---

Equipos creados a mitad de período deben prorratear días?

Opción A: Sí, calcular días parciales (más complejo pero preciso)
¿Snapshots pueden recalcularse N veces o solo una?

Opción A: Ilimitado (usuario puede editar y recalcular cuantas veces quiera)

¿Guardamos histórico de valores anteriores del equipo?

Opción A: Sí, tabla equipment_history con todos los cambios
