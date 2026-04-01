<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{

    /**
     * Get most wasted items.
     */
    public function getMostWastedItems(int $limit = 10): Collection
    {
        return DB::table('items')
            ->join('entry_items', 'items.id', '=', 'entry_items.item_id')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->where('items.user_id', auth()->id())
            ->select(
                'items.id',
                'items.name',
                'items.category',
                'items.unit',
                'items.unit_quantity'
            )
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->groupBy(
                'items.id',
                'items.name',
                'items.category',
                'items.unit',
                'items.unit_quantity'
            )
            ->orderByDesc('total_wasted')
            ->limit($limit)
            ->get()
            ->map(function ($item) {

                $total = $item->total_used + $item->total_wasted;

                $item->waste_rating =
                    $total > 0
                    ? ($item->total_wasted / $total) * 100
                    : 0;

                return $item;
            });
    }

    /**
     * Get most used items.
     */
    public function getMostUsedItems(int $limit = 10): Collection
    {
        return DB::table('items')
            ->join('entry_items', 'items.id', '=', 'entry_items.item_id')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->where('items.user_id', auth()->id())
            ->select(
                'items.id',
                'items.name',
                'items.category',
                'items.unit',
                'items.unit_quantity'
            )
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->groupBy(
                'items.id',
                'items.name',
                'items.category',
                'items.unit',
                'items.unit_quantity'
            )
            ->orderByDesc('total_used')
            ->limit($limit)
            ->get()
            ->map(function ($item) {

                $total = $item->total_used + $item->total_wasted;

                $item->waste_rating =
                    $total > 0
                    ? ($item->total_wasted / $total) * 100
                    : 0;

                return $item;
            });
    }

    /**
     * Usage vs Waste comparison.
     */
    public function getUsageComparison(?string $dateRange = null): Collection
    {

        $query = DB::table('items')
            ->join('entry_items', 'items.id', '=', 'entry_items.item_id')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->where('items.user_id', auth()->id())
            ->select(
                'items.id',
                'items.name',
                'items.category',
                'items.unit'
            )
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted');

        if ($dateRange) {
            $query->where('daily_entries.date', '>=', $dateRange);
        }

        return $query
            ->groupBy(
                'items.id',
                'items.name',
                'items.category',
                'items.unit'
            )
            ->get()
            ->map(function ($item) {

                $total = $item->total_used + $item->total_wasted;

                $item->waste_rating =
                    $total > 0
                    ? ($item->total_wasted / $total) * 100
                    : 0;

                return $item;
            });
    }

    /**
     * Daily / Weekly / Monthly statistics
     */
    public function getTimePeriodStatistics(string $period = 'daily', ?string $dateRange = null): Collection
    {

        $query = DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items', 'entry_items.item_id', '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->select('items.name')
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted');

        if ($dateRange) {
            $query->where('daily_entries.date', '>=', $dateRange);
        }

        switch ($period) {

            case 'weekly':

                $query->selectRaw('YEARWEEK(daily_entries.date) as period')
                    ->selectRaw('MIN(daily_entries.date) as period_date')
                    ->groupBy('items.name', DB::raw('YEARWEEK(daily_entries.date)'));

                break;

            case 'monthly':

                $query->selectRaw('YEAR(daily_entries.date) as year')
                    ->selectRaw('MONTH(daily_entries.date) as month')
                    ->selectRaw('MIN(daily_entries.date) as period_date')
                    ->groupBy('items.name', DB::raw('YEAR(daily_entries.date)'), DB::raw('MONTH(daily_entries.date)'));

                break;

            default:

                $query->selectRaw('daily_entries.date as period_date')
                    ->groupBy('items.name', 'daily_entries.date');
        }

        return $query
            ->orderBy('period_date', 'desc')
            ->get();
    }

    public function getMeaningfulInsights(): array
{
    $insights = [];

    $mostWasted = $this->getMostWastedItems(1)->first();
    $mostUsed = $this->getMostUsedItems(1)->first();
    $comparison = $this->getUsageComparison();

    $totalUsed = $comparison->sum('total_used');
    $totalWasted = $comparison->sum('total_wasted');
    $overallWasteRate = ($totalUsed + $totalWasted) > 0
        ? ($totalWasted / ($totalUsed + $totalWasted)) * 100
        : 0;

    $highestWasteRate = $comparison->sortByDesc('waste_rating')->first();
    $trackedItems = $comparison->count();

    // 1. Waste Priority
    if ($mostWasted) {
        $insights[] = [
            'type' => 'warning',
            'icon' => '⚠️',
            'title' => 'Waste Priority',
            'message' => "{$mostWasted->name} contributes the highest total waste with " . number_format($mostWasted->total_wasted, 2) . " units, making it the primary source of inventory loss.",
        ];
    }

    // 2. Strong Performer
    if ($mostUsed) {
        $insights[] = [
            'type' => 'success',
            'icon' => '🔥',
            'title' => 'Strong Performer',
            'message' => "{$mostUsed->name} is the most used item with " . number_format($mostUsed->total_used, 2) . " units consumed, showing strong demand and consistent turnover.",
        ];
    }

    // 3. Efficiency Warning
    if ($highestWasteRate) {
        $insights[] = [
            'type' => $highestWasteRate->waste_rating >= 25 ? 'danger' : 'info',
            'icon' => '📉',
            'title' => 'Efficiency Warning',
            'message' => "{$highestWasteRate->name} has the highest waste rate at " . number_format($highestWasteRate->waste_rating, 1) . "%, indicating possible overstocking, low demand, or spoilage risk.",
        ];
    }

    // 4. Inventory Health
    if (($totalUsed + $totalWasted) > 0) {
        if ($overallWasteRate < 10) {
            $healthMessage = "Overall inventory performance is excellent with a low waste rate of " . number_format($overallWasteRate, 1) . "%.";
            $healthType = 'success';
            $healthIcon = '✅';
        } elseif ($overallWasteRate <= 20) {
            $healthMessage = "Overall inventory performance is acceptable with a moderate waste rate of " . number_format($overallWasteRate, 1) . "%, but some items may still need optimization.";
            $healthType = 'info';
            $healthIcon = '📊';
        } else {
            $healthMessage = "Overall inventory waste is high at " . number_format($overallWasteRate, 1) . "%, which may indicate inefficient purchasing or stock handling.";
            $healthType = 'danger';
            $healthIcon = '🚨';
        }

        $insights[] = [
            'type' => $healthType,
            'icon' => $healthIcon,
            'title' => 'Inventory Health',
            'message' => $healthMessage,
        ];
    }

    // 5. Recommended Action (MAIN FEATURE)
    if ($highestWasteRate && $highestWasteRate->waste_rating >= 25) {
        $insights[] = [
            'type' => 'danger',
            'icon' => '🎯',
            'title' => 'Recommended Action',
            'message' => "Reduce the restocking quantity of {$highestWasteRate->name} and monitor its daily consumption trend before the next inventory cycle, since its " . number_format($highestWasteRate->waste_rating, 1) . "% waste rate suggests overstocking or inefficient turnover.",
        ];
    } elseif ($mostWasted && $mostWasted->total_wasted > 0) {
        $insights[] = [
            'type' => 'warning',
            'icon' => '🎯',
            'title' => 'Recommended Action',
            'message' => "Review the purchasing quantity, storage condition, and handling process for {$mostWasted->name}, since it contributes the highest waste volume in the current dataset.",
        ];
    }

    // 6. Dataset Reliability
    if ($trackedItems < 5) {
        $insights[] = [
            'type' => 'info',
            'icon' => '🧾',
            'title' => 'Dataset Note',
            'message' => "Insights are currently based on {$trackedItems} tracked item(s). Adding more inventory records will improve the reliability and accuracy of future analytics.",
        ];
    }

    return $insights;
    }
}