@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Daily Entries</h1>
        <p class="page-subtitle">Track and manage your daily inventory logs</p>
    </div>
    <a href="{{ route('entries.create') }}" class="btn-main">+ New Entry</a>
</div>

<div class="a-card">
    @if($entries->count() > 0)
        <div class="table-wrap">
            <table class="a-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Items Logged</th>
                        <th>Waste Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($entries as $entry)
                        <tr>
                            <td><strong>{{ $entry->date->format('M j, Y') }}</strong></td>
                            <td>
                                <span style="font-size:13px; color:#555;">
                                    {{ $entry->entryItems->count() }} item{{ $entry->entryItems->count() !== 1 ? 's' : '' }}
                                </span>
                            </td>
                            <td>
                                <span class="w-badge {{ $entry->waste_rating >= 20 ? 'wb-high' : ($entry->waste_rating >= 10 ? 'wb-med' : 'wb-low') }}">
                                    {{ number_format($entry->waste_rating, 1) }}%
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <a href="{{ route('entries.show', $entry) }}" class="tbl-btn tbl-btn-view">View</a>
                                    <form action="{{ route('entries.destroy', $entry) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this entry?');"
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
            {{ $entries->links() }}
        </div>
    @else
        <div style="padding:60px 20px; text-align:center;">
            <div style="font-size:36px; margin-bottom:12px;">📋</div>
            <div style="font-size:15px; font-weight:600; color:#333; margin-bottom:6px;">No entries yet</div>
            <div style="font-size:13px; color:#999; margin-bottom:20px;">Start tracking your inventory by creating your first daily entry.</div>
            <a href="{{ route('entries.create') }}" class="btn-main">+ Create First Entry</a>
        </div>
    @endif
</div>

<style>
.page-header   { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.page-subtitle { font-size:13px; color:#888; margin-top:2px; }
.a-card        { background:#fff; border:1px solid #ebebeb; border-radius:12px; padding:20px; }
.table-wrap    { overflow-x:auto; }

.a-table { width:100%; border-collapse:collapse; }
.a-table th, .a-table td { padding:11px 12px; text-align:left; font-size:13px; border-bottom:1px solid #f0f0f0; }
.a-table th { background:#f8fafc; color:#666; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
.a-table tr:last-child td { border-bottom:none; }
.a-table tr:hover td { background:#fafafa; }

.w-badge { display:inline-block; padding:3px 9px; border-radius:99px; font-size:11px; font-weight:700; }
.wb-high { background:#fecaca; color:#b91c1c; }
.wb-med  { background:#fde68a; color:#92400e; }
.wb-low  { background:#dcfce7; color:#166534; }

.btn-main  { padding:10px 20px; background:#2D7A3E; color:#fff; border:none; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; transition:background .2s; }
.btn-main:hover { background:#245f31; }

.tbl-btn { display:inline-block; padding:5px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:none; text-decoration:none; transition:background .2s; }
.tbl-btn-view { background:#f0fdf4; color:#2D7A3E; border:1px solid #bbf7d0; }
.tbl-btn-view:hover { background:#dcfce7; }
.tbl-btn-del  { background:#fff0f0; color:#C62828; border:1px solid #fecaca; }
.tbl-btn-del:hover  { background:#fecaca; }

@media (max-width: 768px) {
    .page-header { flex-direction:column; gap:12px; }
}
</style>

@endsection