# 🚀 Guía de Optimización - ModoAhorro SaaS

> **Filosofía**: "Premature optimization is the root of all evil" — Donald Knuth  
> Esta guía te ayuda a optimizar **en el momento correcto**, con **métricas claras** y **retorno comprobable**.

---

## 📋 Índice

1. [¿Cuándo optimizar?](#cuándo-optimizar)
2. [Fases del proyecto y prioridades](#fases-del-proyecto)
3. [Quick Wins (bajo esfuerzo, alto impacto)](#quick-wins)
4. [Optimización de queries y base de datos](#optimización-de-queries)
5. [Optimización de cálculos del negocio](#optimización-de-cálculos)
6. [Optimización de frontend](#optimización-de-frontend)
7. [Monitoreo y métricas](#monitoreo-y-métricas)
8. [Checklist de auditoría pre-producción](#checklist-de-auditoría)
9. [Plan de escalamiento](#plan-de-escalamiento)

---

## ⏰ ¿Cuándo optimizar?

### ❌ NO optimices si:
- Todo carga en **<2 segundos**
- Tenés **<20 usuarios activos**
- Estás agregando **features core** (MVP)
- **No mediste** con profiler (no optimices por intuición)

### ✅ Optimizá YA si:
- Usuario espera **>5s** en alguna acción frecuente
- Logs muestran **>200 queries** en una página
- Memoria del servidor llega a **80%+** constantemente
- Vas a hacer una **demo comercial** importante
- Estás lanzando a **beta pública**

### 📊 Gatillos objetivos (números)

| Métrica | Umbral OK | Umbral Crítico | Acción |
|---------|-----------|----------------|--------|
| **TTFB** (Time To First Byte) | <300ms | >500ms | Optimizar backend |
| **Queries por request** | <50 | >100 | Eager loading + índices |
| **Memoria por request** | <64MB | >128MB | Reducir carga en memoria |
| **LCP** (Largest Contentful Paint) | <2.5s | >4s | Optimizar frontend |
| **Usuarios concurrentes** | <100 | >500 | Escalar horizontalmente |

---

## 📅 Fases del proyecto

### **Fase 1: MVP (0-3 meses)** ← ETAPA ACTUAL
**Prioridad**: Features, validación de flujo, UX  
**Optimización**: ❌ CERO (salvo bugs evidentes)  
**Métricas**: Cualitativas (¿el usuario entiende? ¿le sirve?)

**Reglas**:
- Escribí código legible, no código rápido
- Preferí simplicidad sobre performance
- Testeá flujos completos, no velocidad

---

### **Fase 2: Beta privada (3-6 meses)**
**Prioridad**: Estabilidad, primeros usuarios reales  
**Optimización**: ✅ Quick wins (1-2 días de trabajo)  
**Métricas**: Tiempo de carga de páginas clave, errores en logs

**Tareas clave**:
1. ✅ **Eager loading** en todos los controladores (arregla N+1)
2. ✅ **Índices básicos** en columnas frecuentes (company_id, entity_id, start_date)
3. ✅ **Cache** del Centro Económico (15 min)
4. ✅ `php artisan optimize` en producción

**Resultado esperado**: Dashboard carga en <1s, ajustes en <2s

---

### **Fase 3: Lanzamiento público (6-12 meses)**
**Prioridad**: Escalabilidad, conversión  
**Optimización**: ✅ Cacheo avanzado, queues, CDN  
**Métricas**: Uptime 99.9%, TTFB <300ms, error rate <0.1%

**Tareas clave**:
1. ✅ **Queue jobs** para tareas pesadas (emails, cálculos masivos)
2. ✅ **CDN** para assets estáticos (JS, CSS, imágenes)
3. ✅ **Redis** para cache + sesiones compartidas
4. ✅ **Cacheo de vistas** compiladas y queries pesadas

**Resultado esperado**: Soporta 100-500 usuarios concurrentes

---

### **Fase 4: Crecimiento (12+ meses)**
**Prioridad**: Retención, nuevas features, ROI  
**Optimización**: ✅ Horizontal scaling, microservicios (si es necesario)  
**Métricas**: Cost per user, infrastructure cost vs revenue

**Tareas clave**:
1. ✅ **Load balancer** + múltiples instancias Laravel
2. ✅ **DB read replicas** (lecturas distribuidas)
3. ✅ **Microservicios** para cálculos ML/análisis (opcional)
4. ✅ **Kubernetes** / auto-scaling (si >1000 usuarios activos)

---

## ⚡ Quick Wins

### 1. **Eager Loading (arregla N+1 queries)**

#### ❌ Antes (N+1 problem):
```php
$equipments = EntityEquipment::all(); // 1 query
foreach ($equipments as $eq) {
    echo $eq->entity->name; // N queries adicionales
}
// Total: 1 + N queries (ej. 1 + 50 = 51 queries)
```

#### ✅ Después:
```php
$equipments = EntityEquipment::with('entity')->get(); // 2 queries
foreach ($equipments as $eq) {
    echo $eq->entity->name; // ya cargado en memoria
}
// Total: 2 queries
```

#### 📍 Archivos a revisar:
- `app/Http/Controllers/UsageSettingsController.php` ✅ (ya tiene `with`)
- `app/Http/Controllers/EconomicsCenterController.php` ✅ (ya tiene `with`)
- `app/Http/Controllers/DashboardController.php` (revisar)
- `app/Http/Controllers/UsageSnapshotController.php` (revisar)

---

### 2. **Índices de base de datos**

#### Crear migración:
```bash
php artisan make:migration add_indexes_for_performance
```

#### Contenido de la migración:
```php
public function up(): void
{
    Schema::table('entity_equipment', function (Blueprint $table) {
        // Búsquedas por entidad y tipo
        $table->index(['entity_id', 'equipment_type_id'], 'idx_entity_type');
        
        // Filtros por compañía (vía relación)
        $table->index('entity_id', 'idx_entity');
    });
    
    Schema::table('invoices', function (Blueprint $table) {
        // Búsquedas por contrato y fecha
        $table->index(['contract_id', 'start_date'], 'idx_contract_period');
        $table->index('end_date', 'idx_end_date');
    });
    
    Schema::table('equipment_usage_snapshots', function (Blueprint $table) {
        // Búsquedas por factura y equipo
        $table->index(['invoice_id', 'entity_equipment_id'], 'idx_invoice_equipment');
        $table->index('entity_equipment_id', 'idx_equipment');
    });
    
    Schema::table('entities', function (Blueprint $table) {
        // Filtros por compañía
        $table->index('company_id', 'idx_company');
    });
}
```

#### Ejecutar:
```bash
php artisan migrate
```

**Ganancia esperada**: Queries de 200-500ms bajan a 5-20ms

---

### 3. **Cache de artisan en producción**

```bash
# Al deployar a producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# En desarrollo, si cambiás config/rutas, limpiar:
php artisan optimize:clear
```

**Ganancia**: +10-30% velocidad general en requests

---

## 🔍 Optimización de queries

### Herramientas de detección

#### Laravel Debugbar (desarrollo):
```bash
composer require barryvdh/laravel-debugbar --dev
```

En el dashboard verás:
- Cantidad de queries por request
- Tiempo de cada query
- Queries duplicadas (N+1)

#### Laravel Telescope (desarrollo/staging):
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

Accedé a `/telescope` para ver:
- Queries más lentas
- Requests más pesados
- Jobs fallidos

---

### Patrones comunes

#### 1. Select solo columnas necesarias
```php
// ❌ Trae todas las columnas
$invoices = Invoice::all();

// ✅ Trae solo lo necesario
$invoices = Invoice::select('id', 'start_date', 'end_date', 'total_amount')->get();
```

#### 2. Usar `pluck()` en lugar de `get()` para listas
```php
// ❌ Trae modelos completos
$entityIds = Entity::where('company_id', $companyId)->get()->pluck('id');

// ✅ Solo trae IDs
$entityIds = Entity::where('company_id', $companyId)->pluck('id');
```

#### 3. Lazy collections para datasets grandes
```php
// ❌ Carga todo en memoria (puede explotar con 10k+ registros)
$equipments = EntityEquipment::all();
foreach ($equipments as $eq) { /* procesar */ }

// ✅ Procesa de a uno, sin cargar todo
EntityEquipment::cursor()->each(function ($eq) {
    // procesar
});
```

---

## 🧮 Optimización de cálculos

### 1. **Centro Económico (cacheo de métricas)**

#### ❌ Antes (calcula siempre):
```php
public function index(Request $request)
{
    // ... cálculos pesados cada vez ...
    return view('economics.index', compact('metrics'));
}
```

#### ✅ Después (cache 15 min):
```php
public function index(Request $request)
{
    $user = auth()->user();
    $companyId = $user->company_id;
    
    $metrics = Cache::remember("economics.metrics.{$companyId}", 900, function() use ($companyId) {
        // Cálculos pesados (solo se ejecutan si no está en cache)
        $invoices = Invoice::whereHas('contract.supply.entity', fn($q) => 
            $q->where('company_id', $companyId)
        )->orderByDesc('end_date')->limit(3)->get();
        
        // ... resto de cálculos ...
        
        return [
            'monthly_cost_estimate' => $monthlyCostEstimate,
            'standby_savings' => $standbyPotentialSavings,
            // ...
        ];
    });
    
    return view('economics.index', compact('metrics', 'invoices', 'equipments'));
}
```

#### Invalidar cache al cambiar datos:
```php
// app/Observers/EntityEquipmentObserver.php
public function updated(EntityEquipment $equipment)
{
    Cache::forget("economics.metrics.{$equipment->entity->company_id}");
}

// app/Observers/InvoiceObserver.php (crear si no existe)
public function created(Invoice $invoice)
{
    $companyId = $invoice->contract->supply->entity->company_id;
    Cache::forget("economics.metrics.{$companyId}");
}
```

**Ganancia**: Primera carga igual, cargas subsiguientes instantáneas (0ms)

---

### 2. **Recomendaciones heurísticas (pre-cálculo)**

#### ✅ Implementación con cache:
```php
public function recommendations()
{
    $companyId = auth()->user()->company_id;
    
    return Cache::remember("usage.recommendations.{$companyId}", 3600, function() use ($companyId) {
        $equipments = EntityEquipment::whereHas('entity', fn($q) => 
            $q->where('company_id', $companyId)
        )->with('equipmentType.equipmentCategory')->get();
        
        $result = [];
        foreach ($equipments as $eq) {
            // ... cálculo heurístico ...
            $result[] = [ /* datos */ ];
        }
        
        return response()->json(['status' => 'ok', 'equipments' => $result]);
    });
}

// Invalidar al confirmar o aplicar recomendaciones:
public function confirm(Request $request)
{
    // ... código actual ...
    Cache::forget("usage.recommendations.{$user->company_id}");
}
```

---

### 3. **Snapshots (batch inserts + queue)**

#### ❌ Antes (inserts individuales):
```php
foreach ($equipments as $eq) {
    EquipmentUsageSnapshot::create([
        'entity_equipment_id' => $eq->id,
        'invoice_id' => $invoice->id,
        // ... campos ...
    ]);
}
// N inserts individuales (lento)
```

#### ✅ Después (batch insert):
```php
$snapshots = $equipments->map(function($eq) use ($invoice) {
    return [
        'entity_equipment_id' => $eq->id,
        'invoice_id' => $invoice->id,
        'calculated_kwh_period' => $this->calculateKwh($eq),
        'created_at' => now(),
        'updated_at' => now(),
        // ... campos ...
    ];
});

EquipmentUsageSnapshot::insert($snapshots->toArray());
// 1 insert masivo (10-50x más rápido)
```

#### ✅✅ Óptimo (si son >100 equipos, mover a queue):
```php
// En el controlador
dispatch(new CalculateSnapshotsJob($invoice, $equipments->pluck('id')));

return redirect()->back()->with('success', 'Cálculo iniciado. Te notificaremos al terminar.');

// app/Jobs/CalculateSnapshotsJob.php
public function handle()
{
    $equipments = EntityEquipment::whereIn('id', $this->equipmentIds)->get();
    
    $snapshots = $equipments->map(function($eq) {
        // ... cálculo ...
    });
    
    EquipmentUsageSnapshot::insert($snapshots->toArray());
}
```

**Ganancia**: No bloquea el request, usuario continúa usando la app

---

## 🎨 Optimización de frontend

### 1. **Vite build optimizado**

#### Producción:
```bash
npm run build
```

Esto genera:
- Bundle minificado + tree-shaking
- Code splitting automático
- Hash de archivos para cache busting

#### En `vite.config.js`:
```js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'alpine': ['alpinejs'],
                    'vendor': ['axios']
                }
            }
        }
    }
});
```

---

### 2. **Lazy loading de imágenes**
```blade
<img src="/img/logo.png" loading="lazy" alt="Logo">
```

---

### 3. **Minimizar Alpine.js en páginas pesadas**
```blade
<!-- ❌ Evitar lógica pesada en Alpine -->
<div x-data="{ items: @json($equipments) }">
    <!-- Si $equipments tiene 500 items, se embebe todo en HTML -->
</div>

<!-- ✅ Mejor: cargar vía fetch cuando sea necesario -->
<div x-data="heavyComponent()">
    <button @click="loadData()">Cargar datos</button>
</div>

<script>
function heavyComponent() {
    return {
        items: [],
        loadData() {
            fetch('/api/equipments')
                .then(r => r.json())
                .then(data => this.items = data);
        }
    };
}
</script>
```

---

## 📊 Monitoreo y métricas

### Herramientas de producción

#### 1. **Laravel Pulse** (built-in monitoring)
```bash
composer require laravel/pulse
php artisan pulse:install
php artisan migrate
```

Dashboard en `/pulse`:
- Requests más lentos
- Queries más pesadas
- Exceptions frecuentes
- Jobs fallidos

---

#### 2. **New Relic / Datadog** (APM profesional)
- Monitoreo real de usuarios
- Alertas automáticas si TTFB >500ms
- Análisis de queries lentas
- Trazabilidad end-to-end

---

#### 3. **MySQL Slow Query Log**
```bash
# En my.cnf / my.ini
slow_query_log = 1
long_query_time = 1  # Log queries >1s
```

Analizar:
```bash
mysqldumpslow /var/log/mysql/slow-query.log
```

---

### Métricas clave a trackear

| Métrica | Objetivo | Herramienta |
|---------|----------|-------------|
| **TTFB** | <300ms | Chrome DevTools, Lighthouse |
| **LCP** | <2.5s | Lighthouse, PageSpeed Insights |
| **Queries/request** | <50 | Laravel Debugbar, Telescope |
| **Error rate** | <0.1% | Logs, Sentry, New Relic |
| **Uptime** | >99.9% | UptimeRobot, Pingdom |

---

## ✅ Checklist de auditoría pre-producción

### 🔍 Backend
- [ ] Eager loading en todos los controladores principales
- [ ] Índices en columnas con WHERE/JOIN frecuentes
- [ ] `php artisan optimize` ejecutado
- [ ] Cache de métricas pesadas (>500ms)
- [ ] Queue configurado para emails/tareas pesadas
- [ ] Logs de errores monitoreados (Sentry)

### 🎨 Frontend
- [ ] `npm run build` ejecutado
- [ ] Assets servidos desde CDN (opcional)
- [ ] Imágenes con `loading="lazy"`
- [ ] Lighthouse score >80

### 🗄️ Base de datos
- [ ] Índices creados y testeados
- [ ] Slow query log activado
- [ ] Backups automáticos configurados

### 🔐 Seguridad y configuración
- [ ] `.env` con `APP_DEBUG=false` en producción
- [ ] `APP_KEY` generada y única
- [ ] HTTPS configurado
- [ ] CORS configurado si hay APIs

---

## 📈 Plan de escalamiento

### 🟢 Hasta 100 usuarios
**Infraestructura**: 1 servidor (app + DB en mismo host)  
**Configuración**: Básica (shared hosting o VPS pequeño)  
**Optimizaciones**: Índices + eager loading

---

### 🟡 100-500 usuarios
**Infraestructura**: App y DB en servidores separados  
**Configuración**:
- Redis para cache + sesiones
- Queue workers (2-3 procesos)
- CDN para assets

**Comando queue workers**:
```bash
php artisan queue:work --tries=3 --timeout=90
```

---

### 🟠 500-2000 usuarios
**Infraestructura**: Load balancer + 2-3 app servers + DB master  
**Configuración**:
- Redis cluster
- DB read replicas
- Supervisor para queue workers
- Auto-scaling (AWS/DigitalOcean)

---

### 🔴 2000+ usuarios
**Infraestructura**: Kubernetes / microservicios  
**Configuración**:
- Load balancer con auto-scaling horizontal
- DB master + múltiples read replicas
- Separación de servicios críticos (ej. cálculos ML en Python)
- CDN global (Cloudflare, AWS CloudFront)

---

## 🎯 Próximos pasos para ModoAhorro

### Ahora (MVP - Fase 1)
✅ Nada. Seguir con features.

### Pre-Beta (en 1-2 meses)
1. [ ] Audit de eager loading en todos los controladores
2. [ ] Crear índices con la migración sugerida
3. [ ] Cache del Centro Económico (15 min)
4. [ ] `php artisan optimize` en servidor de staging

### Pre-Lanzamiento (en 3-6 meses)
1. [ ] Queue para aplicar recomendaciones masivas
2. [ ] CDN para assets (`/build`, `/img`)
3. [ ] Redis para cache + sesiones
4. [ ] Monitoreo con Laravel Pulse

### Crecimiento (6+ meses)
1. [ ] Horizontal scaling si >500 usuarios concurrentes
2. [ ] DB read replicas
3. [ ] Análisis de cost per user vs infrastructure cost

---

## 📚 Recursos adicionales

- [Laravel Performance Best Practices](https://laravel.com/docs/performance)
- [Database Query Performance](https://laravel.com/docs/queries#query-builder)
- [Laravel Horizon (queue monitoring)](https://laravel.com/docs/horizon)
- [Lighthouse CI (automated audits)](https://github.com/GoogleChrome/lighthouse-ci)

---

**Última actualización**: 9 de noviembre de 2025  
**Versión**: 1.0  
**Autor**: Equipo ModoAhorro
