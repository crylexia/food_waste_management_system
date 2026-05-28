<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BusinessIntelligenceService
{
    // Memoised per-request to avoid running the same heavy join multiple times
    private ?Collection $cachedComparison   = null;
    private ?Collection $cachedByReason     = null;
    private ?string $dateRange              = null;

    public function __construct(protected AnalyticsService $analyticsService) {}

    /**
     * Set the date range filter for all KPI/BI calculations.
     * Call this before any get* methods.
     */
    public function setDateRange(?string $dateRange): static
    {
        // Invalidate cache if the range changes
        if ($this->dateRange !== $dateRange) {
            $this->cachedComparison = null;
            $this->cachedByReason   = null;
            $this->dateRange        = $dateRange;
        }
        return $this;
    }

    private function comparison(): Collection
    {
        return $this->cachedComparison ??= $this->analyticsService->getUsageComparison($this->dateRange);
    }

    private function comparisonByReason(): Collection
    {
        return $this->cachedByReason ??= $this->analyticsService->getUsageComparisonByReason($this->dateRange);
    }

    public function getKPIs(): array
    {
        $comparison     = $this->comparison();
        $totalQtyUsed   = $comparison->sum('total_used');
        $totalQtyWasted = $comparison->sum('total_wasted');
        $totalWastedVal = $comparison->sum('wasted_value');
        $total          = $totalQtyUsed + $totalQtyWasted;

        $wasteRate       = $total > 0 ? ($totalQtyWasted / $total) * 100 : 0;
        $utilizationRate = $total > 0 ? ($totalQtyUsed   / $total) * 100 : 0;
        $efficiencyScore = (int) max(0, min(100, round(100 - ($wasteRate * 1.5))));
        $costPerWasteUnit = $totalQtyWasted > 0 ? $totalWastedVal / $totalQtyWasted : 0;

        $color = match (true) {
            $efficiencyScore >= 85 => 'success',
            $efficiencyScore >= 70 => 'info',
            $efficiencyScore >= 50 => 'warning',
            default                => 'danger',
        };

        return [
            'efficiency_score' => $efficiencyScore,
            'efficiency_label' => match (true) {
                $efficiencyScore >= 85 => 'Excellent',
                $efficiencyScore >= 70 => 'Good',
                $efficiencyScore >= 50 => 'Needs Improvement',
                default                => 'Critical',
            },
            'efficiency_color'  => $color,
            'utilization_rate'  => round($utilizationRate, 1),
            'waste_rate'        => round($wasteRate, 1),
            'cost_per_waste'    => $costPerWasteUnit,
            'revenue_loss'      => $totalWastedVal,
            'total_used_value'  => $comparison->sum('used_value'),
            'tracked_items'     => $comparison->count(),
        ];
    }

    public function getDecisionCards(): array
    {
        $cards      = [];
        $comparison = $this->comparison();
        $totalLoss  = $comparison->sum('wasted_value');
        $kpis       = $this->getKPIs();

        $thisWeek  = $this->weeklyWaste(0);
        $lastWeek  = $this->weeklyWaste(1);
        $weekDelta = $lastWeek > 0 ? (($thisWeek - $lastWeek) / $lastWeek) * 100 : null;

        $topLoss = $comparison->sortByDesc('wasted_value')->first();
        if ($topLoss && $totalLoss > 0) {
            $pct = ($topLoss->wasted_value / $totalLoss) * 100;
            if ($pct >= 30) {
                $cards[] = [
                    'type'   => 'critical',
                    'icon'   => '🚨',
                    'label'  => 'Critical Issue',
                    'title'  => "{$topLoss->name} is responsible for " . number_format($pct, 1) . "% of all losses",
                    'detail' => "₱" . number_format($topLoss->wasted_value, 2) . " wasted from this single item.",
                    'action' => "Reduce procurement or adjust production quantity immediately.",
                ];
            }
        }

        if ($weekDelta !== null && $weekDelta > 5) {
            $cards[] = [
                'type'   => 'warning',
                'icon'   => '📈',
                'label'  => 'Waste Increasing',
                'title'  => "Waste up " . number_format($weekDelta, 1) . "% this week",
                'detail' => "₱" . number_format($thisWeek, 2) . " this week vs ₱" . number_format($lastWeek, 2) . " last week.",
                'action' => "Review production quantities and storage conditions immediately.",
            ];
        }

        if ($weekDelta !== null && $weekDelta < -5) {
            $cards[] = [
                'type'   => 'improvement',
                'icon'   => '📉',
                'label'  => 'Improvement',
                'title'  => "Waste down " . number_format(abs($weekDelta), 1) . "% this week",
                'detail' => "Saved ₱" . number_format($lastWeek - $thisWeek, 2) . " vs last week.",
                'action' => "Identify what changed and standardize the practice.",
            ];
        }

        $star = $comparison->filter(fn($i) => $i->total_used >= 5)->sortBy('waste_rating')->first();
        if ($star && $star->waste_rating <= 5) {
            $cards[] = [
                'type'   => 'improvement',
                'icon'   => '⭐',
                'label'  => 'Top Performer',
                'title'  => "{$star->name} — only " . number_format($star->waste_rating, 1) . "% waste rate",
                'detail' => "₱" . number_format($star->used_value, 2) . " in value fully utilized.",
                'action' => "Use this item's practices as the operational benchmark.",
            ];
        }

        $cards[] = [
            'type'   => $kpis['efficiency_color'],
            'icon'   => match ($kpis['efficiency_color']) {
                'success' => '✅', 'info' => '📊', 'warning' => '⚠️', default => '🚨'
            },
            'label'  => 'Inventory Health',
            'title'  => "Efficiency score: {$kpis['efficiency_score']}/100 — {$kpis['efficiency_label']}",
            'detail' => "Utilization: {$kpis['utilization_rate']}%  ·  Revenue loss: ₱" . number_format($kpis['revenue_loss'], 2),
            'action' => $kpis['efficiency_score'] >= 70
                ? "Maintain current practices and monitor weekly."
                : "Apply the recommended actions below to reduce loss.",
        ];

        return $cards;
    }

    public function getRecommendations(): array
    {
        $recs       = [];
        $comparison = $this->comparison();
        $byReason   = $this->comparisonByReason()
                        ->groupBy('id'); // group by item id so we can look up reasons per item

        foreach ($comparison->filter(fn($i) => $i->waste_rating >= 30 && $i->total_wasted > 0)
                            ->sortByDesc('wasted_value')->take(3) as $item) {

            $reasons = $byReason->get($item->id, collect())
                                ->pluck('waste_reason')
                                ->filter()
                                ->unique()
                                ->values();

            $cut = round($item->waste_rating * 0.6);

            // Tailor the action based on logged reasons
            if ($reasons->contains('expired') || $reasons->contains('spoiled')) {
                $action = "Review storage conditions and FIFO rotation for {$item->name} — logged as expired/spoiled.";
            } elseif ($reasons->contains('overproduced')) {
                $action = "Reduce procurement of {$item->name} by ~{$cut}% — consistently logged as overproduced.";
            } elseif ($reasons->contains('leftover')) {
                $action = "Switch {$item->name} to smaller batches or on-demand — frequently logged as leftover.";
            } else {
                $action = "Reduce procurement of {$item->name} by ~{$cut}% based on waste rate.";
            }

            $recs[] = [
                'priority'         => 'high',
                'icon'             => '📉',
                'action'           => $action,
                'reason'           => number_format($item->waste_rating, 1) . "% waste rate · ₱" . number_format($item->wasted_value, 2) . " lost",
                'estimated_saving' => $item->wasted_value * 0.5,
            ];
        }

        foreach ($comparison->filter(fn($i) => $i->total_used < 5 && $i->total_wasted > 0)
                            ->sortByDesc('wasted_value')->take(2) as $item) {

            $reasons = $byReason->get($item->id, collect())
                                ->pluck('waste_reason')
                                ->filter()
                                ->unique()
                                ->values();

            $action = $reasons->contains('expired') || $reasons->contains('spoiled')
                ? "Check shelf life and storage for {$item->name} — low demand and logged as expired/spoiled."
                : "Switch {$item->name} to on-demand or smaller batches — low usage with notable waste.";

            $recs[] = [
                'priority'         => 'medium',
                'icon'             => '🔍',
                'action'           => $action,
                'reason'           => number_format($item->total_used, 1) . " units used vs " . number_format($item->total_wasted, 1) . " wasted",
                'estimated_saving' => $item->wasted_value * 0.4,
            ];
        }

        $coveredIds = $comparison->filter(fn($i) => $i->waste_rating >= 30)->pluck('id');
        $highVal    = $comparison->whereNotIn('id', $coveredIds)->sortByDesc('wasted_value')->first();
        if ($highVal && $highVal->wasted_value > 0) {
            $recs[] = [
                'priority'         => 'medium',
                'icon'             => '🏪',
                'action'           => "Review storage and FIFO compliance for {$highVal->name}",
                'reason'           => "Highest uncovered monetary loss at ₱" . number_format($highVal->wasted_value, 2),
                'estimated_saving' => $highVal->wasted_value * 0.3,
            ];
        }

        return collect($recs)
            ->sortByDesc(fn($r) => $r['priority'] === 'high' ? 1 : 0)
            ->values()
            ->toArray();
    }

    public function getRootCauseAnalysis(): array
    {
        $items = $this->comparisonByReason();

        $overproduction = $items->filter(fn($i) => $i->waste_reason === 'overproduced');
        $lowDemand      = $items->filter(fn($i) => $i->waste_reason === 'leftover');
        $highSpoilage   = $items->filter(fn($i) => in_array($i->waste_reason, ['expired', 'spoiled']));
        $other          = $items->filter(fn($i) => $i->waste_reason === 'other' || $i->waste_reason === null);

        $allItems   = $this->comparison();
        $badItemIds = $items->filter(fn($i) => $i->waste_reason !== null)->pluck('id')->unique();
        $efficient  = $allItems->filter(
            fn($i) => $i->waste_rating < 10
                   && $i->total_used >= 5
                   && !$badItemIds->contains($i->id)
        );

        return [
            [
                'cause'        => 'Overproduction',
                'icon'         => '🏭',
                'color'        => 'danger',
                'count'        => $overproduction->pluck('name')->unique()->count(),
                'items'        => $overproduction->pluck('name')->unique()->take(3)->toArray(),
                'wasted_value' => $overproduction->sum('wasted_value'),
                'description'  => 'Items explicitly logged as overproduced — production consistently exceeds actual demand.',
                'action'       => 'Calibrate batch sizes using historical consumption averages.',
            ],
            [
                'cause'        => 'Leftover / Poor Forecasting',
                'icon'         => '📉',
                'color'        => 'warning',
                'count'        => $lowDemand->pluck('name')->unique()->count(),
                'items'        => $lowDemand->pluck('name')->unique()->take(3)->toArray(),
                'wasted_value' => $lowDemand->sum('wasted_value'),
                'description'  => 'Items logged as leftover — demand is consistently overestimated at ordering time.',
                'action'       => 'Switch to smaller batches or on-demand preparation.',
            ],
            [
                'cause'        => 'Expired / Spoiled',
                'icon'         => '🧊',
                'color'        => 'warning',
                'count'        => $highSpoilage->pluck('name')->unique()->count(),
                'items'        => $highSpoilage->pluck('name')->unique()->take(3)->toArray(),
                'wasted_value' => $highSpoilage->sum('wasted_value'),
                'description'  => 'Items logged as expired or spoiled — likely caused by storage conditions or short shelf life.',
                'action'       => 'Review cold chain procedures and FIFO rotation compliance.',
            ],
            [
                'cause'        => 'Other / Untagged',
                'icon'         => '❓',
                'color'        => 'neutral',
                'count'        => $other->pluck('name')->unique()->count(),
                'items'        => $other->pluck('name')->unique()->take(3)->toArray(),
                'wasted_value' => $other->sum('wasted_value'),
                'description'  => 'Waste with no specific reason logged. Tagging waste reasons improves analytics accuracy.',
                'action'       => 'Encourage staff to select a waste reason when logging wasted quantities.',
            ],
            [
                'cause'        => 'Well-Managed',
                'icon'         => '✅',
                'color'        => 'success',
                'count'        => $efficient->count(),
                'items'        => $efficient->pluck('name')->take(3)->toArray(),
                'wasted_value' => 0,
                'description'  => 'Items with low waste, consistent usage, and no negative reason tags — your operational benchmarks.',
                'action'       => 'Document and replicate these management practices across other items.',
            ],
        ];
    }

    public function getItemPerformanceIntelligence(): Collection
{
        $byReason = $this->comparisonByReason()
                        ->groupBy('id');

        return $this->comparison()
            ->map(function ($item) use ($byReason) {
                $totalVal = $item->used_value + $item->wasted_value;
                $item->profitability_score = $totalVal > 0
                    ? round(($item->used_value / $totalVal) * 100, 1)
                    : 0;

                $reasons    = $byReason->get($item->id, collect())
                                    ->pluck('waste_reason')
                                    ->filter()
                                    ->unique()
                                    ->values();

                $highDemand = $item->total_used >= 10;
                $highWaste  = $item->waste_rating >= 25;
                $lowDemand  = $item->total_used < 5;

                // Reason-driven classification takes priority over thresholds
                [$item->class_key, $item->class_label, $item->class_color, $item->class_icon] = match (true) {
                    $reasons->contains('expired') || $reasons->contains('spoiled')
                        => ['spoilage',  'Spoilage Issue',      'warning', '🧊'],
                    $reasons->contains('overproduced')
                        => ['overprod',  'Overproduction Risk', 'danger',  '🏭'],
                    $reasons->contains('leftover')
                        => ['leftover',  'Leftover Issue',      'warning', '📦'],
                    // Fall back to threshold-based classification
                    $item->waste_rating >= 50
                        => ['critical',  'Critical Waste',      'danger',  '🚨'],
                    $highDemand && $highWaste
                        => ['overprod',  'Overproduction Risk', 'danger',  '🏭'],
                    $lowDemand && $item->total_wasted > 0
                        => ['low_dem',   'Low Demand',          'warning', '📦'],
                    $highDemand && !$highWaste
                        => ['star',      'Star Item',           'success', '⭐'],
                    default
                        => ['normal',    'Normal',              'neutral', '📊'],
                };

                return $item;
            })
            ->sortByDesc('wasted_value')
            ->values();
    }

    public function getImpactEstimation(): array
    {
        $recs        = $this->getRecommendations();
        $totalSaving = collect($recs)->sum('estimated_saving');
        $currentLoss = $this->comparison()->sum('wasted_value');

        return [
            'total_saving'         => $totalSaving,
            'current_loss'         => $currentLoss,
            'projected_loss_after' => max(0, $currentLoss - $totalSaving),
            'improvement_pct'      => $currentLoss > 0 ? ($totalSaving / $currentLoss) * 100 : 0,
            'recommendation_count' => count($recs),
        ];
    }

    public function getCategoryBreakdown(): Collection
    {
        return $this->comparison()
            ->groupBy(fn($i) => $i->category ?: 'Uncategorized')
            ->map(function ($items, $category) {
                $used   = $items->sum('total_used');
                $wasted = $items->sum('total_wasted');
                $total  = $used + $wasted;
                return [
                    'category'     => $category,
                    'item_count'   => $items->count(),
                    'total_used'   => $used,
                    'total_wasted' => $wasted,
                    'used_value'   => $items->sum('used_value'),
                    'wasted_value' => $items->sum('wasted_value'),
                    'waste_rate'   => $total > 0 ? round(($wasted / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('wasted_value')
            ->values();
    }

    public function getPeriodSummary(Collection $periodStats): Collection
    {
        return $periodStats
            ->groupBy('period_date')
            ->map(function ($items, $date) {
                $used   = $items->sum('total_used');
                $wasted = $items->sum('total_wasted');
                $total  = $used + $wasted;
                return [
                    'date'         => $date,
                    'item_count'   => $items->count(),
                    'total_used'   => $used,
                    'total_wasted' => $wasted,
                    'used_value'   => $items->sum('used_value'),
                    'wasted_value' => $items->sum('wasted_value'),
                    'waste_rate'   => $total > 0 ? round(($wasted / $total) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('date')
            ->values();
    }

    private function weeklyWaste(int $weeksAgo): float
    {
        return (float) DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items', 'entry_items.item_id', '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->whereBetween('daily_entries.date', [
                now()->subWeeks($weeksAgo)->startOfWeek(),
                now()->subWeeks($weeksAgo)->endOfWeek(),
            ])
            ->sum(DB::raw('entry_items.wasted_quantity * items.price'));
    }
}