<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PredictiveAnalyticsService
{
    // Minimum days of history required before showing predictions
    private const MIN_DAYS_FOR_PREDICTION = 7;

    public function __construct(protected AnalyticsService $analyticsService) {}

    // ─────────────────────────────────────────────────────────────
    // 1. FORECASTED WASTE  (next N days per item)
    //    Based on: avg daily wasted_quantity per item over last 30 days
    // ─────────────────────────────────────────────────────────────
    public function getForecastedWaste(int $daysAhead = 7): Collection
    {
        $rows = DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items',         'entry_items.item_id',        '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->where('daily_entries.date', '>=', now()->subDays(30)->format('Y-m-d'))
            ->select('items.id', 'items.name', 'items.category', 'items.price')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('SUM(entry_items.used_quantity)   as total_used')
            ->selectRaw('COUNT(DISTINCT daily_entries.date) as active_days')
            ->groupBy('items.id', 'items.name', 'items.category', 'items.price')
            ->get();

        // How many days of data exist in the last 30-day window
        $windowDays = DB::table('daily_entries')
            ->where('user_id', auth()->id())
            ->where('date', '>=', now()->subDays(30)->format('Y-m-d'))
            ->distinct('date')
            ->count('date');

        $confidence = $this->confidenceLabel($windowDays);

        return $rows->map(function ($item) use ($daysAhead, $windowDays, $confidence) {
            $divisor             = max($windowDays, 1);
            $avgDailyWaste       = $item->total_wasted / $divisor;
            $avgDailyUsed        = $item->total_used   / $divisor;
            $forecastedWaste     = $avgDailyWaste * $daysAhead;
            $forecastedWasteVal  = $forecastedWaste * $item->price;

            return (object) [
                'id'                   => $item->id,
                'name'                 => $item->name,
                'category'             => $item->category,
                'price'                => $item->price,
                'avg_daily_waste'      => round($avgDailyWaste, 3),
                'avg_daily_used'       => round($avgDailyUsed,  3),
                'forecasted_waste'     => round($forecastedWaste, 2),
                'forecasted_waste_val' => round($forecastedWasteVal, 2),
                'days_ahead'           => $daysAhead,
                'window_days'          => $windowDays,
                'confidence'           => $confidence,
            ];
        })
        ->filter(fn($i) => $i->avg_daily_waste > 0)
        ->sortByDesc('forecasted_waste_val')
        ->values();
    }

    // ─────────────────────────────────────────────────────────────
    // 2. WASTE TREND / RISK SCORE  (is waste getting worse?)
    //    Based on: early-half vs recent-half waste rate in last 30 days
    // ─────────────────────────────────────────────────────────────
    public function getRiskScores(): Collection
    {
        $midpoint = now()->subDays(15)->format('Y-m-d');
        $start    = now()->subDays(30)->format('Y-m-d');

        // Early half (days 30–16 ago)
        $early = DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items',         'entry_items.item_id',        '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->whereBetween('daily_entries.date', [$start, $midpoint])
            ->select('items.id')
            ->selectRaw('SUM(entry_items.wasted_quantity) as wasted')
            ->selectRaw('SUM(entry_items.used_quantity)   as used')
            ->groupBy('items.id')
            ->get()
            ->keyBy('id');

        // Recent half (last 15 days)
        $recent = DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items',         'entry_items.item_id',        '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->where('daily_entries.date', '>=', $midpoint)
            ->select('items.id', 'items.name', 'items.category', 'items.price')
            ->selectRaw('SUM(entry_items.wasted_quantity) as wasted')
            ->selectRaw('SUM(entry_items.used_quantity)   as used')
            ->groupBy('items.id', 'items.name', 'items.category', 'items.price')
            ->get();

        return $recent->map(function ($item) use ($early) {
            $earlyRow  = $early->get($item->id);
            $earlyTotal = $earlyRow ? ($earlyRow->used + $earlyRow->wasted) : 0;
            $earlyRate  = $earlyTotal > 0 ? ($earlyRow->wasted / $earlyTotal) * 100 : 0;

            $recentTotal = $item->used + $item->wasted;
            $recentRate  = $recentTotal > 0 ? ($item->wasted / $recentTotal) * 100 : 0;

            $trend = $recentRate - $earlyRate;  // positive = getting worse

            [$riskLevel, $riskColor, $riskIcon] = match (true) {
                $trend  >  15 => ['Critical',   'danger',  '🚨'],
                $trend  >   5 => ['Rising',     'warning', '📈'],
                $trend  <  -5 => ['Improving',  'success', '📉'],
                default       => ['Stable',     'neutral', '➡️'],
            };

            return (object) [
                'id'          => $item->id,
                'name'        => $item->name,
                'category'    => $item->category,
                'price'       => $item->price,
                'early_rate'  => round($earlyRate,  1),
                'recent_rate' => round($recentRate, 1),
                'trend'       => round($trend, 1),
                'risk_level'  => $riskLevel,
                'risk_color'  => $riskColor,
                'risk_icon'   => $riskIcon,
            ];
        })
        ->filter(fn($i) => $i->recent_rate > 0 || $i->early_rate > 0)
        ->sortByDesc(fn($i) => abs($i->trend))
        ->values();
    }

    // ─────────────────────────────────────────────────────────────
    // 3. PROJECTED MONETARY LOSS  (next N days)
    //    Based on: average weekly waste cost extrapolated forward
    // ─────────────────────────────────────────────────────────────
    public function getProjectedLoss(int $daysAhead = 30): array
    {
        // Collect weekly waste totals over the last 8 weeks
        $weeks = collect(range(0, 7))->map(function ($weeksAgo) {
            return (float) DB::table('entry_items')
                ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
                ->join('items',         'entry_items.item_id',        '=', 'items.id')
                ->where('daily_entries.user_id', auth()->id())
                ->whereBetween('daily_entries.date', [
                    now()->subWeeks($weeksAgo)->startOfWeek()->format('Y-m-d'),
                    now()->subWeeks($weeksAgo)->endOfWeek()->format('Y-m-d'),
                ])
                ->sum(DB::raw('entry_items.wasted_quantity * items.price'));
        })->filter(fn($v) => $v > 0);

        if ($weeks->isEmpty()) {
            return [
                'avg_weekly_loss'       => 0,
                'projected_loss'        => 0,
                'days_ahead'            => $daysAhead,
                'weeks_of_data'         => 0,
                'confidence'            => 'insufficient',
                'trend_direction'       => 'stable',
                'trend_pct'             => 0,
            ];
        }

        $avgWeeklyLoss   = $weeks->average();
        $projectedLoss   = ($avgWeeklyLoss / 7) * $daysAhead;

        // Trend: compare most recent week vs average of older weeks
        $recentWeekLoss  = $weeks->first();
        $olderAvg        = $weeks->count() > 1 ? $weeks->skip(1)->average() : $avgWeeklyLoss;
        $trendPct        = $olderAvg > 0 ? (($recentWeekLoss - $olderAvg) / $olderAvg) * 100 : 0;
        $trendDirection  = $trendPct > 5 ? 'worsening' : ($trendPct < -5 ? 'improving' : 'stable');

        $windowDays = DB::table('daily_entries')
            ->where('user_id', auth()->id())
            ->where('date', '>=', now()->subDays(56)->format('Y-m-d'))
            ->distinct('date')
            ->count('date');

        return [
            'avg_weekly_loss'  => round($avgWeeklyLoss, 2),
            'projected_loss'   => round($projectedLoss, 2),
            'days_ahead'       => $daysAhead,
            'weeks_of_data'    => $weeks->count(),
            'confidence'       => $this->confidenceLabel($windowDays),
            'trend_direction'  => $trendDirection,
            'trend_pct'        => round(abs($trendPct), 1),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // 4. DAY-OF-WEEK PATTERNS
    //    Based on: avg wasted_quantity grouped by DAYOFWEEK(date)
    // ─────────────────────────────────────────────────────────────
    public function getDayOfWeekPatterns(): Collection
    {
        $rows = DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items',         'entry_items.item_id',        '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->selectRaw('DAYOFWEEK(daily_entries.date) as dow')
            ->selectRaw('SUM(entry_items.wasted_quantity * items.price) as total_waste_val')
            ->selectRaw('COUNT(DISTINCT daily_entries.date) as entry_count')
            ->groupBy(DB::raw('DAYOFWEEK(daily_entries.date)'))
            ->get()
            ->keyBy('dow');

        $dayNames = [1 => 'Sun', 2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri', 7 => 'Sat'];

        return collect(range(1, 7))->map(function ($dow) use ($rows, $dayNames) {
            $row        = $rows->get($dow);
            $entryCount = $row ? max($row->entry_count, 1) : 1;
            $avgWaste   = $row ? ($row->total_waste_val / $entryCount) : 0;

            return (object) [
                'dow'       => $dow,
                'day_name'  => $dayNames[$dow],
                'avg_waste' => round($avgWaste, 2),
                'entries'   => $row ? $row->entry_count : 0,
            ];
        });
    }

    // ─────────────────────────────────────────────────────────────
    // 5. PROCUREMENT SUGGESTIONS
    //    Based on: (total_used / days) = ideal order qty
    //              (total_used + total_wasted) / days = current implied order qty
    //    Suggested reduction = current implied - ideal
    // ─────────────────────────────────────────────────────────────
    public function getProcurementSuggestions(): Collection
    {
        $windowDays = 30;

        $rows = DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items',         'entry_items.item_id',        '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->where('daily_entries.date', '>=', now()->subDays($windowDays)->format('Y-m-d'))
            ->select('items.id', 'items.name', 'items.category', 'items.price')
            ->selectRaw('SUM(entry_items.used_quantity)   as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('COUNT(DISTINCT daily_entries.date) as active_days')
            ->groupBy('items.id', 'items.name', 'items.category', 'items.price')
            ->get();

        $activeDays = max(
            DB::table('daily_entries')
                ->where('user_id', auth()->id())
                ->where('date', '>=', now()->subDays($windowDays)->format('Y-m-d'))
                ->distinct('date')
                ->count('date'),
            1
        );

        return $rows
            ->filter(fn($i) => $i->total_wasted > 0)
            ->map(function ($item) use ($activeDays) {
                $idealDailyOrder   = $item->total_used   / $activeDays;
                $currentDailyOrder = ($item->total_used + $item->total_wasted) / $activeDays;
                $reductionQty      = max(0, $currentDailyOrder - $idealDailyOrder);
                $reductionPct      = $currentDailyOrder > 0
                    ? ($reductionQty / $currentDailyOrder) * 100
                    : 0;
                $savingPerDay      = $reductionQty * $item->price;

                return (object) [
                    'id'                   => $item->id,
                    'name'                 => $item->name,
                    'category'             => $item->category,
                    'price'                => $item->price,
                    'current_daily_order'  => round($currentDailyOrder, 3),
                    'ideal_daily_order'    => round($idealDailyOrder,   3),
                    'suggested_reduction'  => round($reductionQty,      3),
                    'reduction_pct'        => round($reductionPct,       1),
                    'saving_per_day'       => round($savingPerDay,       2),
                    'saving_per_month'     => round($savingPerDay * 30,  2),
                ];
            })
            ->filter(fn($i) => $i->reduction_pct >= 5) // only surface meaningful reductions
            ->sortByDesc('saving_per_month')
            ->values();
    }

    // ─────────────────────────────────────────────────────────────
    // HELPER: confidence label based on days of data available
    // ─────────────────────────────────────────────────────────────
    private function confidenceLabel(int $days): string
    {
        return match (true) {
            $days < self::MIN_DAYS_FOR_PREDICTION => 'insufficient',
            $days < 30  => 'low',
            $days < 90  => 'moderate',
            default     => 'high',
        };
    }
}