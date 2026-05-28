@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Inventory Activity Archive</h1>
        <p class="page-subtitle">
            Review past inventory usage, waste trends, and recorded operational data
        </p>
    </div>
</div>

<div class="a-card" style="margin-bottom:18px;">
    <form method="GET" action="{{ route('records.index') }}">
        <div class="filter-grid">

            <div class="form-group">
                <label class="field-label">Order</label>
                <select name="sort_order" class="field-input" onchange="this.form.submit()">
                    <option value="desc" {{ $sortOrder == 'desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="asc" {{ $sortOrder == 'asc' ? 'selected' : '' }}>Oldest First</option>
                </select>
            </div>

        </div>
    </form>
</div>

@if($entries->count() > 0)

    @foreach($entries as $entry)

        <div class="a-card" style="margin-bottom:18px;">

            <div class="entry-header">

                <div>
                    <div class="entry-date">
                        {{ $entry->date->format('F d, Y') }}
                    </div>

                    <div class="entry-sub">
                        {{ $entry->entryItems->count() }} item(s) recorded
                    </div>
                </div>

                <div class="waste-pill">
                    Waste Rate:
                    {{ number_format($entry->waste_rating, 2) }}%
                </div>

            </div>

            @php
                $entryTotalUsedCost = 0;
                $entryTotalWastedCost = 0;
            @endphp

            <div class="table-wrap">

                <table class="modern-table">

                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Used</th>
                            <th>Wasted</th>
                            <th>Used Cost</th>
                            <th>Wasted Cost</th>
                            <th>Waste Rate</th>
                            <th>Notes</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($entry->entryItems as $entryItem)

                            @php
                                $price = $entryItem->item->price ?? 0;

                                $usedCost =
                                    $entryItem->used_quantity * $price;

                                $wastedCost =
                                    $entryItem->wasted_quantity * $price;

                                $entryTotalUsedCost += $usedCost;
                                $entryTotalWastedCost += $wastedCost;
                            @endphp

                            <tr>

                                <td class="fw-600">
                                    {{ $entryItem->item->name }}
                                </td>

                                <td>
                                    <span class="cat-badge">
                                        {{ $entryItem->item->category }}
                                    </span>
                                </td>

                                <td>₱{{ number_format($price, 2) }}</td>

                                <td>
                                    {{ number_format($entryItem->used_quantity, 2) }}
                                </td>

                                <td>
                                    {{ number_format($entryItem->wasted_quantity, 2) }}
                                </td>

                                <td>
                                    ₱{{ number_format($usedCost, 2) }}
                                </td>

                                <td class="text-danger">
                                    ₱{{ number_format($wastedCost, 2) }}
                                </td>

                                <td>
                                    {{ number_format($entryItem->waste_rating, 2) }}%
                                </td>

                                <td>
                                    {{ $entryItem->notes ?? '-' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="totals-box">

                <div class="totals-grid">

                    <div class="total-card">
                        <div class="total-label">Used Quantity</div>
                        <div class="total-value">
                            {{ number_format($entry->entryItems->sum('used_quantity'), 2) }}
                        </div>
                    </div>

                    <div class="total-card">
                        <div class="total-label">Wasted Quantity</div>
                        <div class="total-value text-danger">
                            {{ number_format($entry->entryItems->sum('wasted_quantity'), 2) }}
                        </div>
                    </div>

                    <div class="total-card">
                        <div class="total-label">Used Cost</div>
                        <div class="total-value">
                            ₱{{ number_format($entryTotalUsedCost, 2) }}
                        </div>
                    </div>

                    <div class="total-card">
                        <div class="total-label">Wasted Cost</div>
                        <div class="total-value text-danger">
                            ₱{{ number_format($entryTotalWastedCost, 2) }}
                        </div>
                    </div>

                </div>

            </div>

        </div>

    @endforeach

    <div class="pagination-wrapper">
        {{ $entries->appends([
            'sort_order' => $sortOrder
        ])->links() }}
    </div>

@else

    <div class="a-card empty-state">

        <div class="empty-icon">📂</div>

        <div class="empty-title">
            No historical records found
        </div>

        <div class="empty-text">
            Start adding daily inventory entries to generate analytics and reports.
        </div>

        <a href="{{ route('entries.create') }}" class="btn-main">
            Create First Entry
        </a>

    </div>

@endif

<style>

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:20px;
}

.page-subtitle{
    font-size:13px;
    color:#888;
    margin-top:3px;
}

.a-card{
    background:#fff;
    border:1px solid #ebebeb;
    border-radius:14px;
    padding:24px;
}

.filter-grid{
    display:grid;
    grid-template-columns:1fr;
    gap:16px;
}

.field-label{
    display:block;
    font-size:11px;
    font-weight:700;
    color:#666;
    margin-bottom:7px;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.field-input{
    width:100%;
    border:1px solid #ddd;
    border-radius:8px;
    font-size: 12px;
    padding: 10px 14px;
    transition:.2s;
}

.field-input:focus{
    outline:none;
    border-color:#2D7A3E;
    box-shadow:0 0 0 3px rgba(45,122,62,.08);
}

.entry-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:18px;
    padding-bottom:16px;
    border-bottom:1px solid #f0f0f0;
}

.entry-date{
    font-size:20px;
    font-weight:700;
    color:#111;
}

.entry-sub{
    font-size:12px;
    color:#999;
    margin-top:3px;
}

.waste-pill{
    background:#E8F5E9;
    color:#1B5E20;
    padding:8px 16px;
    border-radius:999px;
    font-size:13px;
    font-weight:700;
}

.table-wrap{
    overflow-x:auto;
}

.modern-table{
    width:100%;
    border-collapse:collapse;
}

.modern-table thead th{
    background:#f8faf8;
    color:#666;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.05em;
    padding:13px;
    text-align:left;
    border-bottom:1px solid #eee;
}

.modern-table tbody td{
    padding:14px 13px;
    border-bottom:1px solid #f4f4f4;
    font-size:14px;
    color:#444;
}

.modern-table tbody tr:hover{
    background:#fafafa;
}

.fw-600{
    font-weight:600;
    color:#111;
}

.cat-badge{
    background:#f1f5f9;
    color:#475569;
    padding:5px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:600;
}

.text-danger{
    color:#C62828;
}

.totals-box{
    margin-top:18px;
}

.totals-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
}

.total-card{
    background:#f9fafb;
    border:1px solid #eee;
    border-radius:12px;
    padding:16px;
}

.total-label{
    font-size:11px;
    color:#888;
    text-transform:uppercase;
    margin-bottom:6px;
    letter-spacing:.05em;
}

.total-value{
    font-size:18px;
    font-weight:700;
    color:#111;
}

.empty-state{
    text-align:center;
    padding:60px 20px;
}

.empty-icon{
    font-size:42px;
    margin-bottom:10px;
}

.empty-title{
    font-size:18px;
    font-weight:700;
    color:#111;
}

.empty-text{
    font-size:14px;
    color:#888;
    margin-top:6px;
    margin-bottom:20px;
}

.btn-main{
    display:inline-block;
    padding:11px 20px;
    background:#2D7A3E;
    color:#fff;
    border-radius:8px;
    text-decoration:none;
    font-weight:600;
}

.btn-main:hover{
    background:#245f31;
}

.pagination-wrapper{
    margin-top:20px;
    text-align:center;
}

@media(max-width:768px){

    .entry-header{
        flex-direction:column;
        align-items:flex-start;
        gap:12px;
    }

    .totals-grid{
        grid-template-columns:1fr 1fr;
    }

}

</style>

@endsection