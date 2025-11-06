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
        'avg_daily_use_minutes',
        'power_watts',
        'has_standby_mode',
        'calculated_kwh_period',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'has_standby_mode' => 'boolean',
    ];

    /**
     * Obtiene el equipo del inventario al que pertenece este snapshot.
     */
    public function entityEquipment(): BelongsTo
    {
        return $this->belongsTo(EntityEquipment::class);
    }

    /**
     * Obtiene la factura a la que está asociado este snapshot.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}