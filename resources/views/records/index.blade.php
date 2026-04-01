@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Historical Records</h1>
</div>

<div class="card">
    <form method="GET" action="{{ route('records.index') }}" class="sort-form">
        <div class="sort-row">
            <div class="form-group">
                <label for="sort_by">Sort by</label>
                <select name="sort_by" id="sort_by" class="form-control" onchange="this.form.submit()">
                    <option value="date" {{ $sortBy == 'date' ? 'selected' : '' }}>Date</option>
                    <option value="name" {{ $sortBy == 'name' ? 'selected' : '' }}>Item Name</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sort_order">Order</label>
                <select name="sort_order" id="sort_order" class="form-control" onchange="this.form.submit()">
                    <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>
                        {{ $sortBy == 'date' ? 'Newest First' : 'Z to A' }}
                    </option>
                    <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>
                        {{ $sortBy == 'date' ? 'Oldest First' : 'A to Z' }}
                    </option>
                </select>
            </div>
        </div>
    </form>
</div>

@if($entries->count() > 0)
    @foreach($entries as $entry)
        <div class="card">
            <div class="entry-header">
                <h3>{{ $entry->date->format('F d, Y') }}</h3>
                <span class="waste-badge">Waste Rate: {{ number_format($entry->waste_rating, 2) }}%</span>
            </div>

            @if($entry->entryItems->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Used</th>
                            <th>Wasted</th>
                            <th>Waste Rate</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entry->entryItems as $entryItem)
                            <tr>
                                <td>{{ $entryItem->item->name }}</td>
                                <td>{{ $entryItem->item->category }}</td>
                                <td>{{ number_format($entryItem->used_quantity, 2) }}</td>
                                <td>{{ number_format($entryItem->wasted_quantity, 2) }}</td>
                                <td>{{ number_format($entryItem->waste_rating, 2) }}%</td>
                                <td>{{ $entryItem->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    <div class="pagination-wrapper">
        {{ $entries->appends(['sort_by' => $sortBy, 'sort_order' => $sortOrder])->links() }}
    </div>
@else
    <div class="card">
        <p class="text-center" style="padding: 40px; color: #757575;">
            No records found. <a href="{{ route('entries.create') }}">Create your first entry</a>
        </p>
    </div>
@endif

<style>
.sort-form {
    margin-bottom: 20px;
}

.sort-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.entry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #E8F5E9;
}

.entry-header h3 {
    margin: 0;
    color: #2D7A3E;
    font-size: 20px;
}

.waste-badge {
    background: #E8F5E9;
    color: #1B5E20;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.pagination-wrapper {
    padding: 20px;
    text-center;
}

@media (max-width: 768px) {
    .sort-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
