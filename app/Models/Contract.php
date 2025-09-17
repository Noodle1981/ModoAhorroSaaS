<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Buena práctica importar
use Illuminate\Database\Eloquent\Relations\HasMany; // Buena práctica importar

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id', 'utility_company_id', 'contract_identifier', 'rate_name',
        'contracted_power_kw_p1', 'contracted_power_kw_p2', 'contracted_power_kw_p3',
        'start_date', 'end_date', 'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Obtiene el suministro al que pertenece el contrato.
     */
    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    /**
     * Obtiene la compañía comercializadora del contrato.
     */
    public function utilityCompany(): BelongsTo
    {
        return $this->belongsTo(UtilityCompany::class);
    }

    /**
     * Obtiene las facturas asociadas a este contrato.
     * ¡¡ESTA ES LA FUNCIÓN QUE FALTABA!!
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}