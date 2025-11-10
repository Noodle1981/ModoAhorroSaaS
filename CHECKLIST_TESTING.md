# ✅ CHECKLIST DE TESTING - Servidor Laravel

## 🚀 Pre-Arranque

```bash
# Limpiar caché
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Verificar errores de sintaxis
php artisan about

# Levantar servidor
php artisan serve
```

---

## 📍 Rutas Críticas por Orden de Importancia

### **1. Dashboard Principal** ✅
**URL:** `http://localhost:8000/dashboard`

**Esperado:**
- ✅ Muestra resumen de entidades, facturas y equipos
- ✅ Gráficos de consumo

**Posibles errores:**
- ❌ Vista no encuentra variables
- ❌ Relaciones de modelos no cargadas
- ❌ Gráficos sin datos

**Fix:**
```php
// Si DashboardController tiene cálculos manuales, migrar a Service
```

---

### **2. Centro Económico** ✅ PRIORIDAD ALTA
**URL:** `http://localhost:8000/economics`

**Esperado:**
- ✅ Muestra métricas de costo mensual
- ✅ Análisis de standby
- ✅ Sugerencias de reemplazo con ROI
- ✅ Detalles por equipo

**Variables que debe recibir la vista:**
```php
compact(
    'invoices',          // Collection de facturas
    'equipments',        // Collection de equipos
    'metrics',           // Array con todas las métricas
    'equipmentDetails',  // Array de detalles por equipo
    'standbyDetails',    // Array de equipos con standby
    'replacementDetails' // Array de sugerencias de reemplazo
)
```

**Posibles errores:**
- ❌ Vista `economics/index.blade.php` no existe
- ❌ Vista espera variables antiguas
- ❌ Service retorna null cuando no hay sugerencias

**Fix:**
```bash
# Crear vista si no existe
touch resources/views/economics/index.blade.php

# Verificar estructura de datos en controller
dd($metrics, $standbyDetails, $replacementDetails);
```

---

### **3. Carga de Equipos** ⚠️ VALIDACIÓN CRÍTICA
**URL:** `http://localhost:8000/entities/{entity}/equipment/create`

**Esperado:**
- ✅ Si NO hay factura → Redirige con mensaje de error
- ✅ Si HAY factura → Muestra formulario

**Validación a implementar:**
```php
// EntityEquipmentController::create()
public function create(Entity $entity)
{
    // Verificar que tenga factura
    if (!$entity->supplies()->whereHas('contracts.invoices')->exists()) {
        return redirect()
            ->route('entities.show', $entity)
            ->with('error', '⚠️ Debés cargar al menos una factura antes de agregar equipos.');
    }
    
    // ... resto del código
}
```

**POST Equipos:**
**URL:** `POST /entities/{entity}/equipment`

**Esperado:**
- ✅ Asigna `tipo_de_proceso` automáticamente
- ✅ Calcula `factor_carga` y `eficiencia` desde `ProcessFactor`
- ✅ Calcula `energia_consumida_wh`
- ✅ Guarda sin errores

**Posibles errores:**
- ❌ `tipo_de_proceso` null
- ❌ Factores no se asignan
- ❌ Cálculo falla

**Fix:**
```php
// Verificar que assignFactorsAndCalculate() se ejecute
// Ver línea 158-192 de EntityEquipmentController
```

---

### **4. Recomendaciones** 📊
**URL:** `http://localhost:8000/recommendations`

**Esperado:**
- ✅ Hub central de recomendaciones
- ✅ Links a standby, usage, reemplazo

**Posibles errores:**
- ❌ Vista no renderiza
- ❌ Falta enlace a módulos

---

### **5. Standby Settings** 🔌
**URL:** `http://localhost:8000/standby`

**Estado actual:** ⚠️ Tiene lógica de cálculo en controller

**Esperado:**
- ✅ Muestra equipos agrupados por categoría
- ✅ Permite activar/desactivar standby
- ✅ Genera recomendaciones

**Refactorización pendiente:**
- Mover lógica de `applyRecommendations()` a `RecommendationService`

**Probar:**
1. Ver lista de equipos
2. Activar standby en un equipo
3. Confirmar configuración
4. Aplicar recomendaciones

---

### **6. Usage Snapshots** 📸 ⚠️ REFACTORIZACIÓN PENDIENTE
**URL:** `http://localhost:8000/invoices/{invoice}/snapshots/create`

**Estado actual:** Muchos cálculos manuales en el controller

**Esperado:**
- ✅ Muestra equipos para ajustar uso
- ✅ Calcula consumo por período
- ✅ Compara con factura real

