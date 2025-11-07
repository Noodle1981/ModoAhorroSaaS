<?php

namespace App\Console\Commands;

use App\Models\Entity;
use App\Services\EquipmentReplacementService;
use Illuminate\Console\Command;

class AnalyzeEquipmentReplacements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'equipment:analyze-replacements 
                            {--entity= : ID de la entidad específica a analizar}
                            {--all : Analizar todas las entidades}
                            {--kwh-price=150 : Precio del kWh en ARS}
                            {--min-savings=15 : Porcentaje mínimo de ahorro requerido}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza equipos de entidades y genera recomendaciones de reemplazo por equipos más eficientes';

    protected EquipmentReplacementService $replacementService;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Iniciando análisis de reemplazos de equipos...');
        $this->newLine();

        // Obtener parámetros
        $kwhPrice = (float) $this->option('kwh-price');
        $minSavings = (float) $this->option('min-savings');
        
        // Instanciar servicio con parámetros
        $this->replacementService = new EquipmentReplacementService($kwhPrice, $minSavings);

        // Determinar qué entidades analizar
        if ($this->option('entity')) {
            $entities = Entity::where('id', $this->option('entity'))->get();
            if ($entities->isEmpty()) {
                $this->error('❌ Entidad no encontrada');
                return 1;
            }
        } elseif ($this->option('all')) {
            $entities = Entity::all();
        } else {
            $this->error('❌ Debes especificar --entity=ID o --all');
            return 1;
        }

        if ($entities->isEmpty()) {
            $this->warn('⚠️ No hay entidades para analizar');
            return 0;
        }

        $this->info("📊 Configuración:");
        $this->line("   Precio kWh: \${$kwhPrice}");
        $this->line("   Ahorro mínimo: {$minSavings}%");
        $this->line("   Entidades a analizar: {$entities->count()}");
        $this->newLine();

        // Analizar cada entidad
        $totalRecommendations = 0;
        $totalNoMatch = 0;
        $results = [];

        foreach ($entities as $entity) {
            $this->line("🏠 Analizando: {$entity->name}...");
            
            $result = $this->replacementService->analyzeEntityEquipment($entity);
            $results[] = $result;
            
            $totalRecommendations += $result['recommendations_generated'];
            $totalNoMatch += $result['no_replacement_found'];

            // Mostrar detalle
            if ($result['recommendations_generated'] > 0) {
                $this->info("   ✅ {$result['recommendations_generated']} recomendaciones generadas");
            }
            if ($result['no_replacement_found'] > 0) {
                $this->warn("   ⚠️ {$result['no_replacement_found']} equipos sin reemplazo en catálogo");
            }
            if ($result['insufficient_savings'] > 0) {
                $this->line("   ℹ️ {$result['insufficient_savings']} con ahorro insuficiente");
            }
        }

        $this->newLine();

        // Tabla resumen
        $this->info('📋 RESUMEN DE ANÁLISIS');
        $tableData = [];
        foreach ($results as $result) {
            $tableData[] = [
                $result['entity_name'],
                $result['analyzed'],
                $result['recommendations_generated'],
                $result['no_replacement_found'],
                $result['insufficient_savings'],
            ];
        }

        $this->table(
            ['Entidad', 'Analizados', 'Recomendaciones', 'Sin match', 'Ahorro bajo'],
            $tableData
        );

        // Resumen global
        $this->newLine();
        $this->info("🎯 TOTAL: {$totalRecommendations} recomendaciones generadas");
        
        if ($totalNoMatch > 0) {
            $this->newLine();
            $this->warn("💡 TIP: {$totalNoMatch} equipos no tienen reemplazo en el catálogo.");
            $this->warn("   Revisa storage/logs/laravel.log para ver qué tipos de equipos faltan.");
            $this->warn("   Luego ejecuta: php artisan db:seed --class=MarketEquipmentCatalogSeeder");
        }

        return 0;
    }
}
