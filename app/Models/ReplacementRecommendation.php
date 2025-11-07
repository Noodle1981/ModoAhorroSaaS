<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReplacementRecommendation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entity_equipment_id',
        'market_equipment_id',
        'current_equipment_name',
        'current_power_watts',
        'current_annual_kwh',
        'recommended_equipment_name',
        'recommended_power_watts',
        'recommended_annual_kwh',
        'recommended_energy_label',
        'kwh_saved_per_year',
        'money_saved_per_year',
        'money_saved_per_month',
        'investment_required',
        'roi_months',
        'kwh_price_used',
        'status',
        'accepted_at',
        'completed_at',
        'recovery_start_date',
        'estimated_recovery_date',
    ];

    protected $casts = [
        'current_power_watts' => 'integer',
        'current_annual_kwh' => 'decimal:2',
        'recommended_power_watts' => 'integer',
        'recommended_annual_kwh' => 'decimal:2',
        'kwh_saved_per_year' => 'decimal:2',
        'money_saved_per_year' => 'decimal:2',
        'money_saved_per_month' => 'decimal:2',
        'investment_required' => 'decimal:2',
        'roi_months' => 'decimal:2',
        'kwh_price_used' => 'decimal:2',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'recovery_start_date' => 'date',
        'estimated_recovery_date' => 'date',
    ];

    // Relaciones
    public function entityEquipment()
    {
        return $this->belongsTo(EntityEquipment::class);
    }

    public function marketEquipment()
    {
        return $this->belongsTo(MarketEquipmentCatalog::class, 'market_equipment_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeInRecovery($query)
    {
        return $query->where('status', 'in_recovery');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'accepted', 'in_recovery']);
    }

    // Métodos de acción
    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function reject()
    {
        $this->update(['status' => 'rejected']);
        $this->delete(); // Soft delete
    }

    public function startRecovery(\DateTime $start_date = null)
    {
        $startDate = $start_date ?? now();
        if (!$startDate instanceof \Carbon\Carbon) {
            $startDate = \Carbon\Carbon::parse($startDate->format('Y-m-d H:i:s'));
        }
        $estimatedDate = $startDate->copy()->addMonths((int) ceil($this->roi_months));

        $this->update([
            'status' => 'in_recovery',
            'recovery_start_date' => $startDate,
            'estimated_recovery_date' => $estimatedDate,
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
        $this->delete(); // Soft delete para mantener historial
    }

    // Atributos computados
    public function getSavingsPercentageAttribute(): float
    {
        if ($this->current_annual_kwh == 0) return 0;
        return round(($this->kwh_saved_per_year / $this->current_annual_kwh) * 100, 1);
    }

    public function getRoiYearsAttribute(): ?float
    {
        return $this->roi_months ? round($this->roi_months / 12, 1) : null;
    }

    public function getIsGoodInvestmentAttribute(): bool
    {
        // Consideramos buena inversión si ROI < 5 años
        return $this->roi_months && $this->roi_months <= 60;
    }
}