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
        $sortOrder = $request->get('sort_order', 'desc');

        $entries = DailyEntry::with('entryItems.item')
            ->orderBy('date', $sortOrder)
            ->paginate(50);

        return view('records.index', compact('entries', 'sortOrder'));
    }
}