<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entity;
use App\Services\MaintenanceSchedulerService;

class ScanMaintenance extends Command
{
    protected $signature = 'maintenance:scan {--entity= : ID de la entidad a escanear (opcional)}';
    protected $description = 'Escanea tareas de mantenimiento y genera alertas próximas o vencidas';

    public function handle(MaintenanceSchedulerService $service)
    {
        $entityId = $this->option('entity');
        $entities = $entityId ? Entity::where('id', $entityId)->get() : Entity::all();

        $total = 0; $alerts = 0;
        foreach ($entities as $entity) {
            $res = $service->scanEntity($entity);
            $alerts += $res['new_alerts'] ?? 0;
            $total++;
            $this->info("Entidad {$entity->name}: +".($res['new_alerts'] ?? 0)." alertas");
        }

        $this->info("Listo. Entidades: $total, Alertas nuevas: $alerts");
        return self::SUCCESS;
    }
}
