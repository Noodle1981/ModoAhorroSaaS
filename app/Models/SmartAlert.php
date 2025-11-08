<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmartAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'invoice_id',
        'type',
        'severity',
        'title',
        'description',
        'data',
        'is_read',
        'is_dismissed',
        'dismissed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'is_dismissed' => 'boolean',
        'dismissed_at' => 'datetime',
    ];

    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Scope para alertas activas (no descartadas)
     */
    public function scopeActive($query)
    {
        return $query->where('is_dismissed', false);
    }

    /**
     * Scope para alertas no leídas
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Marcar como leída
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Descartar alerta
     */
    public function dismiss()
    {
        $this->update([
            'is_dismissed' => true,
            'dismissed_at' => now(),
        ]);
    }

    /**
     * Obtener icono según tipo de alerta
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'consumption_anomaly' => 'fa-exclamation-triangle',
            'temperature_mismatch' => 'fa-thermometer-half',
            'equipment_inefficiency' => 'fa-wrench',
            'climate_alert' => 'fa-cloud-sun',
            'cost_spike' => 'fa-dollar-sign',
            'baseline_deviation' => 'fa-chart-line',
            'standby_pending' => 'fa-plug',
            'standby_recommendation_available' => 'fa-lightbulb',
            'standby_new_equipment' => 'fa-plug-circle-plus',
            'usage_pending' => 'fa-calendar-check',
            'usage_recommendation_available' => 'fa-calendar-plus',
            'usage_new_equipment' => 'fa-calendar-day',
            default => 'fa-bell',
        };
    }

    /**
     * Obtener color según severidad
     */
    public function getColorClassAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'red',
            'warning' => 'yellow',
            'info' => 'blue',
            default => 'gray',
        };
    }
}