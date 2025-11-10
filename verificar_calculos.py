#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script para verificar que Laravel calcula igual que Python
Compara los cálculos exportados desde Laravel con los cálculos de Python
"""

import json
import pandas as pd
from datetime import datetime

# Cargar datos exportados desde Laravel
with open('storage/app/exports/equipments.json', 'r', encoding='utf-8') as f:
    equipos_laravel = json.load(f)

with open('storage/app/exports/invoices.json', 'r', encoding='utf-8') as f:
    facturas = json.load(f)

# Tabla de factores (igual que en Python)
factores_data = {
    'tipo_de_proceso': ['Motor', 'Resistencia', 'Electrónico', 'Motor & Resistencia', 'Magnetrón', 'Electroluminiscencia'],
    'factor_carga': [0.7, 1, 0.7, 0.8, 0.7, 1],
    'eficiencia': [0.9, 0.6, 0.8, 0.82, 0.6, 0.9]
}
tabla_factores = pd.DataFrame(factores_data)

# Convertir equipos a DataFrame
df_laravel = pd.DataFrame(equipos_laravel)

# Renombrar columnas de Laravel para evitar conflictos
df_laravel = df_laravel.rename(columns={
    'factor_carga': 'factor_carga_laravel',
    'factor_eficiencia': 'eficiencia_laravel'
})

# Merge con tabla de factores para verificar
df_merged = pd.merge(df_laravel, tabla_factores, on='tipo_de_proceso', how='left')

# Calcular energía según Python
# EnergiaConsumida_Wh = horas_por_dia * factor_carga * cantidad * potencia_watts / eficiencia
df_merged['EnergiaConsumida_Wh_Python'] = (
    df_merged['horas_por_dia'] * 
    df_merged['factor_carga'] * 
    df_merged['cantidad'] * 
    df_merged['potencia_watts'] / 
    df_merged['eficiencia']
)

# Convertir a kWh/año para comparar
df_merged['kwh_activo_año_Python'] = (df_merged['EnergiaConsumida_Wh_Python'] * 365) / 1000

# Comparar
df_merged['diff_kwh'] = df_merged['kwh_activo_año'] - df_merged['kwh_activo_año_Python']
df_merged['diff_factor_carga'] = df_merged['factor_carga_laravel'] - df_merged['factor_carga']
df_merged['diff_eficiencia'] = df_merged['eficiencia_laravel'] - df_merged['eficiencia']

# Filtrar discrepancias significativas (> 0.01 kWh)
discrepancias = df_merged[abs(df_merged['diff_kwh']) > 0.01]

print("=" * 80)
print("VERIFICACIÓN DE CÁLCULOS: Laravel vs Python")
print("=" * 80)
print(f"\nTotal equipos: {len(df_merged)}")
print(f"Equipos con discrepancias: {len(discrepancias)}")

if len(discrepancias) > 0:
    print("\n⚠️  DISCREPANCIAS ENCONTRADAS:\n")
    for idx, row in discrepancias.iterrows():
        print(f"Equipo: {row['nombre']}")
        print(f"  Tipo de proceso: {row['tipo_de_proceso']}")
        print(f"  Factor carga - Laravel: {row['factor_carga_laravel']:.2f} | Python: {row['factor_carga']:.2f} | Diff: {row['diff_factor_carga']:.4f}")
        print(f"  Eficiencia - Laravel: {row['eficiencia_laravel']:.2f} | Python: {row['eficiencia']:.2f} | Diff: {row['diff_eficiencia']:.4f}")
        print(f"  kWh/año - Laravel: {row['kwh_activo_año']:.2f} | Python: {row['kwh_activo_año_Python']:.2f} | Diff: {row['diff_kwh']:.2f}")
        print()
    
    # Guardar discrepancias en archivo
    with open('errores_golden_test.txt', 'w', encoding='utf-8') as f:
        f.write("# Errores de comparación golden test - Laravel vs Python\n\n")
        for idx, row in discrepancias.iterrows():
            f.write(f"Equipo: {row['nombre']} | Tipo: {row['tipo_de_proceso']}\n")
            f.write(f"  Factor carga - Laravel: {row['factor_carga_laravel']:.2f} vs Python: {row['factor_carga']:.2f}\n")
            f.write(f"  Eficiencia - Laravel: {row['eficiencia_laravel']:.2f} vs Python: {row['eficiencia']:.2f}\n")
            f.write(f"  kWh/año - Laravel: {row['kwh_activo_año']:.2f} vs Python: {row['kwh_activo_año_Python']:.2f} | Diff: {row['diff_kwh']:.2f}\n\n")
    
    print(f"Discrepancias guardadas en: errores_golden_test.txt")
else:
    print("\n✅ NO HAY DISCREPANCIAS - Laravel calcula igual que Python")
    print("\nResumen:")
    print(f"  - Total kWh/año Laravel: {df_merged['kwh_activo_año'].sum():.2f}")
    print(f"  - Total kWh/año Python: {df_merged['kwh_activo_año_Python'].sum():.2f}")
    print(f"  - Diferencia total: {abs(df_merged['kwh_activo_año'].sum() - df_merged['kwh_activo_año_Python'].sum()):.4f} kWh")

# Verificar factores por tipo de proceso
print("\n" + "=" * 80)
print("VERIFICACIÓN DE FACTORES POR TIPO DE PROCESO")
print("=" * 80)
for tipo in tabla_factores['tipo_de_proceso']:
    equipos_tipo = df_merged[df_merged['tipo_de_proceso'] == tipo]
    if len(equipos_tipo) > 0:
        factor_python = tabla_factores[tabla_factores['tipo_de_proceso'] == tipo]['factor_carga'].values[0]
        efic_python = tabla_factores[tabla_factores['tipo_de_proceso'] == tipo]['eficiencia'].values[0]
        
        # Verificar que todos los equipos de este tipo tienen los mismos factores
        factores_laravel_unicos = equipos_tipo['factor_carga_laravel'].unique()
        efic_laravel_unicos = equipos_tipo['eficiencia_laravel'].unique()
        
        match_factor = len(factores_laravel_unicos) == 1 and factores_laravel_unicos[0] == factor_python
        match_efic = len(efic_laravel_unicos) == 1 and efic_laravel_unicos[0] == efic_python
        
        status = "✅" if match_factor and match_efic else "❌"
        print(f"{status} {tipo}: {len(equipos_tipo)} equipos")
        print(f"   Factor carga: Laravel={factores_laravel_unicos} | Python={factor_python}")
        print(f"   Eficiencia: Laravel={efic_laravel_unicos} | Python={efic_python}")

print("\n" + "=" * 80)
