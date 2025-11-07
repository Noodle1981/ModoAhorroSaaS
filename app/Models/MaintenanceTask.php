<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_type_id',
        'name',
        'description',
        'recommended_frequency_days', // frecuencia base en días
        'recommended_season', // opcional: 'summer','winter','all','transition'
        'maintenance_type', // tipo lógico (filter_clean, deep_clean, defrost, drum_clean, gasket_clean)
        'variable_interval_json', // JSON para reglas dinámicas (ej: {"usage_hours_per_day":{">6":30,"<=6":60}})
    ];

    protected $casts = [
        'variable_interval_json' => 'array',
    ];

    /**
     * Calcula la frecuencia efectiva de la tarea en días considerando reglas dinámicas y uso.
     * @param array $context Ej: ['usage_hours_per_day' => 7, 'ice_thickness_mm' => 4]
     */
    public function resolveEffectiveIntervalDays(array $context = []): int
    {
        $base = (int)($this->recommended_frequency_days ?? 0);
        if (!$this->variable_interval_json || !is_array($this->variable_interval_json)) {
            return max(1, $base);
        }

        $rules = $this->variable_interval_json;
        // Regla ejemplo para horas de uso diarias
        if (isset($rules['usage_hours_per_day']) && isset($context['usage_hours_per_day'])) {
            $value = (float)$context['usage_hours_per_day'];
            // Reglas en formato {"<=4":90,">4":60}
            foreach ($rules['usage_hours_per_day'] as $expr => $interval) {
                if ($this->matchesExpression($value, $expr)) {
                    return (int)$interval;
                }
            }
        }
        // Regla ejemplo para espesor de hielo heladera
        if (isset($rules['ice_thickness_mm']) && isset($context['ice_thickness_mm'])) {
            $value = (float)$context['ice_thickness_mm'];
            foreach ($rules['ice_thickness_mm'] as $expr => $interval) {
                if ($this->matchesExpression($value, $expr)) {
                    return (int)$interval;
                }
            }
        }

        return max(1, $base);
    }

    private function matchesExpression(float $value, string $expression): bool
    {
        // Soporta expresiones simples: "<=4", ">6", "==3".
        if (preg_match('/^(<=|>=|<|>|==)([0-9]+(\.[0-9]+)?)$/', trim($expression), $m)) {
            $op = $m[1];
            $num = (float)$m[2];
            return match($op) {
                '<' => $value < $num,
                '>' => $value > $num,
                '<=' => $value <= $num,
                '>=' => $value >= $num,
                '==' => abs($value - $num) < 0.00001,
                default => false,
            };
        }
        return false;
    }

    public function equipmentType()
    {
        return $this->belongsTo(EquipmentType::class);
    }
}