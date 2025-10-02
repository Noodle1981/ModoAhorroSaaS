<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMaintenanceRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-maintenance-recommendations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates proactive maintenance recommendations based on overdue tasks.';

    /**
     * Execute the console command.
     */
    public function handle(RecommendationService $recommendationService)
    {
        $this->info('Generating maintenance recommendations...');
        
        $recommendationService->generateMaintenanceRecommendations();
        
        $this->info('Maintenance recommendations generated successfully.');
    }
}
