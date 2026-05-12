<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\BusinessIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService,
        protected BusinessIntelligenceService $biService,
    ) {}

    public function index(Request $request): View
    {
        $timePeriod = $request->get('time_period', 'daily');
        $dateRange  = $request->get('date_range', null);

        // ── Descriptive (existing) ──────────────────────────────
        $mostWasted      = $this->analyticsService->getMostWastedItems(10);
        $mostUsed        = $this->analyticsService->getMostUsedItems(10);
        $usageComparison = $this->analyticsService->getUsageComparison($dateRange);
        $periodStats     = $this->analyticsService->getTimePeriodStatistics($timePeriod, $dateRange);
        $insights        = $this->analyticsService->getMeaningfulInsights();

        // ── Diagnostic + Prescriptive (new) ────────────────────
        $kpis             = $this->biService->getKPIs();
        $decisionCards    = $this->biService->getDecisionCards();
        $recommendations  = $this->biService->getRecommendations();
        $itemIntelligence = $this->biService->getItemPerformanceIntelligence();
        $rootCauses       = $this->biService->getRootCauseAnalysis();
        $impact           = $this->biService->getImpactEstimation();
        $categoryBreakdown = $this->biService->getCategoryBreakdown();
        $periodSummary    = $this->biService->getPeriodSummary($periodStats);

        return view('analytics.index', compact(
            'mostWasted', 'mostUsed', 'usageComparison', 'periodStats',
            'timePeriod', 'dateRange', 'insights',
            'kpis', 'decisionCards', 'recommendations', 'itemIntelligence',
            'rootCauses', 'impact', 'categoryBreakdown', 'periodSummary',
        ));
    }
}