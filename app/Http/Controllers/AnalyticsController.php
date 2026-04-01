<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display analytics dashboard.
     */
    public function index(Request $request): View
{
    $timePeriod = $request->get('time_period', 'daily');
    $dateRange = $request->get('date_range', null);

    $mostWasted = $this->analyticsService->getMostWastedItems(10);
    $mostUsed = $this->analyticsService->getMostUsedItems(10);
    $usageComparison = $this->analyticsService->getUsageComparison($dateRange);
    $periodStats = $this->analyticsService->getTimePeriodStatistics($timePeriod, $dateRange);

    // IMPORTANT
    $insights = $this->analyticsService->getMeaningfulInsights();

    return view('analytics.index', compact(
        'mostWasted',
        'mostUsed',
        'usageComparison',
        'periodStats',
        'timePeriod',
        'dateRange',
        'insights'
    ));
}
}