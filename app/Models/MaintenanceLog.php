<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_equipment_id',
        'maintenance_task_id',
        'performed_on_date',
        'verification_status',
        'notes',
    ];
    
    protected $casts = [
        'performed_on_date' => 'date',
    ];

    public function entityEquipment()
    {
        return $this->belongsTo(EntityEquipment::class, 'entity_equipment_id');
    }

    public function maintenanceTask()
    {
        return $this->belongsTo(MaintenanceTask::class);
    }
}