# 🏗️ ARQUITECTURA DE SERVICES - ModoAhorroSaaS

## 📖 Filosofía: Controllers HTTP vs Services

### **Controllers HTTP** (`app/Http/Controllers/`)
**Responsabilidad:** Manejar la solicitud HTTP y devolver la respuesta
- ✅ Validar requests
- ✅ Autorizar acciones
- ✅ Coordinar Services
- ✅ Preparar datos para vistas
- ✅ Retornar respuestas (views, JSON, redirects)
- ❌ **NO** realizar cálculos complejos
- ❌ **NO** lógica de negocio pesada
- ❌ **NO** duplicar código entre controllers

**Ejemplo:**
```php
// ✅ BIEN: Controller delgado
public function index()
{
    $equipments = EntityEquipment::all();
    $analysis = $this->calculationService->calculateBulkConsumption($equipments, 30, 150);
    return view('dashboard', compact('analysis'));
}

// ❌ MAL: Controller gordo con lógica
public function index()
{
    $equipments = EntityEquipment::all();
    $totalKwh = 0;
    foreach ($equipments as $eq) {
        $powerWatts = $eq->power_watts_override ?? $eq->equipmentType->default_power_watts;
        $horasPerDay = ($eq->avg_daily_use_minutes_override ?? 0) / 60;
        $loadFactor = $eq->factor_carga ?? 1.0;
        // ... 50 líneas más de cálculo duplicado ...
    }
    return view('dashboard', compact('totalKwh'));
}
```

---

### **Services** (`app/Services/`)
**Responsabilidad:** Lógica de negocio reutilizable
- ✅ Cálculos complejos
- ✅ Procesamiento de datos
- ✅ Análisis y recomendaciones
- ✅ Integración con APIs externas
- ✅ Reglas de negocio centralizadas
- ✅ Testeable en aislamiento
- ✅ Reutilizable en múltiples controllers

**Ejemplo:**
```php
// Service encapsula toda la lógica
class EquipmentCalculationService
{
    public function calculateEquipmentConsumption($equipment, $days, $tariff)
    {
        // Fórmula Python replicada
        $hoursPerDay = $this->getHoursPerDay($equipment);
        $loadFactor = $equipment->factor_carga ?? 1.0;
        $efficiency = $equipment->eficiencia ?? 1.0;
        
        $activeKwh = ($hoursPerDay * $loadFactor * $equipment->quantity * $powerWatts) 
                     / ($efficiency * 1000);
        
        return [
            'kwh_activo' => $activeKwh,
            'costo' => $activeKwh * $tariff,
            // ...
        ];
    }
}
```

---

## 🗂️ Estructura de Services Recomendada

### **Services Existentes:**
```
app/Services/
├── EquipmentCalculationService.php  ✅ IMPLEMENTADO
│   ├── calculateEquipmentConsumption()
│   ├── calculateFromInvoice()
│   ├── calculateBulkConsumption()
│   ├── calculateStandbySavingsPotential()
│   ├── calculateReplacementAnalysis()
│   └── generateReplacementSuggestions()
│
└── InventoryAnalysisService.php     ⚠️ REVISAR (usado en RecommendationController)
    └── findAllOpportunities()
```

### **Services a Crear/Refactorizar:**

```
app/Services/
├── RecommendationService.php        📝 NUEVO
│   ├── generateStandbyRecommendations()
│   ├── generateUsageRecommendations()
│   ├── generateMaintenanceRecommendations()
│   └── prioritizeRecommendations()
│
├── InvoiceAnalysisService.php       📝 NUEVO
│   ├── calculateAverageTariff()
│   ├── comparePeriods()
│   ├── detectAnomalies()
│   └── forecastNextPeriod()
│
├── SnapshotService.php              📝 NUEVO
│   ├── createSnapshot()
│   ├── recalculateSnapshot()
│   ├── validateAgainstInvoice()
│   └── detectChanges()
│
└── SolarAnalysisService.php         📝 FUTURO
    ├── calculatePotential()
    ├── estimateROI()
    └── simulateProduction()
```

---

## 🎯 Mapeo: Controllers → Services

