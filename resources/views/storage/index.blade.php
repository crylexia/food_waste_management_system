@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Storage</h1>
        <p class="page-subtitle">Track inventory stock levels and expiration dates</p>
    </div>
</div>

{{-- ── Summary KPIs ── --}}
<div class="kpi-row" style="margin-bottom:20px;">
    <div class="a-card kpi-card">
        <div class="kpi-label">Active Items</div>
        <div class="kpi-value">{{ $totalActive }}</div>
        <div class="kpi-hint">in storage</div>
    </div>
    <div class="a-card kpi-card">
        <div class="kpi-label">Expired</div>
        <div class="kpi-value {{ $totalExpired > 0 ? 'c-danger' : '' }}">{{ $totalExpired }}</div>
        <div class="kpi-hint">past expiry date</div>
    </div>
    <div class="a-card kpi-card">
        <div class="kpi-label">Critical</div>
        <div class="kpi-value {{ $totalCritical > 0 ? 'c-danger' : '' }}">{{ $totalCritical }}</div>
        <div class="kpi-hint">expires within 2 days</div>
    </div>
    <div class="a-card kpi-card">
        <div class="kpi-label">Expiring Soon</div>
        <div class="kpi-value {{ $totalSoon > 0 ? 'c-warning' : '' }}">{{ $totalSoon }}</div>
        <div class="kpi-hint">expires within 7 days</div>
    </div>
</div>

{{-- ── Add to Storage Form ── --}}
<div class="a-card" style="margin-bottom:20px;">
    <h3 class="section-title">➕ Add Item to Storage</h3>
    <p class="section-desc">Log a new batch or restock of an inventory item</p>

    <form method="POST" action="{{ route('storage.store') }}">
        @csrf

        <div class="storage-form-grid">
            <div class="form-group">
                <label class="field-label" for="item_id">Item *</label>
                <select name="item_id" id="item_id"
                        class="field-input @error('item_id') field-error @enderror" required>
                    <option value="">-- Select an item --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }} ({{ $item->category }})
                        </option>
                    @endforeach
                </select>
                @error('item_id')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="quantity">Quantity *</label>
                <input type="number" id="quantity" name="quantity"
                       class="field-input @error('quantity') field-error @enderror"
                       value="{{ old('quantity') }}" step="0.01" min="0.01" required
                       placeholder="0.00">
                @error('quantity')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label class="field-label" for="expiration_date">Expiration Date</label>
                <input type="date" id="expiration_date" name="expiration_date"
                       class="field-input @error('expiration_date') field-error @enderror"
                       value="{{ old('expiration_date') }}">
                @error('expiration_date')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="received_date">Received Date</label>
                <input type="date" id="received_date" name="received_date"
                       class="field-input @error('received_date') field-error @enderror"
                       value="{{ old('received_date', date('Y-m-d')) }}">
                @error('received_date')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="batch_number">Batch / Lot #</label>
                <input type="text" id="batch_number" name="batch_number"
                       class="field-input @error('batch_number') field-error @enderror"
                       value="{{ old('batch_number') }}" placeholder="Optional">
                @error('batch_number')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="field-label" for="notes">Notes</label>
                <input type="text" id="notes" name="notes"
                       class="field-input @error('notes') field-error @enderror"
                       value="{{ old('notes') }}" placeholder="Optional">
                @error('notes')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <button type="submit" class="btn-main">Add to Storage</button>
    </form>
</div>

{{-- ── Storage Table ── --}}
<div class="a-card">
    <h3 class="section-title" style="margin-bottom:3px;">📦 Storage Inventory</h3>
    <p class="section-desc">{{ $storageItems->total() }} total record{{ $storageItems->total() !== 1 ? 's' : '' }}</p>

    @if($storageItems->count() > 0)
    <div class="table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Received</th>
                    <th>Expiration</th>
                    <th>Status</th>
                    <th>Batch / Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($storageItems as $si)
                <tr>
                    <td><strong>{{ $si->item->name }}</strong>
                        <div style="font-size:11px; color:#aaa;">{{ $si->item->category }}</div>
                    </td>
                    <td>
                        {{ number_format($si->quantity, 2) }}
                        {{ $si->item->unit }}
                    </td>
                    <td style="font-size:12px; color:#888;">
                        {{ $si->received_date ? $si->received_date->format('M j, Y') : '—' }}
                    </td>
                    <td>
                        @if($si->status !== 'active')
                            <span style="color:#bbb; font-size:12px;">—</span>
                        @elseif($si->expiration_date)
                            <div style="display:flex; flex-direction:column; gap:3px;">
                                <span style="font-size:12px; font-weight:600; color:#111;">
                                    {{ $si->expiration_date->format('M j, Y') }}
                                </span>

                                @php $status = $si->expiry_status; @endphp

                                @if($status === 'expired')
                                    <span class="expiry-badge expiry-expired">Expired</span>

                                @elseif($status === 'critical')
                                    <span class="expiry-badge expiry-critical">
                                        {{ $si->days_until_expiry }}d left
                                    </span>

                                @elseif($status === 'soon')
                                    <span class="expiry-badge expiry-soon">
                                        {{ $si->days_until_expiry }}d left
                                    </span>

                                @else
                                    <span class="expiry-badge expiry-ok">
                                        {{ $si->days_until_expiry }}d left
                                    </span>
                                @endif
                            </div>
                        @else
                            <span style="color:#bbb; font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge status-{{ $si->status }}">
                            {{ ucfirst($si->status) }}
                        </span>
                    </td>
                    <td style="font-size:12px; color:#666;">
                        @if($si->batch_number)
                            <div><span style="color:#aaa;">Batch:</span> {{ $si->batch_number }}</div>
                        @endif
                        {{ $si->notes ?? '' }}
                        @if(!$si->batch_number && !$si->notes) — @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:6px; align-items:center;">
                            {{-- Quick status update --}}
                            @if($si->status === 'active')
                            <form action="{{ route('storage.deplete', $si) }}" method="POST" style="margin:0;">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="tbl-btn tbl-btn-warn"
                                        onclick="return confirm('Mark as depleted?')">
                                    Deplete
                                </button>
                            </form>

                            @elseif($si->status === 'depleted')
                            <form action="{{ route('storage.restore', $si) }}" method="POST" style="margin:0;">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="tbl-btn tbl-btn-ok"
                                        onclick="return confirm('Restore this item?')">
                                    Restore
                                </button>
                            </form>
                            @endif

                            {{-- Delete --}}
                            <form action="{{ route('storage.destroy', $si) }}" method="POST"
                                onsubmit="return confirm('Delete this item?')">

                                @csrf
                                @method('DELETE')

                                <button type="submit" class="tbl-btn tbl-btn-danger">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">
        {{ $storageItems->links() }}
    </div>

    @else
    <div style="padding:40px 20px; text-align:center;">
        <div style="font-size:32px; margin-bottom:10px;">🗄️</div>
        <div style="font-size:14px; font-weight:600; color:#333; margin-bottom:4px;">No storage items yet</div>
        <div style="font-size:12px; color:#999;">Use the form above to start tracking your inventory stock.</div>
    </div>
    @endif
</div>

<style>
.page-header   { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.page-subtitle { font-size:13px; color:#888; margin-top:2px; }
.a-card        { background:#fff; border:1px solid #ebebeb; border-radius:12px; padding:20px; }
.section-title { font-size:15px; font-weight:700; color:#111; margin:0 0 3px; }
.section-desc  { font-size:12px; color:#999; margin:0 0 14px; }
.table-wrap    { overflow-x:auto; }

.kpi-row  { display:grid; grid-template-columns:repeat(auto-fit, minmax(140px,1fr)); gap:14px; }
.kpi-card { display:flex; flex-direction:column; }
.kpi-label { font-size:11px; color:#aaa; text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px; }
.kpi-value { font-size:24px; font-weight:700; color:#111; margin-bottom:4px; }
.kpi-hint  { font-size:11px; color:#bbb; margin-top:auto; }
.c-success { color:#2D7A3E; } .c-warning { color:#E65100; } .c-danger { color:#C62828; }

.storage-form-grid {
    display:grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr 1.5fr;
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

.expiry-badge    { display:inline-block; padding:2px 8px; border-radius:99px; font-size:10px; font-weight:700; }
.expiry-expired  { background:#fecaca; color:#b91c1c; }
.expiry-critical { background:#fed7aa; color:#9a3412; }
.expiry-soon     { background:#fde68a; color:#92400e; }
.expiry-ok       { background:#dcfce7; color:#166534; }

.status-badge    { display:inline-block; padding:3px 9px; border-radius:99px; font-size:11px; font-weight:600; }
.status-active   { background:#dcfce7; color:#166534; }
.status-depleted { background:#f3f4f6; color:#555; }
.status-discarded{ background:#fecaca; color:#b91c1c; }

.tbl-btn      { display:inline-block; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:background .2s; }
.tbl-btn-del  { background:#fff0f0; color:#C62828; border:1px solid #fecaca; }
.tbl-btn-del:hover  { background:#fecaca; }
.tbl-btn-warn { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
.tbl-btn-warn:hover { background:#fde68a; }
.tbl-btn-ok   { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.tbl-btn-ok:hover   { background:#bbf7d0; }

@media (max-width: 1100px) {
    .storage-form-grid { grid-template-columns:1fr 1fr 1fr; }
}
@media (max-width: 640px) {
    .storage-form-grid { grid-template-columns:1fr; }
    .kpi-row { grid-template-columns:1fr 1fr; }
    .page-header { flex-direction:column; gap:10px; }
}
</style>

@endsection