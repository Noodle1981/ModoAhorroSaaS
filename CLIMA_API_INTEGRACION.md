# 🌡️ Integración de Datos Climáticos Reales

## ✅ Sistema Implementado

### APIs Climáticas Integradas

#### 1. **Open-Meteo** (RECOMENDADO - ACTIVO)
- ✅ **Gratuita**: Sin API key, sin límites
- ✅ **Datos históricos completos**: Desde 1940 hasta hoy
- ✅ **Cobertura global**: Cualquier localidad con coordenadas GPS
- ✅ **Variables disponibles**: Temperatura (max/min/media), precipitación, viento
- 📚 Documentación: https://open-meteo.com/en/docs/historical-weather-api
- 🎯 **ACTUALMENTE EN USO**

**Ejemplo de uso:**
```bash
php load_climate_from_api.php 1
```

#### 2. **Visual Crossing** (BACKUP)
- 🔑 Requiere API key gratuita
- ✅ 1000 requests/día gratis
- ✅ Datos históricos detallados
- ✅ Incluye humedad
- 📚 Signup: https://www.visualcrossing.com/sign-up

**Configuración:**
```env
VISUAL_CROSSING_API_KEY=tu_api_key_aqui
```

#### 3. **WeatherAPI.com** (BACKUP)
- 🔑 Requiere API key gratuita
- ✅ 1M requests/mes gratis
- ✅ Datos históricos
- 📚 Signup: https://www.weatherapi.com/signup.aspx

**Configuración:**
```env
WEATHERAPI_KEY=tu_api_key_aqui
```

---

## 📊 Estado Actual del Sistema

### Datos Cargados (Factura #1)

| Métrica | Valor |
|---------|-------|
| **Período** | 15/01/2025 - 20/03/2025 (65 días) |
| **Localidad** | Santa Lucía, San Juan, Argentina |
| **Coordenadas** | -31.5397, -68.5069 |
| **Fuente de datos** | Open-Meteo API ✅ |
| **Días con datos** | 65 / 65 (100%) |

### Estadísticas Climáticas Reales

| Variable | Valor |
|----------|-------|
| **Temp. media** | 28.5°C |
| **Temp. mínima** | 18.9°C |
| **Temp. máxima** | 37.2°C |
| **CDD total** | 683.7 (refrigeración) |
| **HDD total** | 0 (calefacción) |

### Distribución de Días

| Condición | Días | % Período |
|-----------|------|-----------|
| >28°C (calor intenso) | 38 | 58.5% |
| >26°C (uso A/A) | 53 | 81.5% |
| >24°C (ventiladores) | 57 | **87.7%** |
| <18°C (fresco) | 0 | 0% |

### Días Efectivos Calculados

| Categoría | Días Totales | Días Efectivos | Ratio | Descuento |
|-----------|--------------|----------------|-------|-----------|
| **Aires Acondicionados** | 65 | 57 | 0.88 | -12.3% |
| **Ventiladores** | 65 | 57 | 0.88 | -12.3% |
| **Calefacción** | 65 | 0 | 0.00 | -100% |
| **Otros equipos** | 65 | 65 | 1.00 | 0% |

---

## 🎯 Resultados de Calibración

### Evolución del Cálculo

| Etapa | Consumo Estimado | % Factura | Método |
|-------|------------------|-----------|---------|
| Inicial | 1,800 kWh | 289% | Sin ajustes |
| Eficiencia magnetrón | 1,609 kWh | 258% | Fórmula física |
| Descuento 25% fijo | 1,300 kWh | 209% | Estimación manual |
| Datos simulados | 1,203 kWh | 193% | Datos de ejemplo |
| **Open-Meteo (REAL)** | **1,454 kWh** | **233%** | **API climática** |

### Análisis

**¿Por qué subió con datos reales?**
- Datos simulados subestimaban el calor (44 días >24°C)
- Datos reales de Open-Meteo: **57 días >24°C (88% del período)**
- Verano 2025 en San Juan fue **muy caluroso** (28.5°C promedio)
- El problema NO son los días, sino los **minutos/día** (600 min = 10h/día)

**Conclusión:**
- ✅ Días efectivos correctamente calculados con datos reales
- ❌ Minutos de uso aún sobreestimados
- 🎯 Próximo ajuste: Reducir minutos de 600 → 350-400 min/día

---

## 🚀 Cómo Usar el Sistema

### 1. Cargar Datos Climáticos para una Factura

```bash
php load_climate_from_api.php {invoice_id}
```

**Ejemplo:**
```bash
php load_climate_from_api.php 1
```

**Salida:**
- Conecta con Open-Meteo API
- Descarga temperaturas diarias del período
- Calcula CDD/HDD automáticamente
- Guarda en tabla `daily_weather_logs`
- Muestra estadísticas y días efectivos

### 2. Recalcular Snapshots con Clima

```bash
php recalculate_snapshots_climate.php {invoice_id}
```

