<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentHistory extends Model
{
    use HasFactory;

    protected $table = 'equipment_history';

    protected $fillable = [
        'entity_equipment_id',
        'company_id',
        'change_type',
        'before_values',
        'after_values',
        'change_description',
        'changed_by_user_id',
    ];

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
    ];

    // Relaciones
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(EntityEquipment::class, 'entity_equipment_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }

    // Helpers
    public function getReadableDescription(): string
    {
        if ($this->change_description) {
            return $this->change_description;
        }

        return match ($this->change_type) {
            'power_changed' => sprintf(
                'Potencia cambió de %dW a %dW',
                $this->before_values['power_watts'] ?? 0,
                $this->after_values['power_watts'] ?? 0
            ),
            'usage_changed' => sprintf(
                'Uso diario cambió de %d min/día a %d min/día',
                $this->before_values['avg_daily_use_minutes'] ?? 0,
                $this->after_values['avg_daily_use_minutes'] ?? 0
            ),
            'type_changed' => 'Tipo de equipo modificado',
            'activated' => 'Equipo activado',
            'deleted' => 'Equipo dado de baja',
            'replaced' => 'Equipo reemplazado',
            default => 'Cambio en equipo',
        };
    }
}
