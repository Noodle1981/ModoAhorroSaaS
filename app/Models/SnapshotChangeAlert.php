<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnapshotChangeAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'company_id',
        'entity_equipment_id',
        'equipment_history_id',
        'alert_type',
        'message',
        'affected_snapshots',
        'status',
        'acknowledged_at',
        'resolved_at',
    ];

    protected $casts = [
        'affected_snapshots' => 'array',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    // Relaciones
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(EntityEquipment::class, 'entity_equipment_id');
    }

    public function historyRecord(): BelongsTo
    {
        return $this->belongsTo(EquipmentHistory::class, 'equipment_history_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeForEntity($query, int $entityId)
    {
        return $query->where('entity_id', $entityId);
    }

    // Helpers
    public function acknowledge(): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);
    }

    public function resolve(): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function getAffectedSnapshotsCount(): int
    {
        return is_array($this->affected_snapshots) ? count($this->affected_snapshots) : 0;
    }
}