### **1. EntityEquipmentController**
**Estado:** ✅ Parcialmente OK
- ✅ Ya usa `assignFactorsAndCalculate()` al crear/editar
- ⚠️ Debería validar que haya factura antes de permitir carga

**Migración pendiente:**
```php
// ANTES: Cálculo manual en el controller
$equipment->energia_consumida_wh = ($horasPorDia * $factor_carga * $quantity * $powerWatts) / $eficiencia;

// DESPUÉS: Delegar al Service
$calculation = $this->calculationService->calculateEquipmentConsumption($equipment, 1, 0);
$equipment->energia_consumida_wh = $calculation['kwh_activo'] * 1000; // Convertir a Wh
```

**Validación a agregar:**
```php
public function store(Request $request, Entity $entity)
{
    // Verificar que la entidad tenga al menos una factura
    if (!$entity->supplies()->whereHas('contracts.invoices')->exists()) {
        return redirect()->back()
            ->with('error', 'Debés cargar al menos una factura antes de agregar equipos.');
    }
    
    // ... resto del código
}
```

---

### **2. UsageSnapshotController**
**Estado:** ⚠️ MUCHOS CÁLCULOS MANUALES

**Código actual:**
```php
// Línea 141: Cálculo manual de kWh
$activeKwh = ($powerWatts / 1000) * $totalHours;
$standbyWatts = $hasStandby ? ($entityEquipment->equipmentType->standby_power_watts ?? 0) : 0;
$standbyKwh = ($standbyWatts / 1000) * $standbyHours * max(1, (int)$entityEquipment->quantity);
$calculatedKwh = $activeKwh + $standbyKwh;
```

**Refactorización sugerida:**
```php
// CREAR: SnapshotService
class SnapshotService
{
    public function __construct(
        protected EquipmentCalculationService $calculationService
    ) {}
    
    public function calculateSnapshotConsumption($equipment, $invoice, $snapshotData)
    {
        $days = $invoice->start_date->diffInDays($invoice->end_date);
        $tariff = $this->calculationService->calculateAverageTariff($invoice);
        
        // Usar el service existente
        return $this->calculationService->calculateEquipmentConsumption(
            $equipment, 
            $days, 
            $tariff
        );
    }
}

// USAR en Controller:
$calculation = $this->snapshotService->calculateSnapshotConsumption(
    $entityEquipment,
    $invoice,
    $snapshotData
);

$snapshot = UsageSnapshot::create([
    'calculated_kwh_period' => $calculation['kwh_total'],
    // ...
]);
```

---

### **3. StandbySettingsController**
**Estado:** ⚠️ Lógica de recomendaciones en el controller

**Código actual:**
```php
// applyRecommendations() tiene lógica compleja mezclada
// Debería delegar a un Service
```

**Refactorización:**
```php
// CREAR: RecommendationService
class RecommendationService
{
    public function generateStandbyRecommendations($equipments)
    {
        $recommendations = [];
        
        foreach ($equipments as $eq) {
            $avgMinutesPerDay = $this->getAvgDailyMinutes($eq);
            $isContinuous = $avgMinutesPerDay >= (24 * 60 * 0.9);
            
            if (!$isContinuous && $this->shouldHaveStandby($eq)) {
                $recommendations[] = [
                    'equipment_id' => $eq->id,
                    'action' => 'enable_standby',
                    'reason' => 'No es de uso continuo',
                ];
            }
        }
        
        return $recommendations;
    }
}

// USAR en Controller:
$recommendations = $this->recommendationService->generateStandbyRecommendations($equipments);
```

---

### **4. EconomicsCenterController**
**Estado:** ✅ **EXCELENTE** - Ya usa Services correctamente

```php
// Ejemplo de controller bien hecho:
$bulkCalculation = $this->calculationService->calculateBulkConsumption(...);
$standbySavings = $this->calculationService->calculateStandbySavingsPotential(...);
$replacementAnalysis = $this->calculationService->calculateReplacementAnalysis(...);

return view('economics.index', compact('metrics', 'standbyDetails', 'replacementDetails'));
```

**Este es el patrón a seguir en otros controllers** ✅

---

## 🔧 Plan de Refactorización

