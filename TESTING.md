# 🧪 Guía de Testing - ModoAhorroSaaS

> Objetivo: disponer de una referencia clara y evolutiva para testear cálculos, flujos y reglas de negocio. Esta guía cubre testing manual (exploratorio), semi-automatizado (scripts) y automatizado (PHPUnit).

---
## Índice
1. Estrategia General
2. Entorno y Datos Semilla
3. Flujos Críticos a Testear
4. Testing de Cálculos (Consumo / Frecuencia / Standby / Económico)
5. Testing de Snapshots (Interacción + Persistencia)
6. Testing de Recomendaciones (Heurísticas y Standby)
7. Testing de Centro Económico
8. Testing de Alertas (SmartAlert)
9. Scripts Manuales Rápidos
10. Cobertura y Métricas
11. Checklist por Release
12. Próximas Mejoras

---
## 1. Estrategia General

| Tipo | Objetivo | Herramientas | Frecuencia |
|------|----------|--------------|------------|
| Exploratorio | Validar UX y coherencia | Navegación manual | Cada feature nueva |
| Unit | Asegurar lógica pura (cálculos) | PHPUnit (tests/Unit) | CI / cada push |
| Feature | Validar flujos con DB / sesión | PHPUnit (tests/Feature) | CI / nightly |
| Script puntual | Inspección rápida de casos masivos | `artisan tinker` / scripts en raíz | Ad-hoc |
| Regresión | Evitar romper lo existente | Suite completa | Antes de release |

Priorizar primero Unit Tests de cálculos (fáciles, alto ROI), luego Feature Tests de flujos principales.

---
## 2. Entorno y Datos Semilla

### Comandos básicos
```bash
php artisan migrate:fresh --seed
php artisan test
```

### Recomendación para desarrollo
- Usar SQLite para rapidez local, PostgreSQL en staging.
- Sembrar datos adicionales si el seeder base no cubre escenarios (equipos con uso continuo, facturas con cero consumo, etc.).

### Datos mínimos para probar:
- 1 Company con 1–2 Entities.
- Facturas: al menos 2 con diferentes duraciones (28 vs 31 días).
- Equipos: mezcla de categorías (iluminación, climatización, electrodomésticos, entretenimiento) con y sin standby.
- Snapshots creados y uno pendiente de ajuste.

---
## 3. Flujos Críticos a Testear

| Flujo | Ruta | Objetivo |
|-------|------|----------|
| Gestión Standby | `/standby` | Confirmar configuración + aplicar recomendaciones |
| Gestión Uso (Frecuencia) | `/usage` | Guardar días/semana, confirmar, aplicar heurísticas |
| Ajuste de Período | `/invoices/{id}/snapshots/create` | Redirección gating si no confirma uso, persistencia de ajustes |
| Resumen de Período | `/invoices/{id}/snapshots` | Ver métricas consistentes con ajustes |
| Centro Económico | `/economics` | Mostrar costos, ahorro standby y facturas recientes |
| Recomendaciones Centro | `/recommendations` | Estado de tarjetas (Pendiente/Confirmado) |
| Alertas | `/alerts` (si existe) | Activación / dismiss correcto |

---
## 4. Testing de Cálculos

### 4.1 Consumo por Equipo (Estimado)
Fórmula esperada (simplificada):
```
KWh período = (potencia_watts * minutos_uso_día / 60 / 1000) * días_efectivos
```
Casos a testear:
- Uso diario (is_daily_use = true) → días = 7/sem → período completo.
- Uso parcial 3/sem → días = (3 / 7) * días del período (aprox redondeo?).
- Minutos override vs default.
- Potencia override vs default.

Unit Test sugerido:
```php
public function test_calculated_kwh_period_for_daily_equipment()
{
    $kwh = $this->calc->periodKwh(power:100, minutesPerDay:120, daysEffective:30);
    $this->assertEquals(6.0, $kwh); // 100W * 120m = 2h => 0.2kW * 30 = 6 kWh
}
```

### 4.2 Frecuencia (Gestión de Uso)
Casos:
- Diario → `usage_days_per_week = null`
- No diario → validación 1–7
- Cambiar de diario a parcial limpia días/semana
- Heurística `suggestDaysPerWeek` según potencia y minutos

### 4.3 Standby
Heurística implementada en aplicación recomendada:
- Potencia standby estimada: `clamp(3% potencia, 0.5W..8W)`
- Horas ociosas = 24h – horas activas
- Ahorro potencial mensual = standby_watts * horas_ociosas * 30 / 1000 * tarifa

Testear:
```php
$this->assertEquals(approx(1.62), $calculator->standbyPotential(power:150, activeMinutes:360, tariff:0.25));
```

### 4.4 Centro Económico
- Gasto mensual = promedio normalizado (costo/día * 30)
- Tarifa media = (total_amount / total_kWh) por factura válida
- Ahorro standby potencial > 0 si existe al menos 1 equipo con standby

---
## 5. Testing de Snapshots

