@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Item Management</h1>
    <a href="{{ route('items.create') }}" class="btn btn-primary">+ New Item</a>
</div>

<div class="card">
    <form method="GET" action="{{ route('items.index') }}" class="filter-form">
        <div class="filter-row">
            <div class="form-group">
                <label for="category">Filter by Category</label>
                <select name="category" id="category" class="form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" name="search" id="search" class="form-control" 
                       value="{{ request('search') }}" placeholder="Search by name...">
            </div>
            <div class="form-group" style="align-self: flex-end;">
                <button type="submit" class="btn btn-secondary">Search</button>
                @if(request('category') || request('search'))
                    <a href="{{ route('items.index') }}" class="btn btn-secondary">Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>

<div class="card">
    @if($items->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category }}</td>
                        <td>₱{{ number_format($item->price, 2) }}</td>
                        <td>
                            <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('items.destroy', $item) }}" method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper">
            {{ $items->links() }}
        </div>
    @else
        <p class="text-center" style="padding: 40px; color: #757575;">
            No items found. <a href="{{ route('items.create') }}">Create your first item</a>
        </p>
    @endif
</div>

<style>
.filter-form {
    margin-bottom: 20px;
}

.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 15px;
    align-items: end;
}

.pagination-wrapper {
    padding: 20px;
    text-align: center;
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection
