@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Daily Entry</h1>
        <p class="page-subtitle">{{ $entry->date->format('l, F j, Y') }}</p>
    </div>
    <a href="{{ route('entries.index') }}" class="back-link">← Back to Entries</a>
</div>

{{-- ── Summary KPIs ── --}}
@if($entry->entryItems->count() > 0)
<div class="kpi-row" style="margin-bottom:20px;">
    <div class="a-card kpi-card">
        <div class="kpi-label">Items Logged</div>
        <div class="kpi-value">{{ $entry->entryItems->count() }}</div>
        <div class="kpi-hint">items in this entry</div>
    </div>
    <div class="a-card kpi-card">
        <div class="kpi-label">Total Used</div>
        <div class="kpi-value c-success">
            {{ number_format($entry->entryItems->sum('used_quantity'), 2) }}
        </div>
        <div class="kpi-hint">total quantity consumed</div>
    </div>
    <div class="a-card kpi-card">
        <div class="kpi-label">Total Wasted</div>
        <div class="kpi-value c-danger">{{ number_format($entry->entryItems->sum('wasted_quantity'), 2) }}</div>
        <div class="kpi-hint">units wasted</div>
    </div>
    <div class="a-card kpi-card">
        <div class="kpi-label">Waste Rate</div>
        <div class="kpi-value {{ $entry->waste_rating >= 20 ? 'c-danger' : ($entry->waste_rating >= 10 ? 'c-warning' : 'c-success') }}">
            {{ number_format($entry->waste_rating, 1) }}%
        </div>
        <div class="kpi-track">
            <div class="kpi-fill" style="width:{{ min($entry->waste_rating, 100) }}%;
                background:{{ $entry->waste_rating >= 20 ? '#C62828' : ($entry->waste_rating >= 10 ? '#E65100' : '#2D7A3E') }};"></div>
        </div>
        <div class="kpi-hint">
            @if($entry->waste_rating >= 20) High — review items
            @elseif($entry->waste_rating >= 10) Moderate
            @else Good
            @endif
        </div>
    </div>
</div>
@endif

