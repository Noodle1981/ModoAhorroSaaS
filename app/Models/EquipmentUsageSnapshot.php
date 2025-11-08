<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentUsageSnapshot extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment_usage_snapshots';

    protected $fillable = [
        'entity_equipment_id',
        'invoice_id',
        'start_date',
        'end_date',
        'snapshot_date',
        'avg_daily_use_minutes',
        'power_watts',
        'has_standby_mode',
        'calculated_kwh_period',
        'calculated_daily_kwh',
        'calculated_period_kwh',
        'status',
        'invalidated_at',
        'invalidation_reason',
        'recalculation_count',
        'is_equipment_deleted',
        // Frecuencia del período
        'is_daily_use', 'usage_days_per_week', 'usage_weekdays', 'minutes_per_session', 'frequency_source'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'snapshot_date' => 'date',
        'has_standby_mode' => 'boolean',
        'invalidated_at' => 'datetime',
        'is_equipment_deleted' => 'boolean',
        'is_daily_use' => 'boolean',
        'usage_weekdays' => 'array',
    ];

    /**
     * Obtiene el equipo del inventario al que pertenece este snapshot.
     */
    public function entityEquipment(): BelongsTo
    {
        return $this->belongsTo(EntityEquipment::class);
    }

    /**
     * Alias para entityEquipment (más corto)
     */
    public function equipment(): BelongsTo
    {
        return $this->entityEquipment();
    }

    /**
     * Obtiene la factura a la que está asociado este snapshot.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}