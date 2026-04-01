<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordController extends Controller
{
    /**
     * Display historical records with sorting options.
     */
    public function index(Request $request): View
    {
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');

        $query = DailyEntry::with('entryItems.item');

        if ($sortBy === 'date') {
            $query->orderBy('date', $sortOrder);
        } elseif ($sortBy === 'name') {
            // Sort by item name requires joining with entry_items and items
            $query->join('entry_items', 'daily_entries.id', '=', 'entry_items.daily_entry_id')
                  ->join('items', 'entry_items.item_id', '=', 'items.id')
                  ->select('daily_entries.*')
                  ->distinct()
                  ->orderBy('items.name', $sortOrder);
        }

        $entries = $query->paginate(50);

        // Store sort preferences in session
        session(['records_sort_by' => $sortBy, 'records_sort_order' => $sortOrder]);

        return view('records.index', compact('entries', 'sortBy', 'sortOrder'));
    }
}
