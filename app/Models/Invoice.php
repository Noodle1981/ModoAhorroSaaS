<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id', 'climate_snapshot_id', 'invoice_number', 'invoice_date', 'start_date', 'end_date',
        'energy_consumed_p1_kwh', 'energy_consumed_p2_kwh', 'energy_consumed_p3_kwh', 'total_energy_consumed_kwh',
        'cost_for_energy', 'cost_for_power', 'taxes', 'other_charges', 'total_amount',
        'total_energy_injected_kwh', 'surplus_compensation_amount',
        'file_path', 'source', 'co2_footprint_kg',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function climateSnapshot()
    {
        return $this->belongsTo(ClimateSnapshot::class);
    }

    /**
     * Snapshots de uso de equipos asociados a esta factura.
     */
    public function equipmentUsageSnapshots()
    {
        return $this->hasMany(EquipmentUsageSnapshot::class);
    }
}