### Casos clave:
1. Redirección si no confirma uso → `/usage?invoice={id}`
2. Al guardar ajustes, se crean registros en `equipment_usage_snapshots`
3. Cálculo de `calculated_period_kwh` consistente con fórmula base
4. Equipos borrados → `is_equipment_deleted` marcado en snapshot
5. Recalculation count incrementado si se guarda segunda vez

### Feature Test ejemplo:
```php
public function test_snapshot_store_creates_rows()
{
    $invoice = Invoice::factory()->create([...]);
    $equip = EntityEquipment::factory()->count(3)->create([...]);

    $response = $this->actingAs($user)->post(route('snapshots.store', $invoice), [
        'adjustments' => [
            $equip[0]->id => ['minutes' => 120],
            $equip[1]->id => ['minutes' => 45],
        ]
    ]);

    $response->assertRedirect();
    $this->assertDatabaseCount('equipment_usage_snapshots', 2);
}
```

---
## 6. Testing de Recomendaciones

### Standby (per-invoice)
- Si el período ya ajustado → mensaje “No se generan recomendaciones”
- Si no ajustado → retorna lista JSON de recomendaciones

### Frecuencia (Uso)
- Endpoint `/usage/recommendations` retorna array con `current` y `suggested`
- Cambios marcados si difiere `is_daily_use` o `usage_days_per_week`

### Tests sugeridos:
```php
public function test_usage_recommendations_returns_expected_shape()
{
    $resp = $this->actingAs($user)->get(route('usage.recommendations'));
    $resp->assertOk()->assertJsonStructure([
        'equipments' => [
            '*' => ['id','name','category','current'=>['is_daily_use','usage_days_per_week'],'suggested'=>['is_daily_use','usage_days_per_week']]
        ]
    ]);
}
```

---
## 7. Testing del Centro Económico

Validar que:
- Métricas aparecen (o “—”) según datos
- Facturas listadas con kWh, costo y tarifa calculada
- Ahorro standby potencial > 0 si hay equipos con `has_standby_mode = true`

Feature Test:
```php
public function test_economics_center_shows_metrics()
{
    $this->seed();
    $resp = $this->actingAs($user)->get(route('economics.index'));
    $resp->assertStatus(200);
    $resp->assertSee('Gasto Mensual Estimado');
    $resp->assertSee('Potencial Standby');
}
```

---
## 8. Testing de Alertas (SmartAlert)

Casos:
- Creación automática al confirmar standby uso
- Dismiss al aplicar recomendaciones
- Alertas nuevas al crear equipo si gestión confirmada

Test:
```php
public function test_alert_created_on_new_equipment_after_usage_confirmed()
{
    session(['usage_confirmed_at' => now()->toDateTimeString()]);
    $eq = EntityEquipment::factory()->create([...]);
    $this->assertDatabaseHas('smart_alerts', [
        'type' => 'usage_new_equipment',
        'is_dismissed' => 0
    ]);
}
```

---
## 9. Scripts Manuales Rápidos

Archivo sugerido: `test_usage_management_flow.php` (ya existe). Ampliar con:
```php
php test_economics_metrics.php
php test_standby_potential.php
```
Ejemplo:
```php
require 'vendor/autoload.php';
$equipments = \App\Models\EntityEquipment::with('equipmentType')->get();
foreach($equipments as $eq){ /* cálculo rápido */ }
```

---
## 10. Cobertura y Métricas

### Comandos
```bash
php artisan test --coverage --min=70
```
### Objetivos iniciales
| Área | Meta cobertura |
|------|----------------|
| Cálculos puros (services) | 85% |
| Controladores críticos | 60% |
| Modelos (scopes/accessors) | 50% |

Incrementar metas +10% cada release mayor.

---
## 11. Checklist por Release

Antes de cada deploy:
- [ ] `php artisan test` verde
- [ ] Tests de recomendaciones OK
- [ ] Ajuste de período funciona (crea snapshots)
- [ ] Centro Económico métricas coherentes
- [ ] Sin alertas huérfanas (SmartAlert con invoice_id inexistente)
- [ ] Sin N+1 evidente (Debugbar <60 queries en dashboard)
- [ ] Migraciones nuevas ejecutadas

---
## 12. Próximas Mejoras

| Mejora | Prioridad | Notas |
|--------|----------|-------|
| Tests de regresión para snapshots | Alta | Evitar roturas en ajustes futuros |
| Tests de performance (Laravel Benchmark) | Media | Cargar 500 equipos y medir |
| Fakes para clima (Weather API) | Media | Aislar correlación |
| Contract tests para servicios externos | Baja | Marketplace / IoT |
| Dusk Tests (Browser) | Baja | Para interacción compleja UI |

---
## Referencias
- `app/Http/Controllers/UsageSettingsController.php`
- `app/Http/Controllers/StandbySettingsController.php`
- `app/Http/Controllers/UsageSnapshotController.php`
- `app/Http/Controllers/EconomicsCenterController.php`
- `app/Models/EntityEquipment.php`
- `app/Models/EquipmentUsageSnapshot.php`
- `app/Models/SmartAlert.php`

---
**Última actualización**: 9 de noviembre de 2025  
**Versión**: 1.0

---
## 13. Exportación y validación cruzada (legacy)

