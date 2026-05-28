<?php

namespace App\Http\Controllers;

use App\Models\StorageItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StorageController extends Controller
{
    public function index(): View
    {
        // UserScope on StorageItem automatically limits to auth()->id()
        $storageItems = StorageItem::with('item')
            ->orderByRaw("FIELD(status, 'active', 'depleted', 'discarded')")
            ->orderBy('expiration_date', 'asc')
            ->paginate(20);

        // Summary counts — UserScope applied automatically
        $totalActive   = StorageItem::active()->count();
        $totalExpired  = StorageItem::active()
                            ->whereNotNull('expiration_date')
                            ->where('expiration_date', '<=', today())->count();
        $totalCritical = StorageItem::active()
                    ->whereNotNull('expiration_date')
                    ->whereBetween('expiration_date', [today()->addDays(1), today()->addDays(2)])->count();
        $totalSoon = StorageItem::active()
                ->whereNotNull('expiration_date')
                ->whereBetween('expiration_date', [today()->addDays(3), today()->addDays(7)])->count();

        // Item::orderBy is already scoped to the auth user via UserScope on Item
        $items = Item::orderBy('name')->get();

        return view('storage.index', compact(
            'storageItems',
            'totalActive',
            'totalExpired',
            'totalCritical',
            'totalSoon',
            'items',
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id'         => 'required|exists:items,id',
            'quantity'        => 'required|numeric|min:0.01',
            'expiration_date' => 'nullable|date',
            'received_date'   => 'nullable|date',
            'batch_number'    => 'nullable|string|max:100',
            'notes'           => 'nullable|string|max:500',
        ]);

        StorageItem::create([
            ...$validated,
            'user_id' => auth()->id(),
            'status'  => 'active',
        ]);

        return back()->with('success', 'Item added to storage successfully.');
    }

    public function update(Request $request, StorageItem $storageItem): RedirectResponse
    {
        // UserScope ensures route-model binding only resolves records owned by
        // the authenticated user — a 404 is returned for foreign records.
        $validated = $request->validate([
            'quantity'        => 'required|numeric|min:0',
            'expiration_date' => 'nullable|date',
            'received_date'   => 'nullable|date',
            'batch_number'    => 'nullable|string|max:100',
            'notes'           => 'nullable|string|max:500',
            'status'          => 'required|in:active,depleted,discarded',
        ]);

        $storageItem->update($validated);

        return back()->with('success', 'Storage item updated successfully.');
    }

    public function destroy(StorageItem $storageItem): RedirectResponse
    {
        $storageItem->delete();

        return back()->with('success', 'Storage item removed.');
    }

    public function deplete(StorageItem $storageItem): RedirectResponse
    {
        $storageItem->update(['status' => 'depleted']);

        return back()->with('success', 'Item marked as depleted.');
    }

    public function restore(StorageItem $storageItem): RedirectResponse
    {
        $storageItem->update(['status' => 'active']);

        return back()->with('success', 'Item restored successfully.');
    }
}
