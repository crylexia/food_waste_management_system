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
    const WASTE_REASONS = [
        'expired'      => 'Expired',
        'overproduced' => 'Overproduced',
        'spoiled'      => 'Spoiled / Damaged',
        'leftover'     => 'Leftover',
        'other'        => 'Other',
    ];

    public function index(): View
    {
        $entries = DailyEntry::with('entryItems.item')
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('entries.index', compact('entries'));
    }

    public function create(): View
    {
        $items = Item::orderBy('name')->get();
        return view('entries.create', compact('items'));
    }

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
            'date'    => $validated['date'],
        ]);

        return redirect()->route('entries.show', $entry)
            ->with('success', 'Daily entry created successfully. Now add items to this entry.');
    }

    public function show(DailyEntry $entry): View
    {
        $entry->load('entryItems.item');
        $items        = Item::orderBy('name')->get();
        $wasteReasons = self::WASTE_REASONS;

        return view('entries.show', compact('entry', 'items', 'wasteReasons'));
    }

    public function destroy(DailyEntry $entry): RedirectResponse
    {
        $entry->delete();

        return redirect()->route('entries.index')
            ->with('success', 'Daily entry deleted successfully.');
    }

    public function addItem(Request $request, DailyEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'item_id'         => 'required|exists:items,id',
            'used_quantity'   => 'required|numeric|min:0',
            'wasted_quantity' => 'required|numeric|min:0',
            'waste_reason'    => ['nullable', Rule::in(array_keys(self::WASTE_REASONS))],
            'notes'           => 'nullable|string|max:500',
        ]);

        if ($validated['used_quantity'] <= 0 && $validated['wasted_quantity'] <= 0) {
            return back()->withErrors([
                'used_quantity' => 'At least one quantity (used or wasted) must be greater than zero.'
            ])->withInput();
        }

        EntryItem::create([
            'daily_entry_id'  => $entry->id,
            'item_id'         => $validated['item_id'],
            'used_quantity'   => $validated['used_quantity'],
            'wasted_quantity' => $validated['wasted_quantity'],
            'waste_reason'    => $validated['waste_reason'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Item added to entry successfully.');
    }

    public function removeItem(EntryItem $entryItem): RedirectResponse
    {
        $entryId = $entryItem->daily_entry_id;
        $entryItem->delete();

        return redirect()->route('entries.show', $entryId)
            ->with('success', 'Item removed from entry successfully.');
    }
}