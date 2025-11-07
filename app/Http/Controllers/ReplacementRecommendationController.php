<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\ReplacementRecommendation;
use App\Services\EquipmentReplacementService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Browsershot\Browsershot;
use Illuminate\Http\Request;

class ReplacementRecommendationController extends Controller
{
    protected EquipmentReplacementService $replacementService;

    public function __construct(EquipmentReplacementService $replacementService)
    {
        $this->replacementService = $replacementService;
    }

    /**
     * Muestra todas las recomendaciones de reemplazo
     */
    public function index(Request $request)
    {
        $query = ReplacementRecommendation::with(['entityEquipment.entity', 'marketEquipment'])
            ->latest();

        // Filtrar por estado si se proporciona
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        } else {
            // Por defecto mostrar solo activas
            $query->active();
        }

        // Filtrar por entidad si se proporciona
        if ($request->has('entity_id')) {
            $query->whereHas('entityEquipment', function($q) use ($request) {
                $q->where('entity_id', $request->entity_id);
            });
        }

        $recommendations = $query->paginate(12);

        // Estadísticas
        $stats = [
            'total_pending' => ReplacementRecommendation::pending()->count(),
            'total_accepted' => ReplacementRecommendation::accepted()->count(),
            'total_in_recovery' => ReplacementRecommendation::inRecovery()->count(),
            'total_savings_potential' => ReplacementRecommendation::pending()->sum('money_saved_per_year'),
            'total_investment_required' => ReplacementRecommendation::pending()->sum('investment_required'),
        ];

        $entities = Entity::all();

    return view('replacement-recommendations.index', compact('recommendations', 'stats', 'entities'));
    }

    /**
     * Genera nuevas recomendaciones para una entidad
     */
    public function generate(Request $request)
    {
        $request->validate([
            'entity_id' => 'required|exists:entities,id',
        ]);

        $entity = Entity::findOrFail($request->entity_id);
        $result = $this->replacementService->analyzeEntityEquipment($entity);

        return redirect()->back()
            ->with('success', "Análisis completado: {$result['recommendations_generated']} nuevas recomendaciones generadas.")
            ->with('no_match_count', $result['no_replacement_found'] ?? 0)
            ->with('analyzed_count', $result['analyzed'] ?? 0)
            ->with('insufficient_savings', $result['insufficient_savings'] ?? 0);
    }

    /**
     * Acepta una recomendación
     */
    public function accept(ReplacementRecommendation $recommendation)
    {
        $recommendation->accept();

        return redirect()->back()->with('success', 
            "Recomendación aceptada. Puedes iniciar el seguimiento de recupero cuando realices la compra."
        );
    }

    /**
     * Rechaza una recomendación
     */
    public function reject(ReplacementRecommendation $recommendation)
    {
        $recommendation->reject();

        return redirect()->back()->with('success', 
            "Recomendación descartada."
        );
    }

    /**
     * Inicia el tracking de recupero de inversión
     */
    public function startRecovery(Request $request, ReplacementRecommendation $recommendation)
    {
        $request->validate([
            'start_date' => 'required|date',
        ]);

        $recommendation->startRecovery(new \DateTime($request->start_date));

        return redirect()->back()->with('success', 
            "Seguimiento de ROI iniciado. Fecha estimada de recupero: " . 
            $recommendation->estimated_recovery_date->format('d/m/Y')
        );
    }

    /**
     * Marca la inversión como completamente recuperada
     */
    public function complete(ReplacementRecommendation $recommendation)
    {
        $recommendation->complete();

        return redirect()->back()->with('success', 
            "¡Felicitaciones! Inversión recuperada exitosamente."
        );
    }

    /**
     * Muestra detalles de una recomendación
     */
    public function show(ReplacementRecommendation $recommendation)
    {
        $recommendation->load(['entityEquipment.entity', 'marketEquipment']);

        return view('recommendations.show', compact('recommendation'));
    }

    /**
     * Exporta una recomendación individual a PDF (HTML capturable via Browsershot)
     */
    public function export(ReplacementRecommendation $recommendation)
    {
        $recommendation->load(['entityEquipment.entity', 'marketEquipment']);

        $html = View::make('replacement-recommendations.export-single', [
            'recommendation' => $recommendation,
        ])->render();

        $filename = 'recomendacion-reemplazo-'.$recommendation->id.'-'.Str::slug($recommendation->equipment_name).'.pdf';
        $tempPath = storage_path('app/tmp/'.Str::random(16).'.pdf');
        if (!is_dir(dirname($tempPath))) {
            @mkdir(dirname($tempPath), 0777, true);
        }
        try {
            Browsershot::html($html)
                ->showBackground()
                ->format('A4')
                ->margins(10, 12, 10, 12)
                ->savePdf($tempPath);
            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            // Fallback: descargar HTML si falla la generación de PDF
            $fallback = 'recomendacion-reemplazo-'.$recommendation->id.'-'.Str::slug($recommendation->equipment_name).'.html';
            return response($html)->header('Content-Disposition', 'attachment; filename="'.$fallback.'"');
        }
    }

    /**
     * Exporta todas las recomendaciones activas a un HTML listo para PDF
     */
    public function exportAll(Request $request)
    {
        $query = ReplacementRecommendation::with(['entityEquipment.entity', 'marketEquipment'])->active();

        if ($request->has('entity_id')) {
            $query->whereHas('entityEquipment', function($q) use ($request) {
                $q->where('entity_id', $request->entity_id);
            });
        }

        $recommendations = $query->get();

        $html = View::make('replacement-recommendations.export-all', [
            'recommendations' => $recommendations,
            'generated_at' => now(),
            'total_annual_savings' => $recommendations->sum('money_saved_per_year'),
            'total_investment' => $recommendations->sum('investment_required'),
        ])->render();

        $filename = 'recomendaciones-reemplazo-'.now()->format('Ymd-His').'.pdf';
        $tempPath = storage_path('app/tmp/'.Str::random(16).'.pdf');
        if (!is_dir(dirname($tempPath))) {
            @mkdir(dirname($tempPath), 0777, true);
        }
        try {
            Browsershot::html($html)
                ->showBackground()
                ->format('A4')
                ->margins(10, 12, 10, 12)
                ->savePdf($tempPath);
            return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            $fallback = 'recomendaciones-reemplazo-'.now()->format('Ymd-His').'.html';
            return response($html)->header('Content-Disposition', 'attachment; filename="'.$fallback.'"');
        }
    }
}
