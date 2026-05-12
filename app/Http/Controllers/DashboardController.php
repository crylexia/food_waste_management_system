<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(): View
    {
        $userId = auth()->id();

        // ── Today's snapshot ──────────────────────────────────────
        $todayEntry = DailyEntry::with('entryItems.item')
            ->where('user_id', $userId)
            ->where('date', today())
            ->first();

        $todayWastedQty   = 0;
        $todayWastedValue = 0;

        if ($todayEntry) {
            foreach ($todayEntry->entryItems as $ei) {
                $todayWastedQty   += $ei->wasted_quantity;
                $todayWastedValue += $ei->wasted_quantity * ($ei->item->price ?? 0);
            }
        }

        // ── Weekly waste trend (this week vs last week) ───────────
        $thisWeekWaste = $this->weeklyWasteValue($userId, 0);
        $lastWeekWaste = $this->weeklyWasteValue($userId, 1);

        $wasteTrendPct = null;
        $wasteTrendDir = null;

        if ($lastWeekWaste > 0) {
            $wasteTrendPct = (($thisWeekWaste - $lastWeekWaste) / $lastWeekWaste) * 100;
            $wasteTrendDir = $wasteTrendPct > 0 ? 'up' : ($wasteTrendPct < 0 ? 'down' : 'flat');
        }

        // ── Overall waste rate ────────────────────────────────────
        $comparison      = $this->analyticsService->getUsageComparison();
        $totalQtyUsed    = $comparison->sum('total_used');
        $totalQtyWasted  = $comparison->sum('total_wasted');
        $overallWasteRate = ($totalQtyUsed + $totalQtyWasted) > 0
            ? ($totalQtyWasted / ($totalQtyUsed + $totalQtyWasted)) * 100
            : 0;

        // ── Top 3 most wasted items ───────────────────────────────
        $topWasted = $this->analyticsService->getMostWastedItems(3);

        // ── Critical alerts only (danger/warning insights) ────────
        $criticalInsights = collect($this->analyticsService->getMeaningfulInsights())
            ->filter(fn($i) => in_array($i['type'], ['danger', 'warning']))
            ->take(2)
            ->values();

        // ── Recent entries (last 5) ───────────────────────────────
        $recentEntries = DailyEntry::with('entryItems')
            ->where('user_id', $userId)
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'todayEntry',
            'todayWastedQty',
            'todayWastedValue',
            'thisWeekWaste',
            'wasteTrendPct',
            'wasteTrendDir',
            'overallWasteRate',
            'topWasted',
            'criticalInsights',
            'recentEntries',
        ));
    }

    // ── Helper: total wasted value for a given week offset ────────
    private function weeklyWasteValue(int $userId, int $weeksAgo): float
    {
        return (float) DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items', 'entry_items.item_id', '=', 'items.id')
            ->where('daily_entries.user_id', $userId)
            ->whereBetween('daily_entries.date', [
                now()->subWeeks($weeksAgo)->startOfWeek(),
                now()->subWeeks($weeksAgo)->endOfWeek(),
            ])
            ->selectRaw('SUM(entry_items.wasted_quantity * items.price)')
            ->value(DB::raw('SUM(entry_items.wasted_quantity * items.price)')) ?? 0;
    }
}