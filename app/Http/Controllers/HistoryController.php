<?php

namespace App\Http\Controllers;

use App\Services\HistoricalAnalysisService;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    protected $analysisService;

    public function __construct(HistoricalAnalysisService $analysisService)
    {
        $this->analysisService = $analysisService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $chartData = $this->analysisService->generateMonthlyReport();
        return view('history.index', compact('chartData'));
    }
}
