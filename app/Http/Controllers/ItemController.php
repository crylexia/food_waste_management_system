<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    /**
     * Display a listing of items.
     */
    public function index(Request $request): View
    {
        $query = Item::query();

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search by name if provided
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $items = $query->orderBy('name')->paginate(20);
        $categories = Item::distinct()->pluck('category');

        return view('items.index', compact('items', 'categories'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create(): View
    {
        return view('items.create');
    }

    /**
     * Store a newly created item in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })
            ],
            'category' => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
        ]);

        Item::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'category' => $validated['category'],
            'price' => $validated['price'] ?? 0,
        ]);

        return redirect()->route('items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified item.
     */
    public function show(Item $item): View
    {
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified item.
     */
    public function edit(Item $item): View
    {
        return view('items.edit', compact('item'));
    }

    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('items')->where(function ($query) {
                    return $query->where('user_id', auth()->id());
                })->ignore($item->id)
            ],
            'category' => 'required|string|max:100',
            'price' => 'nullable|numeric|min:0',
        ]);

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy(Item $item): RedirectResponse
    {
        if (!$item->canBeDeleted()) {
            return back()->with('error', 
                'Cannot delete this item because it is used in existing entries.');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
