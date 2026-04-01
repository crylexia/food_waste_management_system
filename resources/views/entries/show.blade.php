@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Daily Entry - {{ $entry->date->format('Y-m-d') }}</h1>
    <a href="{{ route('entries.index') }}" class="btn btn-secondary">Back to Entries</a>
</div>

<div class="card">
    <div class="card-header">Add Item to Entry</div>
    <form method="POST" action="{{ route('entries.items.store', $entry) }}">
        @csrf

        <div class="entry-form-grid">
            <div class="form-group">
                <label for="item_id">Select Item *</label>
                <select name="item_id" id="item_id" class="form-control @error('item_id') error @enderror" required>
                    <option value="">-- Select an item --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} ({{ $item->category }})
                        </option>
                    @endforeach
                </select>
                @error('item_id')
                    <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="used_quantity">Used Quantity *</label>
                <input 
                    type="number" 
                    id="used_quantity" 
                    name="used_quantity" 
                    class="form-control @error('used_quantity') error @enderror" 
                    value="{{ old('used_quantity', 0) }}" 
                    step="0.01"
                    min="0"
                    required
                >
                @error('used_quantity')
                    <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="wasted_quantity">Wasted Quantity *</label>
                <input 
                    type="number" 
                    id="wasted_quantity" 
                    name="wasted_quantity" 
                    class="form-control @error('wasted_quantity') error @enderror" 
                    value="{{ old('wasted_quantity', 0) }}" 
                    step="0.01"
                    min="0"
                    required
                >
                @error('wasted_quantity')
                    <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="notes">Notes (optional)</label>
                <input 
                    type="text" 
                    id="notes" 
                    name="notes" 
                    class="form-control @error('notes') error @enderror" 
                    value="{{ old('notes') }}" 
                    maxlength="500"
                    placeholder="e.g., Stale, Expired, etc."
                >
                @error('notes')
                    <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Add Item</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        Current Items ({{ $entry->entryItems->count() }})
        @if($entry->entryItems->count() > 0)
            <span style="float: right; color: #757575; font-weight: normal;">
                Waste Rate: {{ number_format($entry->waste_rating, 2) }}%
            </span>
        @endif
    </div>

    @if($entry->entryItems->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Used</th>
                    <th>Wasted</th>
                    <th>Waste Rate</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entry->entryItems as $entryItem)
                    <tr>
                        <td>{{ $entryItem->item->name }}</td>
                        <td>{{ number_format($entryItem->used_quantity, 2) }}</td>
                        <td>{{ number_format($entryItem->wasted_quantity, 2) }}</td>
                        <td>{{ number_format($entryItem->waste_rating, 2) }}%</td>
                        <td>{{ $entryItem->notes ?? '-' }}</td>
                        <td>
                            <form action="{{ route('entry-items.destroy', $entryItem) }}" method="POST" 
                                  onsubmit="return confirm('Are you sure you want to remove this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="padding: 20px; background: #F5F5F5; margin-top: 15px; border-radius: 4px;">
            <strong>Totals:</strong>
            Used: {{ number_format($entry->entryItems->sum('used_quantity'), 2) }} | 
            Wasted: {{ number_format($entry->entryItems->sum('wasted_quantity'), 2) }} | 
            Waste Rate: {{ number_format($entry->waste_rating, 2) }}%
        </div>
    @else
        <p class="text-center" style="padding: 40px; color: #757575;">
            No items added yet. Use the form above to add items to this entry.
        </p>
    @endif
</div>

<style>
.entry-form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 2fr;
    gap: 15px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .entry-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