Para alinear la lógica Laravel (derivación por días/semana y minutes_per_session) con el pipeline legacy (Excel/Python), se incorporó una exportación del inventario y un comparador simple.

### Exportación básica (comparación horas/año)

1) Exportar inventario con horas/año derivadas

- Comando: php artisan inventory:export-usage --format=json
- Salida: storage/app/private/exports/inventory_usage.json
- Por fila incluye: potencia (W, kW), cantidad, flags de frecuencia (is_daily_use, usage_days_per_week, minutes_per_session), minutos override/default, minutos diarios derivados, horas/año, factores (load_factor, efficiency_factor) y kWh/año activo.
- Totales: potencia instalada (kW, W) y suma de horas/año.

2) Comparar contra lógica legacy simplificada

- Script: calculosoroginales/compare_inventory_usage.py
- Uso rápido:
  - python calculosoroginales/compare_inventory_usage.py
  - o con ruta: python calculosoroginales/compare_inventory_usage.py storage/app/private/exports/inventory_usage.json
- Qué compara:
  - Laravel: horas/año derivadas considerando patrón de uso (días/semana + minutes_per_session cuando aplica).
  - Legacy: horas/año = (avg_daily_use_minutes_override OR default_avg_daily_use_minutes) / 60 * 365 (sin patrón de frecuencia).
- Reporta: diferencias globales y por equipo si |diff| > 5%.

3) Resultado de referencia (ejecución local)

- Potencia instalada exportada: 7.23 kW (coincide con legacy).
- Suma horas/año (Laravel): 47,754.17
- Suma horas/año (Legacy): 49,743.42
- Diferencia relativa global: -4.00%
- Principales diferencias: equipos con is_daily_use=false y sin usage_days_per_week (consumo activo = 0 en derivación actual), que legacy trataba con promedio fijo; esto es esperado y deseable, pues refleja la frecuencia real cargada.

4) Uso recomendado en CI

- Agregar un assert laxo: |Δglobal| ≤ 10% y sin más de N equipos con Δ > 25%, salvo que existan flags de frecuencia que justifiquen la diferencia.
- Publicar el JSON como artifact para inspección manual si el umbral se supera.

---

### Golden Test: validación completa Python vs Laravel

**Objetivo**: Reproducir en Python todos los cálculos de consumo (activo, standby), costos y recomendaciones usando datos reales de la DB, comparar con Laravel y detectar discrepancias en la aplicación de factores (load_factor, efficiency_factor), standby, etc.

**Workflow**:

1. **Exportar datos completos** (desde Laravel)
   ```bash
   php artisan inventory:export-usage --full --include-standby
   php artisan invoices:export
   ```
   - Salida:
     - `storage/app/private/exports/equipments.json` (estructura anidada: entity, type, category, calculation_factor, usage_pattern, calculated)
     - `storage/app/private/exports/invoices.json` (facturas con tarifa promedio, kWh, período)

2. **Descargar JSON localmente** (o copiar a Colab)
   - Ruta completa: `D:\modoahorrosaas\ModoAhorroSaaS\storage\app\private\exports\equipments.json` e `invoices.json`

3. **Ejecutar script Python golden** (calculosoroginales/golden_test_calculator.py)
   - Carga los JSON exportados
   - Calcula por cada equipo:
     - Consumo activo anual: `(kW * horas_año * load_factor / efficiency_factor) * qty`
     - Consumo standby anual: `(standby_watts / 1000 * horas_ociosas) * qty`
     - Costo anual: `total_kwh * tarifa_promedio`
   - Compara Python vs Laravel y reporta diferencias > 5%
   - Exporta `golden_results.json` con valores esperados

4. **Implementar/ajustar servicios Laravel** según discrepancias
   - Si Python y Laravel difieren > 5% en múltiples equipos, revisar:
     - `InventoryAnalysisService::calculateEnergyProfileForPeriod()` → ¿aplica load_factor y efficiency correctamente?
     - `StandbySettingsController` → ¿calcula horas ociosas bien?
     - `ReplacementAnalysisService` / `EquipmentReplacementService` → ¿usan consumo anual correcto?
   - Crear unit tests con casos del golden (valores esperados de Python)

5. **Validar en vistas**
   - Comparar métricas mostradas en:
     - `/economics` (gasto mensual, ahorro standby)
     - `/recommendations` (recomendaciones generadas)
     - `/invoices/{id}/snapshots` (consumo período)
   - Contra `golden_results.json` para confirmar que UI refleja cálculos correctos

**Ventajas del enfoque**:
- Golden dataset en Python sirve como source of truth
- JSON descargable permite iterar en Colab sin acceso a DB
- Comparación automatizada detecta regresiones
- Script Python puede evolucionar con lógica normalizada del usuario

**Próximos pasos**:
- Usuario implementa lógica Python normalizada (con factores, standby, recomendaciones de reemplazo)
- Pasa el script Python actualizado para alinear servicios Laravel
- Crea unit tests PHPUnit usando casos del golden (e.g., `test_equipment_276_matches_golden_kwh`)
- Opcional: comando artisan `test:golden` que corra automáticamente la comparación y falle si diff > umbral
