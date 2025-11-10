"""
Golden Test Calculator - ModoAhorroSaaS
Reproducción de cálculos de consumo, standby y costos para validación cruzada.

Uso:
1. Copiar equipments.json e invoices.json desde storage/app/private/exports/
2. Ejecutar este script en Colab o local
3. Comparar resultados con lo que muestra Laravel

Estructura:
- Cargar JSON de equipos y facturas
- Calcular consumo activo anual (con load_factor y efficiency_factor)
- Calcular consumo standby anual (si aplica)
- Calcular costo anual por equipo
- Generar recomendaciones (standby, reemplazo, frecuencia)
- Exportar resultados esperados para comparar con Laravel
"""

import json
from pathlib import Path
from typing import Dict, List, Any

# ==========================
# 1. CARGA DE DATOS
# ==========================

def load_json(path: str) -> Dict:
    """Carga un archivo JSON desde la ruta especificada."""
    with open(path, 'r', encoding='utf-8') as f:
        return json.load(f)

# Reemplaza estas rutas con tus archivos descargados
# Si corres desde la raíz del proyecto, usa rutas relativas:
EQUIPMENTS_PATH = 'storage/app/private/exports/equipments.json'
INVOICES_PATH = 'storage/app/private/exports/invoices.json'

# Si corres desde Colab o descargaste los JSON localmente, usa:
# EQUIPMENTS_PATH = 'equipments.json'
# INVOICES_PATH = 'invoices.json'

equipments_data = load_json(EQUIPMENTS_PATH)
invoices_data = load_json(INVOICES_PATH)

equipments = equipments_data['equipments']
invoices = invoices_data['invoices']

print(f"Equipos cargados: {len(equipments)}")
print(f"Facturas cargadas: {len(invoices)}")

# ==========================
# 2. CÁLCULO DE CONSUMO ACTIVO
# ==========================

def calculate_active_consumption(equipment: Dict) -> float:
    """
    Calcula el consumo activo anual en kWh.
    
    Fórmula:
    kWh_año = (potencia_kW * horas_año * load_factor / efficiency_factor) * cantidad
    
    Args:
        equipment: Diccionario con datos del equipo
    
    Returns:
        Consumo activo anual en kWh
    """
    kw = equipment['nominal_kw']
    hours_year = equipment['calculated']['hours_per_year']
    load_factor = equipment['category']['calculation_factor']['load_factor']
    efficiency = equipment['category']['calculation_factor']['efficiency_factor']
    quantity = equipment['quantity']
    
    # Evitar división por cero
    if efficiency == 0:
        efficiency = 1.0
    
    kwh_year = (kw * hours_year * load_factor / efficiency) * quantity
    return round(kwh_year, 2)

# ==========================
# 3. CÁLCULO DE CONSUMO STANDBY
# ==========================

def calculate_standby_consumption(equipment: Dict) -> float:
    """
    Calcula el consumo standby anual en kWh.
    
    Fórmula:
    horas_standby_año = (24 * 365) - horas_activas_año
    kWh_standby_año = (standby_watts / 1000) * horas_standby_año * cantidad
    
    Args:
        equipment: Diccionario con datos del equipo
    
    Returns:
        Consumo standby anual en kWh, o 0 si no tiene standby
    """
    if not equipment['has_standby_mode']:
        return 0.0
    
    standby_watts = equipment['type']['standby_power_watts']
    hours_active_year = equipment['calculated']['hours_per_year']
    hours_standby_year = max(0, (24 * 365) - hours_active_year)
    quantity = equipment['quantity']
    
    kwh_standby_year = (standby_watts / 1000.0) * hours_standby_year * quantity
    return round(kwh_standby_year, 2)

# ==========================
# 4. CÁLCULO DE COSTO ANUAL
# ==========================

def get_avg_tariff(invoices: List[Dict]) -> float:
    """
    Calcula la tarifa promedio ponderada de todas las facturas.
    
    Returns:
        Tarifa promedio en $/kWh
    """
    total_kwh = sum(inv['consumption']['total_kwh'] for inv in invoices if inv['consumption']['total_kwh'] > 0)
    total_amount = sum(inv['consumption']['total_amount'] for inv in invoices if inv['consumption']['total_kwh'] > 0)
    
    if total_kwh == 0:
        return 0.0
    
    return round(total_amount / total_kwh, 4)

def calculate_annual_cost(total_kwh_year: float, avg_tariff: float) -> float:
    """
    Calcula el costo anual estimado.
    
    Args:
        total_kwh_year: Consumo total anual en kWh
        avg_tariff: Tarifa promedio en $/kWh
    
    Returns:
        Costo anual en $
    """
    return round(total_kwh_year * avg_tariff, 2)

