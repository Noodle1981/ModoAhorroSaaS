# ⚡ ModoAhorroSaaS - Plataforma de Optimización Energética Inteligente

<div align="center">

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-06B6D4?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)

**Plataforma SaaS que ayuda a usuarios y empresas a comprender, analizar y optimizar su consumo energético mediante inventario inteligente de equipos, análisis de facturas y recomendaciones automatizadas.**

[Demo](http://127.0.0.1:8000) • [Documentación](PROYECTO_ANALISIS_Y_TESTING.md) • [Propuesta IoT](PROPUESTA_INTEGRACION_TERAOBJECT.md)

</div>

---

## 🎯 Problema que Resuelve

**El desafío**: Los usuarios reciben facturas eléctricas altas pero no saben **por qué** consumen tanto ni **dónde** se va su dinero.

**La solución**: ModoAhorroSaaS permite:
- 📊 **Inventariar equipos eléctricos** con estimaciones de consumo
- 🔍 **Comparar consumo real vs estimado** para identificar discrepancias
- ⚙️ **Ajustar parámetros en tiempo real** mediante snapshots interactivos
- 💡 **Recibir recomendaciones** con ROI calculado para optimizar gastos
- 🏭 **Simular cambios** antes de realizarlos (Gemelo Digital)

---

## ✨ Features Destacadas

### 🎨 Dashboard Inteligente
- Métricas globales: consumo, costo, tendencias
- Gráficos de evolución temporal (últimos 6 meses)
- Top 10 equipos por consumo estimado
- Distribución por entidad
- Responsive design con Tailwind CSS 4.0

### 📦 Inventario Dinámico de Equipos
- **Categorías jerárquicas**: Climatización, Iluminación, Electrodomésticos, etc.
- **Cálculo automático**: Consumo activo + standby (opcional)
- **Campos condicionales**: Se adaptan según tipo de equipo
- **Personalización**: Override de potencia y minutos de uso

### 🎯 Análisis de Período Activo (Feature Única)
- Compara **consumo real** (factura) vs **consumo estimado** (inventario)
- Calcula **% explicado** del consumo
- Identifica equipos "ocultos" o mal estimados
- **Ajuste interactivo con Alpine.js**: Cambia minutos de uso y ve impacto en tiempo real

### 🔧 Snapshots Ajustables (Diferenciador Clave)

**Vista de Edición** (`/invoices/{invoice}/snapshots/create`):
- Ajuste interactivo de minutos de uso por equipo con slider +/-
- Formato intuitivo de tiempo: "7 hrs 50 min" (automático para valores ≥ 60 min)
- Filtrado dinámico por ubicación, categoría y búsqueda en tiempo real
- Ocultar equipos sin uso (0 min) para enfoque en activos
- Distribución visual por ambiente basada en filtros aplicados
- Cálculo instantáneo de impacto en consumo (kWh y %)
- Alpine.js para reactividad sin recarga de página

**Vista de Resumen** (`/invoices/{invoice}/snapshots`):
- Dashboard consolidado con 3 métricas clave (real kWh, estimado kWh, equipos activos)
- Distribución por ambiente con barras de progreso y porcentajes
- Tabla detallada de equipos con consumo calculado por período
- Acceso directo a ajustar desde resumen

```javascript
// Ejemplo de formato automático de tiempo
formatMinutes(470) // → "7 hrs 50 min"
formatMinutes(45)  // → "45 min"
formatMinutes(180) // → "3 hrs"
```

### 🤖 Motor de Recomendaciones
- Analiza equipos con mayor consumo
- Sugiere reemplazos eficientes
- Calcula ROI y payback period
- Listo para integrar con Marketplace (Mercado Libre/Amazon)

### 🏭 Gemelo Digital (Digital Twin)
- Consolida datos de entidad, equipos, facturas, clima
- **Simulador**: "¿Qué pasaría si cambio X equipo?"
- Comparación de múltiples escenarios
- Recomendaciones priorizadas por ahorro

---

## 🏗️ Arquitectura

### Multi-Tenancy
```
Company (Tenant)
├── Users (roles: owner, member)
├── Subscription (Plan limits)
└── Entities (Propiedades)
    ├── Supplies (Puntos de suministro CUPS)
    │   └── Contracts
    │       └── Invoices (Consumo real)
    ├── Equipments (Inventario)
    │   └── Snapshots (Ajustes por período)
    ├── Solar Installations
    └── Recommendations
```

### Navegación de Dashboards

**Nivel 1: Dashboard General (`/dashboard`)**
- Vista global de todas las entidades del usuario
- Métricas consolidadas: consumo, costo, tendencias
- Gráficos de evolución y distribución
- Acceso rápido a entidades específicas

**Nivel 2: Dashboard de Entidad (`/entities/{id}`)**
- Análisis detallado de una propiedad específica
- Sistema de tarjetas grandes por suministro:
  - Consumo real vs estimado con % explicado
  - Gráfico de evolución de últimas 6 facturas
  - Medidor circular de relación real/estimado
  - Top 5 equipos por consumo estimado
  - Distribución por categoría
- Gestión de suministros, contratos y facturas
- Inventario de equipos con análisis de consumo
- CTA directo: "Ajustar Distribución del Consumo" para período activo

**Nivel 3: Gestión de Snapshots**
- Editor interactivo (`/invoices/{invoice}/snapshots/create`): Ajuste de minutos por equipo con visualización en tiempo real
- Resumen consolidado (`/invoices/{invoice}/snapshots`): Dashboard de snapshots guardados con métricas y distribución

**Redirección Inteligente**: Usuarios con 1 sola entidad → Dashboard de entidad directamente

## ✅ Estado del Proyecto

### MVP Completo (v1.0)
- ✅ **Core Features**
  - [x] Autenticación y gestión de usuarios
  - [x] Multi-tenancy con Companies
  - [x] CRUD completo: Entities, Supplies, Contracts, Invoices
  - [x] Inventario dinámico de equipos (28 categorías)
  - [x] Dashboard general responsivo
  - [x] Dashboard de entidad con tarjetas grandes por suministro
  
- ✅ **Análisis Avanzado**
  - [x] Comparación consumo real vs estimado
  - [x] Cálculo de % explicado del consumo
  - [x] Análisis de períodos históricos
  - [x] Medidor circular de relación consumo
  - [x] Gráficos de evolución (últimas 6 facturas)
  - [x] Top 5 equipos y distribución por categoría
  
- ✅ **Snapshots Ajustables** (Feature Única)
  - [x] Editor interactivo con slider +/-
  - [x] Formato automático de tiempo (hrs/min)
  - [x] Filtrado dinámico por ubicación/categoría/búsqueda
  - [x] Distribución visual por ambiente (basada en filtros)
  - [x] Cálculo instantáneo de impacto en kWh
  - [x] Vista de resumen consolidado con métricas
  - [x] UX optimizada: layout estable sin "tambaleo"
  
- ✅ **Servicios de Negocio**
  - [x] `InventoryAnalysisService` - Perfiles de consumo
  - [x] `ReplacementAnalysisService` - Oportunidades de mejora
  - [x] `DigitalTwinService` - Simulador de escenarios
  
- ✅ **UX/UI**
  - [x] Diseño responsivo (mobile/tablet/desktop)
  - [x] Tailwind CSS 4.0 con paleta consistente
  - [x] Alpine.js para interactividad
  - [x] Font Awesome 6.4.0 icons
  - [x] Flujo guiado (sin callejones sin salida)

### 🟡 En Desarrollo
- [ ] Tests automatizados (Unit + Feature)
- [ ] Maintenance module (parcial)
- [ ] Solar dashboard
- [ ] Recommendations engine

### 🔴 Roadmap Futuro
- [ ] Integración IoT (Teraobject) - [Ver propuesta](PROPUESTA_INTEGRACION_TERAOBJECT.md)
- [ ] Correlación con clima (Weather API)
- [ ] Marketplace de productos eficientes
- [ ] Machine Learning para predicciones
- [ ] Gemelo digital con visualización 3D

## 🗂️ Modelos Principales

```php
// Core Business Models
User → Company → [Subscription, Entities]
Entity → [Supplies, Equipments, Recommendations, SolarInstallations]
Supply → Contracts → Invoices
EntityEquipment → [EquipmentType, Snapshots, MaintenanceLogs]

// Catálogo
EquipmentCategory → EquipmentType → CalculationFactor
UtilityCompany → Rates → RatePrices
Province → Locality

// Análisis y Optimización
Recommendation → (trigger_rules JSON)
EquipmentUsageSnapshot → (ajustes por período)
ConsumptionReading → (lecturas smart meter)
DailyWeatherLog → (correlación climática)
```

**Total: 28 modelos** - [Ver análisis completo](PROYECTO_ANALISIS_Y_TESTING.md)

## 🚀 Quick Start

### Requisitos Previos
- PHP 8.2+
- Composer 2.x
- Node.js 18+ y npm
- SQLite 3 (o PostgreSQL para producción)

### Instalación

```bash
# 1. Clonar repositorio
git clone <URL_DEL_REPOSITORIO>
cd ModoAhorroSaaS

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Crear base de datos SQLite
touch database/database.sqlite

# 5. Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# 6. Compilar assets y arrancar servidor
npm run dev
# En otra terminal:
php artisan serve
```

**Listo!** Visita `http://127.0.0.1:8000`

### Datos de Demo

Después del seeder, puedes crear un usuario de prueba:
```bash
php artisan tinker
User::factory()->create(['email' => 'demo@modoahorro.com', 'password' => bcrypt('password')]);
```

---

## 📸 Screenshots

### Dashboard General
![Dashboard](docs/screenshots/dashboard.png)
*Vista consolidada con métricas, gráficos y top equipos*

### Análisis de Entidad
![Entity Analysis](docs/screenshots/entity-show.png)
*Comparación consumo real vs estimado con % explicado*

### Snapshots Ajustables (Feature Única)
![Snapshots](docs/screenshots/snapshots-adjust.png)
*Ajuste interactivo de minutos de uso con recalculo en tiempo real*

---

## 🛠️ Stack Tecnológico

### Backend
- **Framework**: Laravel 12.x (PHP 8.2+)
- **ORM**: Eloquent con relaciones complejas (hasManyThrough, morphMany)
- **Arquitectura**: Service Layer pattern (InventoryAnalysisService, ReplacementAnalysisService, DigitalTwinService, WeatherService)
- **Controllers**: RESTful con Resource Controllers (UsageSnapshotController: create, store, show)
- **Policies**: Authorization granular por modelo
- **External APIs**: Open-Meteo (datos climáticos históricos gratuitos)

### Frontend
- **CSS Framework**: Tailwind CSS 4.0 con utilidades personalizadas
- **Interactividad**: Alpine.js 3.x para reactividad sin build step
- **UI Components**: Font Awesome 6.4.0, sliders personalizados, medidores circulares
- **Responsive Design**: Mobile-first con breakpoints sm/md/lg/xl
- **Build Tool**: Vite 5.x para hot module replacement

## 🌡️ Integración Climática

La plataforma correlaciona consumo energético con condiciones climáticas mediante **Open-Meteo API**:

### Características
- 📊 **Datos históricos**: Temperatura desde 1950 hasta hoy
- 🌍 **Cobertura global**: Modelos de alta resolución para cualquier coordenada
- 🆓 **Gratuito**: Sin clave API ni límites para uso no comercial
- 📈 **Grados-día**: Cálculo automático de HDD (heating) y CDD (cooling)

### Uso

```bash
# Actualizar clima para todas las localidades con coordenadas
php artisan weather:update --all

# Actualizar clima para una localidad específica
php artisan weather:update --locality="Santa Lucía"

# Especificar rango de fechas
php artisan weather:update --locality="Santa Lucía" --start=2024-01-01 --end=2024-12-31
```

### Integración en Snapshots
Al ajustar el consumo de equipos por período, la vista muestra:
- Temperatura promedio, máxima y mínima del período
- Grados-día de refrigeración/calefacción
- Sugerencias contextuales basadas en clima (ej: "Período cálido, considera mayor uso de AC")

Esto permite al usuario correlacionar picos de consumo con condiciones climáticas extremas.
## 🧪 Testing

La guía completa y plan de pruebas (unitarias, de features, cálculos y flujos críticos) se movió a `TESTING.md`.

Acceso rápido:
- `TESTING.md` para casos, estrategias y checklist por release.
- `PROYECTO_ANALISIS_Y_TESTING.md` para análisis general del proyecto.

Comandos básicos:
```bash
php artisan test              # Ejecutar suite completa
php artisan test --filter=InventoryAnalysisTest  # Test específico
php artisan test --coverage   # Con reporte de cobertura
```

Estado: iniciando cobertura sobre cálculos principales y flujos de standby/uso.

---

## 📚 Documentación

- **[PROYECTO_ANALISIS_Y_TESTING.md](PROYECTO_ANALISIS_Y_TESTING.md)**: Análisis completo de arquitectura, módulos, roadmap y plan de testing
- **[PROPUESTA_INTEGRACION_TERAOBJECT.md](PROPUESTA_INTEGRACION_TERAOBJECT.md)**: Propuesta de integración con IoT, correlación climática, marketplace y gemelo digital

---

## 🎯 Casos de Uso

### 1. Usuario Residencial
María quiere saber por qué su factura de €180/mes es tan alta:
1. Crea cuenta y registra su hogar
2. Ingresa factura de último mes (500 kWh)
3. Inventarea sus equipos (heladera, TV, AC, etc.)
4. **Resultado**: El sistema le muestra que su heladera de 15 años consume 40% del total
5. **Recomendación**: Modelo eficiente de €650 ahorra €65/mes → Payback 10 meses

### 2. Pequeño Comercio
Juan tiene local con consumo variable:
1. Registra su comercio y equipos
2. Sube facturas y observa discrepancia: consume 800 kWh pero inventario estima 500 kWh
3. Usa editor de snapshots para ajustar minutos reales de uso por equipo
4. **Resultado**: Descubre que AC industrial funciona 12 hrs/día (no 8 hrs como creía)
5. **Vista de resumen**: Confirma que ajuste explica el 95% del consumo real
6. **Acción**: Negocia tarifa nocturna y optimiza horarios → Ahorro 25%

### 3. Gestor Energético (Futuro)
Empresa gestiona 50+ edificios:
1. Multi-tenancy con subscripción Enterprise
2. Dashboard consolidado de todas las propiedades
3. Alertas automáticas de consumo anormal
4. Recomendaciones priorizadas por ROI

---

## 🤝 Contribuir

Este es un MVP en desarrollo activo. Contribuciones bienvenidas en:
- Tests automatizados
- Mejoras de UX/UI
- Optimizaciones de performance
- Documentación

---

## 📄 Licencia

Propietario - Todos los derechos reservados

---

## 👤 Autor

**Desarrollador Full Stack** especializado en soluciones energéticas

- LinkedIn: [Tu perfil]
- Email: tu@email.com
- Portfolio: [tu-sitio.com]

---

## 🙏 Agradecimientos

- **Teraobject**: Partner potencial para integración IoT
- **Laravel Community**: Framework robusto y bien documentado
- **Tailwind Labs**: CSS framework que agiliza el desarrollo

---

<div align="center">
  <strong>⚡ Construyendo el futuro de la gestión energética inteligente ⚡</strong>
</div> Esto creará la estructura de la base de datos y la llenará con datos de catálogo iniciales.
    ```bash
    php artisan migrate:fresh --seed
    ```

6.  **Compilar assets y arrancar el servidor de desarrollo:**
    ```bash
    npm run dev & php artisan serve
    ```

La aplicación estará disponible en `http://127.0.0.1:8000`.

## Ejecución de Pruebas

Para ejecutar el conjunto de pruebas automatizadas, utiliza el siguiente comando:

```bash
php artisan test
```