<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Entity;
use App\Services\SmartAlertService;
use App\Services\ClimateCorrelationService;

class GenerateSmartAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:generate 
                            {--entity= : ID de la entidad específica (opcional)}
                            {--all : Generar alertas para todas las entidades}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera alertas inteligentes basadas en análisis de consumo, clima y eficiencia';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alertService = new SmartAlertService(new ClimateCorrelationService());

        $this->info('🔔 Generando alertas inteligentes...');
        $this->newLine();

        // Determinar entidades a procesar
        if ($this->option('all')) {
            $entities = Entity::all();
            $this->info("📍 Procesando {$entities->count()} entidades...");
        } elseif ($entityId = $this->option('entity')) {
            $entity = Entity::find($entityId);
            if (!$entity) {
                $this->error("❌ No se encontró la entidad con ID {$entityId}");
                return 1;
            }
            $entities = collect([$entity]);
        } else {
            $this->error("❌ Debes especificar --entity=ID o --all");
            return 1;
        }

        $totalNewAlerts = 0;
        $totalActive = 0;

        foreach ($entities as $entity) {
            $this->info("  📍 Analizando: {$entity->name}");

            try {
                $stats = $alertService->generateAlertsForEntity($entity);
                $totalNewAlerts += $stats['new_alerts'];
                $totalActive += $stats['active_alerts'];

                if ($stats['new_alerts'] > 0) {
                    $this->line("     ✅ {$stats['new_alerts']} alerta(s) nueva(s) | Total activas: {$stats['active_alerts']}");
                } else {
                    $this->line("     ℹ️  Sin nuevas alertas");
                }

            } catch (\Exception $e) {
                $this->error("     ❌ Error: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('✨ Proceso completado:');
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Entidades procesadas', $entities->count()],
                ['Nuevas alertas generadas', $totalNewAlerts],
                ['Total alertas activas', $totalActive],
            ]
        );

        return 0;
    }
}
