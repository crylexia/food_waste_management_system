@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Item Management</h1>
        <p class="page-subtitle">Manage the items tracked in your daily entries</p>
    </div>
    <a href="{{ route('items.create') }}" class="btn-main">+ New Item</a>
</div>

{{-- ── Filter Bar ── --}}
<div class="a-card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('items.index') }}">
        <div class="filter-row">
            <div class="form-group">
                <label class="field-label" for="category">Filter by Category</label>
                <select name="category" id="category"
                        class="field-input" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="field-label" for="search">Search</label>
                <input type="text" name="search" id="search"
                       class="field-input"
                       value="{{ request('search') }}"
                       placeholder="Search by name...">
            </div>
            <div class="form-group" style="align-self:flex-end; display:flex; gap:8px;">
                <button type="submit" class="btn-main">Search</button>
                @if(request('category') || request('search'))
                    <a href="{{ route('items.index') }}" class="btn-ghost">Clear</a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- ── Items Table ── --}}
<div class="a-card">
    @if($items->count() > 0)
        <div class="table-wrap">
            <table class="a-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price per Unit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td>
                                <span class="cat-badge">{{ $item->category }}</span>
                            </td>
                            <td>
                                @if($item->price)
                                    <span style="font-size:13px; font-weight:600; color:#111;">₱{{ number_format($item->price, 2) }}</span>
                                @else
                                    <span style="color:#bbb; font-size:12px;">—</span>
                                @endif
                            </td>
                            <td>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <a href="{{ route('items.edit', $item) }}" class="tbl-btn tbl-btn-edit">Edit</a>
                                    <form action="{{ route('items.destroy', $item) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this item?');"
                                          style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tbl-btn tbl-btn-del">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px 0 0; text-align:center;">
            {{ $items->links() }}
        </div>
    @else
        <div style="padding:60px 20px; text-align:center;">
            <div style="font-size:36px; margin-bottom:12px;">📦</div>
            <div style="font-size:15px; font-weight:600; color:#333; margin-bottom:6px;">No items found</div>
            @if(request('category') || request('search'))
                <div style="font-size:13px; color:#999; margin-bottom:20px;">No items match your current filters.</div>
                <a href="{{ route('items.index') }}" class="btn-ghost">Clear Filters</a>
            @else
                <div style="font-size:13px; color:#999; margin-bottom:20px;">Start by adding the items you want to track.</div>
                <a href="{{ route('items.create') }}" class="btn-main">+ Create First Item</a>
            @endif
        </div>
    @endif
</div>

<style>
.page-header   { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.page-subtitle { font-size:13px; color:#888; margin-top:2px; }
.a-card        { background:#fff; border:1px solid #ebebeb; border-radius:12px; padding:20px; }
.table-wrap    { overflow-x:auto; }

.filter-row { display:grid; grid-template-columns:1fr 1fr auto; gap:16px; align-items:end; }

.field-label  { display:block; font-size:11px; font-weight:600; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
.field-input  { width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; color:#111; box-sizing:border-box; transition:border-color .2s; }
.field-input:focus { outline:none; border-color:#2D7A3E; box-shadow:0 0 0 3px rgba(45,122,62,.08); }

.btn-main  { padding:10px 20px; background:#2D7A3E; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block; transition:background .2s; }
.btn-main:hover { background:#245f31; }
.btn-ghost { padding:10px 16px; background:#fff; color:#555; border:1px solid #ddd; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; display:inline-block; transition:border-color .2s; }
.btn-ghost:hover { border-color:#aaa; }

.a-table { width:100%; border-collapse:collapse; }
.a-table th, .a-table td { padding:11px 12px; text-align:left; font-size:13px; border-bottom:1px solid #f0f0f0; }
.a-table th { background:#f8fafc; color:#666; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.a-table tr:last-child td { border-bottom:none; }
.a-table tr:hover td { background:#fafafa; }

.cat-badge { display:inline-block; padding:3px 9px; border-radius:99px; font-size:11px; font-weight:600; background:#f3f4f6; color:#374151; }

.tbl-btn { display:inline-block; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:background .2s; }
.tbl-btn-edit { background:#eff6ff; color:#1565C0; border:1px solid #bfdbfe; }
.tbl-btn-edit:hover { background:#dbeafe; }
.tbl-btn-del  { background:#fff0f0; color:#C62828; border:1px solid #fecaca; }
.tbl-btn-del:hover  { background:#fecaca; }

@media (max-width: 768px) {
    .filter-row  { grid-template-columns:1fr; }
    .page-header { flex-direction:column; gap:12px; }
}
</style>

@endsection