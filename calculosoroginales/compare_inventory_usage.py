import json
import math
from pathlib import Path
import sys

"""
Comparador rápido entre exportación Laravel y lógica legacy simplificada.

Uso:
    python calculosoroginales/compare_inventory_usage.py [ruta_json]

Si no se pasa ruta, usa storage/app/private/exports/inventory_usage.json.

Lógica legacy asumida:
    horas/año = (avg_daily_use_minutes_override OR default_avg_daily_use_minutes) / 60 * 365
    (no aplica patrón días/semana/minutes_per_session; se refleja diferencia en diff)

Se calcula por fila:
    diff_horas = horas_year_actual - horas_year_legacy
    pct_diff = diff_horas / horas_year_legacy (si legacy > 0)

Totales reportados:
    installed_power_kw (Laravel) vs suma nominal_kw (legacy) – deberían coincidir.
    active_kwh_year sum (Laravel) – sólo referencial aquí.
"""

def load(path: Path):
    with path.open('r', encoding='utf-8') as f:
        return json.load(f)

def main():
    if len(sys.argv) > 1:
        path = Path(sys.argv[1])
    else:
        path = Path('storage/app/private/exports/inventory_usage.json')

    if not path.exists():
        print(f"Archivo no encontrado: {path}")
        sys.exit(1)

    data = load(path)
    rows = data.get('rows', [])

    report = []
    total_kw = 0.0
    total_kw_legacy = 0.0
    sum_hours_actual = 0.0
    sum_hours_legacy = 0.0
    sum_abs_diff_hours = 0.0

    for r in rows:
        nominal_kw = float(r.get('nominal_kw') or 0)
        qty = int(r.get('quantity') or 1)
        total_kw += nominal_kw * qty
        total_kw_legacy += nominal_kw * qty  # misma fuente

        hours_actual = float(r.get('hours_per_year') or 0)
        # Legacy: no aplica derivación por patrón, sólo override/default
        override_minutes = r.get('avg_daily_use_minutes_override')
        default_minutes = r.get('default_avg_daily_use_minutes')
        base_minutes = override_minutes if override_minutes is not None else default_minutes
        hours_legacy = (base_minutes or 0) / 60 * 365

        sum_hours_actual += hours_actual * qty
        sum_hours_legacy += hours_legacy * qty
        diff = hours_actual - hours_legacy
        sum_abs_diff_hours += abs(diff) * qty
        pct = diff / hours_legacy if hours_legacy > 0 else None

        if hours_legacy > 0 and abs(pct) > 0.05:  # umbral 5%
            report.append({
                'id': r.get('id'),
                'type': r.get('type'),
                'category': r.get('category'),
                'qty': qty,
                'hours_actual': round(hours_actual, 2),
                'hours_legacy': round(hours_legacy, 2),
                'diff_hours': round(diff, 2),
                'pct_diff': round(pct * 100, 2)
            })

    print("=== COMPARACIÓN INVENTARIO ===")
    print(f"Potencia instalada (Laravel export): {total_kw:.2f} kW")
    print(f"Potencia instalada (Legacy calc):   {total_kw_legacy:.2f} kW")
    print(f"Horas/año suma (Laravel derivadas): {sum_hours_actual:.2f}")
    print(f"Horas/año suma (Legacy simple):     {sum_hours_legacy:.2f}")
    if sum_hours_legacy > 0:
        print(f"Diferencia relativa global: {(sum_hours_actual - sum_hours_legacy)/sum_hours_legacy*100:.2f}%")
        print(f"Error absoluto promedio por equipo: {sum_abs_diff_hours/len(rows):.2f} h")

    if report:
        print("\nEquipos con diferencia > 5%:")
        for item in report[:30]:  # limitar listado
            print(f" - #{item['id']} {item['type']} ({item['category']}) qty={item['qty']} actual={item['hours_actual']}h legacy={item['hours_legacy']}h diff={item['diff_hours']}h ({item['pct_diff']}%)")
        if len(report) > 30:
            print(f"... {len(report)-30} más")
    else:
        print("\nNo hay diferencias mayores al 5%.")

if __name__ == '__main__':
    main()