# ==========================
# 5. PROCESAMIENTO
# ==========================

avg_tariff = get_avg_tariff(invoices)
print(f"\nTarifa promedio calculada: ${avg_tariff:.4f}/kWh")

results = []

for eq in equipments:
    active_kwh = calculate_active_consumption(eq)
    standby_kwh = calculate_standby_consumption(eq)
    total_kwh = active_kwh + standby_kwh
    annual_cost = calculate_annual_cost(total_kwh, avg_tariff)
    
    results.append({
        'equipment_id': eq['equipment_id'],
        'entity_name': eq['entity']['name'],
        'type': eq['type']['name'],
        'category': eq['category']['name'],
        'quantity': eq['quantity'],
        'power_kw': eq['nominal_kw'],
        'hours_per_year': eq['calculated']['hours_per_year'],
        'load_factor': eq['category']['calculation_factor']['load_factor'],
        'efficiency_factor': eq['category']['calculation_factor']['efficiency_factor'],
        # Resultados calculados por Python
        'active_kwh_year_python': active_kwh,
        'standby_kwh_year_python': standby_kwh,
        'total_kwh_year_python': total_kwh,
        'annual_cost_python': annual_cost,
        # Valores que Laravel calculó (para comparar)
        'active_kwh_year_laravel': eq['calculated']['active_kwh_year'],
        'standby_kwh_year_laravel': eq['calculated']['standby_kwh_year'] or 0.0,
    })

# ==========================
# 6. COMPARACIÓN Y DIFERENCIAS
# ==========================

print("\n" + "="*80)
print("COMPARACIÓN PYTHON vs LARAVEL")
print("="*80)

total_active_python = sum(r['active_kwh_year_python'] for r in results)
total_active_laravel = sum(r['active_kwh_year_laravel'] for r in results)
total_standby_python = sum(r['standby_kwh_year_python'] for r in results)
total_standby_laravel = sum(r['standby_kwh_year_laravel'] for r in results)

print(f"\nConsumo activo total:")
print(f"  Python:  {total_active_python:,.2f} kWh/año")
print(f"  Laravel: {total_active_laravel:,.2f} kWh/año")
print(f"  Diferencia: {abs(total_active_python - total_active_laravel):,.2f} kWh ({abs(total_active_python - total_active_laravel) / max(total_active_python, 1) * 100:.2f}%)")

print(f"\nConsumo standby total:")
print(f"  Python:  {total_standby_python:,.2f} kWh/año")
print(f"  Laravel: {total_standby_laravel:,.2f} kWh/año")
print(f"  Diferencia: {abs(total_standby_python - total_standby_laravel):,.2f} kWh ({abs(total_standby_python - total_standby_laravel) / max(total_standby_python, 1) * 100:.2f}%)")

# Detectar diferencias por equipo
print("\n" + "="*80)
print("EQUIPOS CON DIFERENCIAS > 5%")
print("="*80)

for r in results:
    if r['active_kwh_year_laravel'] > 0:
        diff_pct = abs(r['active_kwh_year_python'] - r['active_kwh_year_laravel']) / r['active_kwh_year_laravel'] * 100
        if diff_pct > 5:
            print(f"\n#{r['equipment_id']} - {r['type']} ({r['category']})")
            print(f"  Python:  {r['active_kwh_year_python']:.2f} kWh/año")
            print(f"  Laravel: {r['active_kwh_year_laravel']:.2f} kWh/año")
            print(f"  Diferencia: {diff_pct:.2f}%")
            print(f"  Load factor: {r['load_factor']}, Efficiency: {r['efficiency_factor']}")

# ==========================
# 7. EXPORTAR RESULTADOS
# ==========================

output = {
    'generated_at': equipments_data['generated_at'],
    'avg_tariff_used': avg_tariff,
    'totals': {
        'active_kwh_year_python': round(total_active_python, 2),
        'active_kwh_year_laravel': round(total_active_laravel, 2),
        'standby_kwh_year_python': round(total_standby_python, 2),
        'standby_kwh_year_laravel': round(total_standby_laravel, 2),
        'total_annual_cost_python': round(sum(r['annual_cost_python'] for r in results), 2),
    },
    'results': results
}

with open('golden_results.json', 'w', encoding='utf-8') as f:
    json.dump(output, f, indent=2, ensure_ascii=False)

print("\n" + "="*80)
print("Resultados exportados a: golden_results.json")
print("="*80)
