<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class VacationPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'start_date',
        'end_date',
        'days_away',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_away' => 'integer',
    ];

    /**
     * Relación con Entity
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Calcular días de ausencia automáticamente
     */
    public function calculateDaysAway(): int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return 0;
    }

    /**
     * Actualizar estado según fechas
     */
    public function updateStatus(): void
    {
        $now = Carbon::now()->startOfDay();
        
        if ($now->lt($this->start_date)) {
            $this->status = 'pending';
        } elseif ($now->between($this->start_date, $this->end_date)) {
            $this->status = 'active';
        } else {
            $this->status = 'completed';
        }
        
        $this->save();
    }

    /**
     * Scope para planes activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para planes pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para planes completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
