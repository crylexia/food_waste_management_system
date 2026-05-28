<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getMostWastedItems(int $limit = 10): Collection
    {
        return DB::table('items')
            ->join('entry_items', 'items.id', '=', 'entry_items.item_id')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->where('items.user_id', auth()->id())
            ->select('items.id', 'items.name', 'items.category', 'items.price')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity * items.price) as wasted_value')
            ->selectRaw('SUM(entry_items.used_quantity * items.price) as used_value')
            ->groupBy('items.id', 'items.name', 'items.category', 'items.price')
            ->orderByDesc('total_wasted')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $total = $item->total_used + $item->total_wasted;
                $item->waste_rating = $total > 0
                    ? ($item->total_wasted / $total) * 100
                    : 0;
                return $item;
            });
    }

    public function getMostUsedItems(int $limit = 10): Collection
    {
        return DB::table('items')
            ->join('entry_items', 'items.id', '=', 'entry_items.item_id')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->where('items.user_id', auth()->id())
            ->select('items.id', 'items.name', 'items.category', 'items.price')
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('SUM(entry_items.used_quantity * items.price) as used_value')
            ->selectRaw('SUM(entry_items.wasted_quantity * items.price) as wasted_value')
            ->groupBy('items.id', 'items.name', 'items.category', 'items.price')
            ->orderByDesc('total_used')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $total = $item->total_used + $item->total_wasted;
                $item->waste_rating = $total > 0
                    ? ($item->total_wasted / $total) * 100
                    : 0;
                return $item;
            });
    }

    public function getUsageComparison(?string $dateRange = null): Collection
    {
        $query = DB::table('items')
            ->join('entry_items', 'items.id', '=', 'entry_items.item_id')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->where('items.user_id', auth()->id())
            ->select('items.id', 'items.name', 'items.category', 'items.price')
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('SUM(entry_items.used_quantity * items.price) as used_value')
            ->selectRaw('SUM(entry_items.wasted_quantity * items.price) as wasted_value');

        if ($dateRange) {
            $query->where('daily_entries.date', '>=', $dateRange);
        }

        return $query
            ->groupBy('items.id', 'items.name', 'items.category', 'items.price')
            ->get()
            ->map(function ($item) {
                $total = $item->total_used + $item->total_wasted;
                $item->waste_rating = $total > 0
                    ? ($item->total_wasted / $total) * 100
                    : 0;
                return $item;
            });
    }

    public function getUsageComparisonByReason(?string $dateRange = null): Collection
    {
        $query = DB::table('items')
            ->join('entry_items', 'items.id', '=', 'entry_items.item_id')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->where('items.user_id', auth()->id())
            ->select(
                'items.id',
                'items.name',
                'items.category',
                'items.price',
                'entry_items.waste_reason'
            )
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('SUM(entry_items.used_quantity * items.price) as used_value')
            ->selectRaw('SUM(entry_items.wasted_quantity * items.price) as wasted_value');

        if ($dateRange) {
            $query->where('daily_entries.date', '>=', $dateRange);
        }

        return $query
            ->groupBy(
                'items.id',
                'items.name',
                'items.category',
                'items.price',
                'entry_items.waste_reason'
            )
            ->get()
            ->map(function ($item) {
                $total = $item->total_used + $item->total_wasted;
                $item->waste_rating = $total > 0
                    ? ($item->total_wasted / $total) * 100
                    : 0;
                return $item;
            });
    }

    public function getTimePeriodStatistics(string $period = 'daily', ?string $dateRange = null): Collection
    {
        $query = DB::table('entry_items')
            ->join('daily_entries', 'entry_items.daily_entry_id', '=', 'daily_entries.id')
            ->join('items', 'entry_items.item_id', '=', 'items.id')
            ->where('daily_entries.user_id', auth()->id())
            ->select('items.name')
            ->selectRaw('SUM(entry_items.used_quantity) as total_used')
            ->selectRaw('SUM(entry_items.wasted_quantity) as total_wasted')
            ->selectRaw('SUM(entry_items.used_quantity * items.price) as used_value')
            ->selectRaw('SUM(entry_items.wasted_quantity * items.price) as wasted_value');

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

        return $query->orderBy('period_date', 'desc')->get();
    }

    public function getMeaningfulInsights(): array
    {
        $insights   = [];
        $mostWasted = $this->getMostWastedItems(1)->first();
        $mostUsed   = $this->getMostUsedItems(1)->first();
        $comparison = $this->getUsageComparison();

        $totalUsed       = $comparison->sum('total_used');
        $totalWasted     = $comparison->sum('total_wasted');
        $totalWastedValue = $comparison->sum('wasted_value');

        $overallWasteRate = ($totalUsed + $totalWasted) > 0
            ? ($totalWasted / ($totalUsed + $totalWasted)) * 100
            : 0;

        $highestWasteRate  = $comparison->sortByDesc('waste_rating')->first();
        $highestWasteValue = $comparison->sortByDesc('wasted_value')->first();
        $trackedItems      = $comparison->count();

        if ($mostWasted) {
            $insights[] = [
                'type'    => 'warning',
                'icon'    => '⚠️',
                'title'   => 'Waste Priority',
                'message' => "{$mostWasted->name} contributes the highest total waste with "
                    . number_format($mostWasted->total_wasted, 2) . " units, worth ₱"
                    . number_format($mostWasted->wasted_value, 2) . ".",
            ];
        }

        if ($mostUsed) {
            $insights[] = [
                'type'    => 'success',
                'icon'    => '🔥',
                'title'   => 'Strong Performer',
                'message' => "{$mostUsed->name} is the most used item with "
                    . number_format($mostUsed->total_used, 2) . " units consumed, worth ₱"
                    . number_format($mostUsed->used_value, 2) . ".",
            ];
        }

        if ($highestWasteRate) {
            $insights[] = [
                'type'    => $highestWasteRate->waste_rating >= 25 ? 'danger' : 'info',
                'icon'    => '📉',
                'title'   => 'Efficiency Warning',
                'message' => "{$highestWasteRate->name} has the highest waste rate at "
                    . number_format($highestWasteRate->waste_rating, 1) . "%.",
            ];
        }

        if ($highestWasteValue) {
            $insights[] = [
                'type'    => 'danger',
                'icon'    => '💸',
                'title'   => 'Highest Cost Loss',
                'message' => "{$highestWasteValue->name} caused the highest monetary loss at ₱"
                    . number_format($highestWasteValue->wasted_value, 2) . ".",
            ];
        }

        if (($totalUsed + $totalWasted) > 0) {
            if ($overallWasteRate < 10) {
                $healthMessage = "Overall inventory performance is excellent with a low waste rate of "
                    . number_format($overallWasteRate, 1) . "%. Total waste cost is ₱"
                    . number_format($totalWastedValue, 2) . ".";
                $healthType = 'success';
                $healthIcon = '✅';
            } elseif ($overallWasteRate <= 20) {
                $healthMessage = "Overall inventory performance is acceptable with a moderate waste rate of "
                    . number_format($overallWasteRate, 1) . "%. Total waste cost is ₱"
                    . number_format($totalWastedValue, 2) . ".";
                $healthType = 'info';
                $healthIcon = '📊';
            } else {
                $healthMessage = "Overall inventory waste is high at "
                    . number_format($overallWasteRate, 1) . "%. Total waste cost is ₱"
                    . number_format($totalWastedValue, 2) . ".";
                $healthType = 'danger';
                $healthIcon = '🚨';
            }

            $insights[] = [
                'type'    => $healthType,
                'icon'    => $healthIcon,
                'title'   => 'Inventory Health',
                'message' => $healthMessage,
            ];
        }

        if ($highestWasteValue && $highestWasteValue->wasted_value > 0) {
            $insights[] = [
                'type'    => 'warning',
                'icon'    => '🎯',
                'title'   => 'Recommended Action',
                'message' => "Review purchasing and storage for {$highestWasteValue->name} first because it currently creates the highest financial loss at ₱"
                    . number_format($highestWasteValue->wasted_value, 2) . ".",
            ];
        }

        if ($trackedItems < 5) {
            $insights[] = [
                'type'    => 'info',
                'icon'    => '🧾',
                'title'   => 'Dataset Note',
                'message' => "Insights are currently based on {$trackedItems} tracked item(s). Adding more inventory records will improve analytics accuracy.",
            ];
        }

        return $insights;
    }
}