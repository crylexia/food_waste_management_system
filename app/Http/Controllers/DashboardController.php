<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        // Get recent entries (last 10)
        $recentEntries = DailyEntry::with('entryItems.item')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Get summary statistics
        $totalItems = Item::count();
        $totalEntries = DailyEntry::count();

        // Calculate average waste rate
        $allEntries = DailyEntry::with('entryItems')->get();
        $avgWasteRate = 0;
        if ($allEntries->count() > 0) {
            $totalWasteRating = $allEntries->sum(function ($entry) {
                return $entry->waste_rating;
            });
            $avgWasteRate = $totalWasteRating / $allEntries->count();
        }

        return view('dashboard', compact('recentEntries', 'totalItems', 'totalEntries', 'avgWasteRate'));
    }
}
