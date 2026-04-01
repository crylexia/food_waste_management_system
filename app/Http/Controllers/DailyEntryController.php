<?php

namespace App\Http\Controllers;

use App\Models\DailyEntry;
use App\Models\EntryItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class DailyEntryController extends Controller
{
    /**
     * Display a listing of daily entries.
     */
    public function index(): View
    {
        $entries = DailyEntry::with('entryItems.item')
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('entries.index', compact('entries'));
    }

    /**
     * Show the form for creating a new daily entry.
     */
    public function create(): View
    {
        $items = Item::orderBy('name')->get();
        return view('entries.create', compact('items'));
    }

    /**
     * Store a newly created daily entry in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date',
                Rule::unique('daily_entries')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })
            ],
        ]);

        $entry = DailyEntry::create([
            'user_id' => auth()->id(),
            'date' => $validated['date'],
        ]);

        return redirect()->route('entries.show', $entry)
            ->with('success', 'Daily entry created successfully. Now add items to this entry.');
    }

    /**
     * Display the specified daily entry.
     */
    public function show(DailyEntry $entry): View
    {
        $entry->load('entryItems.item');
        $items = Item::orderBy('name')->get();
        
        return view('entries.show', compact('entry', 'items'));
    }

    /**
     * Remove the specified daily entry from storage.
     */
    public function destroy(DailyEntry $entry): RedirectResponse
    {
        $entry->delete();

        return redirect()->route('entries.index')
            ->with('success', 'Daily entry deleted successfully.');
    }

    /**
     * Add an item to a daily entry.
     */
    public function addItem(Request $request, DailyEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'used_quantity' => 'required|numeric|min:0',
            'wasted_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Validate that at least one quantity is greater than zero
        if ($validated['used_quantity'] <= 0 && $validated['wasted_quantity'] <= 0) {
            return back()->withErrors([
                'used_quantity' => 'At least one quantity (used or wasted) must be greater than zero.'
            ])->withInput();
        }

        EntryItem::create([
            'daily_entry_id' => $entry->id,
            'item_id' => $validated['item_id'],
            'used_quantity' => $validated['used_quantity'],
            'wasted_quantity' => $validated['wasted_quantity'],
            'notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Item added to entry successfully.');
    }

    /**
     * Remove an item from a daily entry.
     */
    public function removeItem(EntryItem $entryItem): RedirectResponse
    {
        $entryId = $entryItem->daily_entry_id;
        $entryItem->delete();

        return redirect()->route('entries.show', $entryId)
            ->with('success', 'Item removed from entry successfully.');
    }
}