**Refactorización pendiente:**
```php
// ANTES: Cálculos manuales (líneas 141-147)
$activeKwh = ($powerWatts / 1000) * $totalHours;
$standbyKwh = ($standbyWatts / 1000) * $standbyHours * $quantity;

// DESPUÉS: Usar EquipmentCalculationService
$calculation = $this->calculationService->calculateEquipmentConsumption($eq, $days, $tariff);
$activeKwh = $calculation['kwh_activo'];
$standbyKwh = $calculation['kwh_standby'];
```

---

### **7. Reemplazo de Equipos** 💡
**URL:** `http://localhost:8000/replacement-recommendations`

**Estado:** ❓ No verificado

**Debe usar:** `EquipmentCalculationService::generateReplacementSuggestions()`

---

## 🐛 Debugging Tools

### **1. Ver Rutas Registradas**
```bash
php artisan route:list | grep -i "equipment\|economics\|standby\|recommendation"
```

### **2. Ver Errores en Logs**
```bash
# Windows PowerShell
Get-Content storage\logs\laravel.log -Tail 50 -Wait

# Windows CMD
type storage\logs\laravel.log
```

### **3. Verificar Configuración**
```bash
php artisan config:show app
php artisan config:show database
```

### **4. Debug en Controller**
```php
// Agregar temporalmente en el método
dd($variable); // Dump and die
dump($variable); // Dump y continuar
logger()->info('Debug', ['data' => $variable]); // Log
```

---

## 📝 Errores Comunes y Soluciones

### **Error: "View [economics.index] not found"**
**Causa:** Vista no existe

**Fix:**
```bash
mkdir -p resources/views/economics
touch resources/views/economics/index.blade.php
```

**Vista mínima:**
```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Centro Económico</h1>
    
    <div class="row">
        <div class="col-md-12">
            <pre>{{ json_encode($metrics, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
</div>
@endsection
```

---

### **Error: "Call to a member function ... on null"**
**Causa:** Service no inyectado o relación no cargada

**Fix:**
```php
// Verificar inyección de dependencias
public function __construct(
    protected EquipmentCalculationService $calculationService
) {}

// Eager loading de relaciones
$equipments = EntityEquipment::with(['equipmentType', 'processFactor'])->get();
```

---

### **Error: "Undefined array key 'standby_details'"**
**Causa:** Vista espera variable que ya no existe

**Fix:**
```php
// En controller, asegurar que se pase
return view('economics.index', compact(
    'invoices',
    'equipments',
    'metrics',
    'equipmentDetails',
    'standbyDetails',      // ← Verificar que exista
    'replacementDetails'   // ← Verificar que exista
));
```

---

### **Error: "SQLSTATE[HY000]: General error"**
**Causa:** Query mal formada o campo no existe

**Fix:**
```bash
# Ver última migración
php artisan migrate:status

# Recrear DB si es necesario
php artisan migrate:fresh --seed
```

---

## 🎯 Orden de Testing Recomendado

1. ✅ **Dashboard** - Verificar que carga
2. ✅ **Entities** - Ver entidades existentes
3. ✅ **Invoices** - Cargar factura si no hay
4. ✅ **Equipment Create** - Probar validación de factura
5. ✅ **Equipment Store** - Crear equipo y verificar cálculos
6. ✅ **Economics Center** - Verificar métricas y análisis
7. ✅ **Recommendations** - Ver hub
8. ✅ **Standby Settings** - Configurar standby
9. ✅ **Usage Snapshots** - Ajustar consumo

---

## 📊 Datos de Prueba

### **Usuario de Testing:**
- Email: (el que creaste en seeders)
- Password: (configurado en seeder)

### **Entidad de Testing:**
- ID: 1
- Nombre: "Casa de Prueba" (o el que hayas creado)

### **Factura de Testing:**
- ID: 1
- Período: 64 días
- Tarifa: $153.73/kWh
- Consumo: 623 kWh

### **Equipos de Testing:**
- 34 equipos cargados via SampleHouseCasaSeeder
- Todos con tipo_de_proceso asignado
- Factores calculados correctamente

---

## 🚨 Red Flags (Qué NO debe pasar)

❌ **Errores 500** sin mensaje claro
❌ **Cálculos dando 0** cuando deberían tener valores
❌ **Vistas mostrando "null"** o "undefined"
❌ **Redirecciones infinitas**
❌ **Queries N+1** (muchas queries en un loop)
❌ **Memoria agotada** (cálculos muy pesados)

---

## ✅ Señales de Éxito

✅ **Dashboard carga** en <2 segundos
✅ **Economics muestra métricas** correctas
✅ **Cálculos coinciden** con Python
✅ **Validaciones funcionan** (no deja cargar sin factura)
✅ **Services son reutilizables** en múltiples controllers
✅ **No hay código duplicado** entre controllers

---

**¡Listo para levantar! 🚀**

Cuando arranques el servidor, avisame qué ves en:
1. `http://localhost:8000/dashboard`
2. `http://localhost:8000/economics`

Y vamos juntos corrigiendo cada error que aparezca.
