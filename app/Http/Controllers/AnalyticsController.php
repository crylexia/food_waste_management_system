<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\BusinessIntelligenceService;
use App\Services\PredictiveAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService              $analyticsService,
        protected BusinessIntelligenceService   $biService,
        protected PredictiveAnalyticsService    $predictiveService,
    ) {}

    public function index(Request $request): View
    {
        $timePeriod = $request->get('time_period', 'daily');
        $dateRange  = $request->get('date_range', null);

        // ── Descriptive ─────────────────────────────────────────
        $mostWasted      = $this->analyticsService->getMostWastedItems(10);
        $mostUsed        = $this->analyticsService->getMostUsedItems(10);
        $usageComparison = $this->analyticsService->getUsageComparison($dateRange);
        $periodStats     = $this->analyticsService->getTimePeriodStatistics($timePeriod, $dateRange);
        $insights        = $this->analyticsService->getMeaningfulInsights();

        // ── Diagnostic + Prescriptive ───────────────────────────
        $this->biService->setDateRange($dateRange);
        $kpis              = $this->biService->getKPIs();
        $decisionCards     = $this->biService->getDecisionCards();
        $recommendations   = $this->biService->getRecommendations();
        $itemIntelligence  = $this->biService->getItemPerformanceIntelligence();
        $rootCauses        = $this->biService->getRootCauseAnalysis();
        $impact            = $this->biService->getImpactEstimation();
        $categoryBreakdown = $this->biService->getCategoryBreakdown();
        $periodSummary     = $this->biService->getPeriodSummary($periodStats);

        // ── Predictive ──────────────────────────────────────────
        $forecastedWaste      = $this->predictiveService->getForecastedWaste(7);
        $riskScores           = $this->predictiveService->getRiskScores();
        $projectedLoss        = $this->predictiveService->getProjectedLoss(30);
        $dayOfWeekPatterns    = $this->predictiveService->getDayOfWeekPatterns();
        $procurementSuggestions = $this->predictiveService->getProcurementSuggestions();

        return view('analytics.index', compact(
            // Descriptive
            'mostWasted', 'mostUsed', 'usageComparison', 'periodStats',
            'timePeriod', 'dateRange', 'insights',
            // Diagnostic + Prescriptive
            'kpis', 'decisionCards', 'recommendations', 'itemIntelligence',
            'rootCauses', 'impact', 'categoryBreakdown', 'periodSummary',
            // Predictive
            'forecastedWaste', 'riskScores', 'projectedLoss',
            'dayOfWeekPatterns', 'procurementSuggestions',
        ));
    }
}