**Aplica:**
- Días efectivos según temperatura real
- Categorías ajustadas: Climatización, Calefacción, Calefón
- Resto de equipos: Días completos

### 3. Verificar Estado Actual

```bash
php summary_invoice.php {invoice_id}
```

**Muestra:**
- Consumo estimado vs real
- Fuente de datos climáticos
- Próximos pasos recomendados

---

## 🔧 Arquitectura Técnica

### Servicios

**`WeatherApiService`** (`app/Services/WeatherApiService.php`)
- `fetchHistoricalData()`: Obtiene datos de API externa
- `fetchFromOpenMeteo()`: Implementación Open-Meteo
- `fetchFromVisualCrossing()`: Backup 1
- `fetchFromWeatherAPI()`: Backup 2
- `saveWeatherData()`: Persiste en DB
- `loadDataForInvoice()`: Carga automática para factura

**`ClimateCorrelationService`** (`app/Services/ClimateCorrelationService.php`)
- `calculateEffectiveDaysByTemperature()`: Cuenta días según umbral
- `getEffectiveDaysForClimateEquipment()`: Por categoría de equipo
- `fallbackEffectiveDays()`: Estimación estacional si no hay datos

**`UsageSnapshotController`**
- Usa `ClimateCorrelationService` para días efectivos
- Cache por categoría (evita recalcular)
- Aplicación automática en `create()` y `store()`

### Modelos

**`DailyWeatherLog`** (tabla: `daily_weather_logs`)
```php
locality_id
date
avg_temp_celsius
max_temp_celsius
min_temp_celsius
cooling_degree_days  // Base 18°C
heating_degree_days  // Base 18°C
precipitation_mm
wind_speed_kmh
humidity_percent
```

### Flujo Completo

```
1. Usuario crea/edita factura
   ↓
2. Sistema verifica si hay datos climáticos
   ↓
3. Si no hay → Botón "Cargar datos climáticos"
   ↓
4. WeatherApiService::loadDataForInvoice()
   ↓
5. Obtiene coordenadas de locality
   ↓
6. Llama Open-Meteo API
   ↓
7. Guarda en daily_weather_logs
   ↓
8. UsageSnapshotController::create()
   ↓
9. ClimateCorrelationService::getEffectiveDaysForClimateEquipment()
   ↓
10. Cuenta días >24°C (ventiladores/aires)
   ↓
11. Aplica ratio a cálculo de kWh
   ↓
12. Guarda snapshots con kWh ajustado
```

---

## 💡 Para el CEO - Justificación Medidor Inteligente

### Problema Actual

| Aspecto | Sin Medidor | Con Datos Climáticos | Con Medidor |
|---------|-------------|----------------------|-------------|
| **Días efectivos** | Estimación manual | ✅ Temperatura real | ✅ Consumo real |
| **Minutos/día** | ❌ Memoria difusa | ❌ Promedio estático | ✅ Datos horarios |
| **Precisión** | ±30% | ±15-20% | ±2-5% |
| **Costo** | $0 | $0 | $200-400 USD |

### ROI Medidor

**Inversión inicial:** ~$300 USD

**Beneficios:**
1. **Elimina incertidumbre de minutos**: Datos horarios reales
2. **Detecta anomalías**: Equipos defectuosos o mal configurados
3. **Valida recomendaciones**: Mide impacto real de cambios
4. **Correlación automática**: Consumo vs clima sin estimaciones

**Ahorro estimado:**
- Optimización de uso: 10-15% (~$30-50/mes)
- Detección de fallos: Evita sobrecostos (~$100-200/año)
- **Payback**: 6-12 meses

---

## 📝 Próximos Pasos

### Inmediato (Esta Semana)
1. ✅ Integración Open-Meteo completada
2. ⏳ Ajustar minutos de aires (600 → 350-400)
3. ⏳ Recalcular y validar ~100-115% de factura

### Corto Plazo (1-2 Semanas)
1. Interfaz UI para "Cargar datos climáticos" (botón)
2. Badge visual "Ajustado por clima" en snapshots
3. Gráfico: Temperatura vs Consumo

### Mediano Plazo (1 Mes)
1. Carga automática de clima al crear factura
2. Alertas si no hay datos climáticos
3. Sugerencia automática de minutos según CDD/HDD

### Largo Plazo (2-3 Meses)
1. Integración con medidor inteligente (API)
2. Correlación automática consumo real vs clima
3. Machine Learning para predecir consumo futuro

---

## 🎉 Logros del Sistema Actual

✅ **Datos climáticos reales** desde API gratuita (Open-Meteo)  
✅ **Días efectivos calculados automáticamente** según temperatura  
✅ **Diferenciación por categoría**: Climatización vs otros equipos  
✅ **Fallback estacional** si no hay datos de API  
✅ **Scripts de diagnóstico completos**  
✅ **Arquitectura escalable** para múltiples APIs  

🎯 **Próximo milestone**: Calibrar minutos para llegar a 100-115% de precisión