### **Fase 1: Validaciones Críticas** (AHORA)
- [ ] `EntityEquipmentController`: Validar factura antes de cargar equipos
- [ ] Crear middleware `HasInvoice` para rutas que requieran factura
- [ ] Agregar mensajes de error claros

### **Fase 2: Crear Services Faltantes** (PRÓXIMO)
- [ ] `SnapshotService`: Mover lógica de `UsageSnapshotController`
- [ ] `RecommendationService`: Centralizar todas las recomendaciones
- [ ] `InvoiceAnalysisService`: Análisis de facturas

### **Fase 3: Refactorizar Controllers** (DESPUÉS)
- [ ] `UsageSnapshotController` → usar `SnapshotService`
- [ ] `StandbySettingsController` → usar `RecommendationService`
- [ ] `UsageSettingsController` → usar `RecommendationService`
- [ ] `ReplacementRecommendationController` → usar `EquipmentCalculationService`

### **Fase 4: Testing y Documentación**
- [ ] Unit tests para cada Service
- [ ] Integration tests para flujos completos
- [ ] Documentar APIs de Services

---

## 📋 Checklist para Levantar el Servidor

### **Rutas Críticas a Probar:**
```bash
# Dashboard principal
GET /dashboard

# Carga de equipos (debe validar factura)
GET /entities/{entity}/equipment/create
POST /entities/{entity}/equipment

# Centro económico (usa EquipmentCalculationService)
GET /economics

# Recomendaciones
GET /recommendations

# Standby settings
GET /standby

# Usage snapshots (requiere refactorización)
GET /invoices/{invoice}/snapshots/create
POST /invoices/{invoice}/snapshots
```

### **Errores Esperados:**
1. ❌ Vista `economics/index.blade.php` no existe o falta variables
2. ❌ Vistas esperan variables que ya no existen (`$equipmentDetails` vs `equipmentDetails`)
3. ❌ Routes sin protección de factura
4. ❌ Controllers intentando acceder a métodos de Services que no existen

### **Comandos para Debugging:**
```bash
# Ver todas las rutas
php artisan route:list

# Ver errores de compilación
php artisan optimize:clear

# Levantar servidor
php artisan serve

# Logs en tiempo real
tail -f storage/logs/laravel.log
```

---

## 🎓 Buenas Prácticas

### **Inyección de Dependencias**
```php
// ✅ BIEN: Inyectar en constructor
class EconomicsCenterController extends Controller
{
    public function __construct(
        protected EquipmentCalculationService $calculationService
    ) {}
    
    public function index()
    {
        $data = $this->calculationService->calculate(...);
    }
}

// ❌ MAL: Instanciar manualmente
public function index()
{
    $service = new EquipmentCalculationService();
    $data = $service->calculate(...);
}
```

### **Naming Conventions**
```php
// Services: SustantivoService
EquipmentCalculationService
RecommendationService
InvoiceAnalysisService

// Métodos: verbo + sustantivo
calculate()
generate()
analyze()
process()

// Return types: Arrays asociativos documentados
/**
 * @return array{kwh_activo: float, kwh_standby: float, costo: float}
 */
```

### **Separación de Responsabilidades**
```
Controller → Validar + Autorizar + Coordinar
Service → Calcular + Procesar + Analizar
Model → Acceder a DB + Relaciones
Repository → Queries complejas (opcional)
```

---

## 🚀 Próximos Pasos

1. **Levantar servidor y encontrar errores**
   ```bash
   php artisan serve
   ```

2. **Ir ruta por ruta probando:**
   - `/dashboard`
   - `/economics`
   - `/entities/{entity}/equipment/create`
   - `/recommendations`

3. **Crear Services faltantes según necesidad:**
   - Cuando veas cálculos duplicados → Service
   - Cuando veas lógica compleja en controller → Service
   - Cuando veas potencial de reutilización → Service

4. **Documentar decisiones:**
   - Por qué moviste X a un Service
   - Qué beneficio trae
   - Cómo se usa

---

**¿Listo para arrancar el servidor? 🚀**

Cuando levantes, voy a ir contigo paso a paso arreglando cada error y migrando lógica a Services donde corresponda.