{{-- ── Add Item Form ── --}}
<div class="a-card" style="margin-bottom:20px;">
    <h3 class="section-title">➕ Add Item to Entry</h3>
    <p class="section-desc">Log used and wasted quantities for an inventory item</p>

    <form method="POST" action="{{ route('entries.items.store', $entry) }}">
        @csrf

        <div class="entry-form-grid">
            <div class="form-group">
                <label class="field-label" for="item_id">Select Item *</label>
                <select name="item_id" id="item_id"
                        class="field-input @error('item_id') field-error @enderror" required>
                    <option value="">-- Select an item --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}"
                            {{ old('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} ({{ $item->category }}) - {{ $item->unit }}
                        </option>
                    @endforeach
                </select>
                @error('item_id')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="used_quantity">Used Quantity *</label>
                <input type="number" id="used_quantity" name="used_quantity"
                       class="field-input @error('used_quantity') field-error @enderror"
                       value="{{ old('used_quantity', 0) }}" step="0.01" min="0" required>
                @error('used_quantity')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="wasted_quantity">Wasted Quantity *</label>
                <input type="number" id="wasted_quantity" name="wasted_quantity"
                       class="field-input @error('wasted_quantity') field-error @enderror"
                       value="{{ old('wasted_quantity', 0) }}" step="0.01" min="0" required>
                @error('wasted_quantity')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="waste_reason">Waste Reason</label>
                <select name="waste_reason" id="waste_reason"
                        class="field-input @error('waste_reason') field-error @enderror">
                    <option value="">-- None --</option>
                    @foreach($wasteReasons as $value => $label)
                        <option value="{{ $value }}"
                            {{ old('waste_reason') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('waste_reason')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="notes">Notes (optional)</label>
                <input type="text" id="notes" name="notes"
                       class="field-input @error('notes') field-error @enderror"
                       value="{{ old('notes') }}" maxlength="500"
                       placeholder="Any extra detail...">
                @error('notes')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-main">Add Item</button>
    </form>
</div>

{{-- ── Items Table ── --}}
<div class="a-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
        <div>
            <h3 class="section-title" style="margin:0;">📋 Logged Items</h3>
            <p class="section-desc" style="margin:3px 0 0;">
                {{ $entry->entryItems->count() }} item{{ $entry->entryItems->count() !== 1 ? 's' : '' }} in this entry
            </p>
        </div>
    </div>

    @if($entry->entryItems->count() > 0)
        <div class="table-wrap">
            <table class="a-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Used</th>
                        <th>Wasted</th>
                        <th>Waste Rate</th>
                        <th>Waste Reason</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entry->entryItems as $entryItem)
                        <tr>
                            <td><strong>{{ $entryItem->item->name }}</strong></td>
                            <td>
                                {{ number_format($entryItem->used_quantity, 2) }}
                                {{ $entryItem->item->unit }}
                            </td>

                            <td>
                                {{ number_format($entryItem->wasted_quantity, 2) }}
                                {{ $entryItem->item->unit }}
                            </td>
                            <td>
                                <span class="w-badge {{ $entryItem->waste_rating >= 50 ? 'wb-high' : ($entryItem->waste_rating >= 25 ? 'wb-med' : 'wb-low') }}">
                                    {{ number_format($entryItem->waste_rating, 2) }}%
                                </span>
                            </td>
                            <td>
                                @if($entryItem->waste_reason)
                                    <span class="reason-badge reason-{{ $entryItem->waste_reason }}">
                                        {{ $wasteReasons[$entryItem->waste_reason] ?? ucfirst($entryItem->waste_reason) }}
                                    </span>
                                @else
                                    <span style="color:#bbb; font-size:12px;">—</span>
                                @endif
                            </td>
                            <td style="font-size:12px; color:#666;">{{ $entryItem->notes ?? '—' }}</td>
                            <td>
                                <form action="{{ route('entry-items.destroy', $entryItem) }}" method="POST"
                                      onsubmit="return confirm('Remove this item?');"
                                      style="margin:0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tbl-btn tbl-btn-del">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px; padding:14px 16px; background:#f8fafc; border-radius:8px; font-size:13px; color:#555; display:flex; gap:24px;">
            <span>
                <strong style="color:#111;">Used:</strong>
                {{ number_format($entry->entryItems->sum('used_quantity'), 2) }}
            </span>

            <span>
                <strong style="color:#111;">Wasted:</strong>
                {{ number_format($entry->entryItems->sum('wasted_quantity'), 2) }}
            </span>
            <span><strong style="color:#111;">Waste Rate:</strong> {{ number_format($entry->waste_rating, 2) }}%</span>
        </div>
    @else
        <div style="padding:40px 20px; text-align:center;">
            <div style="font-size:32px; margin-bottom:10px;">📦</div>
            <div style="font-size:14px; font-weight:600; color:#333; margin-bottom:4px;">No items added yet</div>
            <div style="font-size:12px; color:#999;">Use the form above to add items to this entry.</div>
        </div>
    @endif
</div>

<style>
.page-header   { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.page-subtitle { font-size:13px; color:#888; margin-top:2px; }
.back-link     { font-size:13px; color:#2D7A3E; text-decoration:none; margin-top:4px; }
.back-link:hover { text-decoration:underline; }
.a-card        { background:#fff; border:1px solid #ebebeb; border-radius:12px; padding:20px; }
.section-title { font-size:15px; font-weight:700; color:#111; margin:0 0 3px; }
.section-desc  { font-size:12px; color:#999; margin:0 0 14px; }
.table-wrap    { overflow-x:auto; }

.kpi-row  { display:grid; grid-template-columns:repeat(auto-fit, minmax(140px,1fr)); gap:14px; }
.kpi-card { display:flex; flex-direction:column; }
.kpi-label { font-size:11px; color:#aaa; text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px; }
.kpi-value { font-size:24px; font-weight:700; color:#111; margin-bottom:4px; }
.kpi-track { height:5px; background:#f0f0f0; border-radius:3px; margin-bottom:4px; overflow:hidden; }
.kpi-fill  { height:100%; border-radius:3px; }
.kpi-hint  { font-size:11px; color:#bbb; margin-top:auto; }
.c-success { color:#2D7A3E; } .c-warning { color:#E65100; } .c-danger { color:#C62828; }

.entry-form-grid {
    display:grid;
    grid-template-columns: 2fr 1fr 1fr 1.5fr 1.5fr;
    gap:14px;
    margin-bottom:20px;
}
.field-label { display:block; font-size:11px; font-weight:600; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
.field-input { width:100%; padding:9px 12px; border:1px solid #ddd; border-radius:8px; font-size:13px; color:#111; box-sizing:border-box; transition:border-color .2s; }
.field-input:focus { outline:none; border-color:#2D7A3E; box-shadow:0 0 0 3px rgba(45,122,62,.08); }
.field-input.field-error { border-color:#C62828; }
.field-msg-error { font-size:11px; color:#C62828; margin-top:4px; }

.btn-main { padding:10px 22px; background:#2D7A3E; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; }
.btn-main:hover { background:#245f31; }

.a-table { width:100%; border-collapse:collapse; }
.a-table th, .a-table td { padding:11px 10px; text-align:left; font-size:13px; border-bottom:1px solid #f0f0f0; }
.a-table th { background:#f8fafc; color:#666; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.a-table tr:last-child td { border-bottom:none; }
.a-table tr:hover td { background:#fafafa; }

.w-badge { display:inline-block; padding:3px 9px; border-radius:99px; font-size:11px; font-weight:700; }
.wb-high { background:#fecaca; color:#b91c1c; }
.wb-med  { background:#fde68a; color:#92400e; }
.wb-low  { background:#dcfce7; color:#166534; }

.tbl-btn { display:inline-block; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:background .2s; }
.tbl-btn-del  { background:#fff0f0; color:#C62828; border:1px solid #fecaca; }
.tbl-btn-del:hover { background:#fecaca; }

.reason-badge { display:inline-block; padding:3px 9px; border-radius:99px; font-size:11px; font-weight:600; }
.reason-expired      { background:#fecaca; color:#b91c1c; }
.reason-overproduced { background:#fde68a; color:#92400e; }
.reason-spoiled      { background:#fde68a; color:#92400e; }
.reason-leftover     { background:#e0e7ff; color:#3730a3; }
.reason-other        { background:#f3f4f6; color:#555; }

@media (max-width: 1100px) {
    .entry-form-grid { grid-template-columns:1fr 1fr 1fr; }
}
@media (max-width: 640px) {
    .entry-form-grid { grid-template-columns:1fr; }
    .kpi-row { grid-template-columns:1fr 1fr; }
    .page-header { flex-direction:column; gap:10px; }
}
</style>

@endsection