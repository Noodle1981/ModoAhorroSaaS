<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ClimateSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'period_start',
        'period_end',
        'avg_temperature_c',
        'min_temperature_c',
        'max_temperature_c',
        'days_above_30c',
        'days_below_15c',
        'total_cooling_degree_days',
        'total_heating_degree_days',
        'avg_humidity_percent',
        'climate_category',
        'data_source',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'avg_temperature_c' => 'decimal:2',
        'min_temperature_c' => 'decimal:2',
        'max_temperature_c' => 'decimal:2',
        'total_cooling_degree_days' => 'decimal:2',
        'total_heating_degree_days' => 'decimal:2',
    ];

    // Relaciones
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // Helper methods
    public function getPeriodDays(): int
    {
        return $this->period_start->diffInDays($this->period_end) + 1;
    }

    public function getFormattedPeriod(): string
    {
        return $this->period_start->format('d/m/Y') . ' - ' . $this->period_end->format('d/m/Y');
    }

    public function getClimateCategoryLabel(): string
    {
        return match($this->climate_category) {
            'muy_caluroso' => 'Muy Caluroso',
            'caluroso' => 'Caluroso',
            'templado' => 'Templado',
            'fresco' => 'Fresco',
            'frio' => 'Frío',
            default => 'N/A',
        };
    }

    public function getClimateCategoryColor(): string
    {
        return match($this->climate_category) {
            'muy_caluroso' => 'red',
            'caluroso' => 'orange',
            'templado' => 'green',
            'fresco' => 'blue',
            'frio' => 'indigo',
            default => 'gray',
        };
    }

    // Scopes
    public function scopeSimilarClimate($query, float $avgTemp, float $tolerance = 3)
    {
        return $query->whereBetween('avg_temperature_c', [
            $avgTemp - $tolerance,
            $avgTemp + $tolerance
        ]);
    }

    public function scopeForMonth($query, int $month, ?int $year = null)
    {
        return $query->whereMonth('period_start', $month)
                     ->when($year, fn($q) => $q->whereYear('period_start', $year));
    }
}
