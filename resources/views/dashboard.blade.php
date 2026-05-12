@extends('layouts.app')

@section('content')

<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <span class="page-date">{{ now()->format('l, F j, Y') }}</span>
</div>

{{-- ── ROW 1: Key Numbers ─────────────────────────────────────── --}}
<div class="kpi-row">

    <div class="kpi-card">
        <div class="kpi-label">Wasted Today</div>
        @if($todayEntry)
            <div class="kpi-value {{ $todayWastedValue > 0 ? 'danger' : 'success' }}">
                ₱{{ number_format($todayWastedValue, 2) }}
            </div>
            <div class="kpi-sub">{{ number_format($todayWastedQty, 2) }} units</div>
        @else
            <div class="kpi-value neutral">—</div>
            <div class="kpi-sub">No entry logged today</div>
        @endif
    </div>

    <div class="kpi-card">
        <div class="kpi-label">Wasted This Week</div>
        <div class="kpi-value {{ $thisWeekWaste > 0 ? 'danger' : 'success' }}">
            ₱{{ number_format($thisWeekWaste, 2) }}
        </div>
        <div class="kpi-sub">
            @if($wasteTrendDir === 'up')
                <span class="trend up">↑ {{ number_format(abs($wasteTrendPct), 1) }}% vs last week</span>
            @elseif($wasteTrendDir === 'down')
                <span class="trend down">↓ {{ number_format(abs($wasteTrendPct), 1) }}% vs last week</span>
            @elseif($wasteTrendDir === 'flat')
                <span class="trend flat">→ No change vs last week</span>
            @else
                <span style="color:#aaa;">No previous week data</span>
            @endif
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-label">Overall Waste Rate</div>
        <div class="kpi-value {{ $overallWasteRate >= 20 ? 'danger' : ($overallWasteRate >= 10 ? 'warning' : 'success') }}">
            {{ number_format($overallWasteRate, 1) }}%
        </div>
        <div class="kpi-sub">
            @if($overallWasteRate >= 20)
                High — action needed
            @elseif($overallWasteRate >= 10)
                Moderate — monitor closely
            @else
                Good — keep it up
            @endif
        </div>
    </div>

</div>

{{-- ── ROW 2: Critical Alerts ──────────────────────────────────── --}}
@if($criticalInsights->isNotEmpty())
<div class="section-block">
    <div class="section-header">
        <span>🚨 Alerts</span>
        <a href="{{ route('analytics.index') }}" class="see-more">See full analysis →</a>
    </div>
    <div class="alert-list">
        @foreach($criticalInsights as $alert)
            <div class="alert-item alert-{{ $alert['type'] }}">
                <span class="alert-icon">{{ $alert['icon'] }}</span>
                <div class="alert-body">
                    <div class="alert-title">{{ $alert['title'] }}</div>
                    <div class="alert-msg">{{ $alert['message'] }}</div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── ROW 3: Top 3 Wasted + Quick Actions ────────────────────── --}}
<div class="two-col">

    <div class="section-block">
        <div class="section-header">
            <span>🗑️ Top Wasted Items</span>
            <a href="{{ route('analytics.index') }}" class="see-more">Full breakdown →</a>
        </div>
        @if($topWasted->isNotEmpty())
            @php $maxWasted = $topWasted->max('wasted_value'); @endphp
            <ul class="waste-list">
                @foreach($topWasted as $i => $item)
                    @php $barPct = $maxWasted > 0 ? ($item->wasted_value / $maxWasted) * 100 : 0; @endphp
                    <li class="waste-item">
                        <div class="waste-top">
                            <div class="waste-info">
                                <span class="waste-rank">{{ $i + 1 }}</span>
                                <span class="waste-name">{{ $item->name }}</span>
                            </div>
                            <div class="waste-amounts">
                                <span class="waste-value">₱{{ number_format($item->wasted_value, 2) }}</span>
                                <span class="waste-rate">{{ number_format($item->waste_rating, 1) }}% rate</span>
                            </div>
                        </div>
                        <div class="waste-bar-track">
                            <div class="waste-bar-fill" style="width: {{ $barPct }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="empty-state">No waste data recorded yet.</p>
        @endif
    </div>

    <div class="section-block">
        <div class="section-header"><span>⚡ Quick Actions</span></div>
        <div class="action-list">
            <a href="{{ route('entries.create') }}" class="action-card primary">
                <span class="action-icon">📋</span>
                <div>
                    <div class="action-label">Log Today's Entry</div>
                    <div class="action-sub">
                        {{ $todayEntry ? 'Entry logged — update it' : 'No entry yet today' }}
                    </div>
                </div>
            </a>
            <a href="{{ route('analytics.index') }}" class="action-card">
                <span class="action-icon">📊</span>
                <div>
                    <div class="action-label">View Full Analytics</div>
                    <div class="action-sub">Trends, breakdowns, comparisons</div>
                </div>
            </a>
            <a href="{{ route('items.index') }}" class="action-card">
                <span class="action-icon">📦</span>
                <div>
                    <div class="action-label">Manage Items</div>
                    <div class="action-sub">Add or edit tracked items</div>
                </div>
            </a>
        </div>
    </div>

</div>

{{-- ── ROW 4: Recent Entries ───────────────────────────────────── --}}
<div class="section-block">
    <div class="section-header">
        <span>🕘 Recent Entries</span>
        <a href="{{ route('entries.index') }}" class="see-more">View all →</a>
    </div>
    @if($recentEntries->count() > 0)
        <table class="entry-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Items Logged</th>
                    <th>Waste Rate</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentEntries as $entry)
                    <tr>
                        <td>{{ $entry->date->format('M j, Y') }}</td>
                        <td>{{ $entry->entryItems->count() }} items</td>
                        <td>
                            <span class="rate-badge {{ $entry->waste_rating >= 20 ? 'danger' : ($entry->waste_rating >= 10 ? 'warning' : 'good') }}">
                                {{ number_format($entry->waste_rating, 1) }}%
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('entries.show', $entry) }}" class="link-btn">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="empty-state" style="padding: 20px;">
            No entries yet. <a href="{{ route('entries.create') }}">Log your first entry</a>
        </p>
    @endif
</div>

<style>
/* ── Page header ── */
.page-header { display: flex; align-items: baseline; gap: 12px; margin-bottom: 20px; }
.page-date   { font-size: 13px; color: #999; }

/* ── KPI Row ── */
.kpi-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
@media (max-width: 640px) { .kpi-row { grid-template-columns: 1fr; } }

.kpi-card {
    background: #fff;
    border: 1px solid #E8E8E8;
    border-radius: 8px;
    padding: 18px 20px;
}
.kpi-label { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #999; margin-bottom: 6px; }
.kpi-value { font-size: 28px; font-weight: 700; line-height: 1.2; margin-bottom: 4px; }
.kpi-value.success { color: #2D7A3E; }
.kpi-value.danger  { color: #C62828; }
.kpi-value.warning { color: #E65100; }
.kpi-value.neutral { color: #BDBDBD; }
.kpi-sub   { font-size: 12px; color: #888; }

/* ── Trend ── */
.trend { font-weight: 600; }
.trend.up   { color: #C62828; } /* up = more waste = bad */
.trend.down { color: #2D7A3E; }
.trend.flat { color: #888; }

/* ── Section blocks ── */
.section-block {
    background: #fff;
    border: 1px solid #E8E8E8;
    border-radius: 8px;
    padding: 18px 20px;
    margin-bottom: 20px;
}
.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    margin-bottom: 14px;
}
.see-more {
    font-size: 12px;
    font-weight: 400;
    color: #2D7A3E;
    text-decoration: none;
}
.see-more:hover { text-decoration: underline; }

/* ── Two-col ── */
.two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 700px) { .two-col { grid-template-columns: 1fr; } }

/* ── Alerts ── */
.alert-list { display: flex; flex-direction: column; gap: 10px; }
.alert-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 14px;
    border-radius: 6px;
    border-left: 4px solid transparent;
}
.alert-icon { font-size: 18px; flex-shrink: 0; line-height: 1.5; }
.alert-title { font-size: 13px; font-weight: 700; margin-bottom: 2px; }
.alert-msg   { font-size: 13px; color: #555; line-height: 1.5; }
.alert-warning { background: #FFF8E1; border-color: #F9A825; }
.alert-warning .alert-title { color: #E65100; }
.alert-danger  { background: #FFF0F0; border-color: #C62828; }
.alert-danger  .alert-title { color: #C62828; }

/* ── Waste list with bar ── */
.waste-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 14px; }
.waste-item { display: flex; flex-direction: column; gap: 6px; }
.waste-top  { display: flex; align-items: center; justify-content: space-between; }
.waste-info { display: flex; align-items: center; gap: 8px; }
.waste-rank {
    width: 22px; height: 22px; border-radius: 50%;
    background: #FFCDD2; color: #C62828;
    font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.waste-name    { font-size: 14px; font-weight: 600; color: #222; }
.waste-amounts { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; }
.waste-value   { font-size: 13px; font-weight: 700; color: #C62828; }
.waste-rate    { font-size: 11px; color: #999; }
.waste-bar-track { height: 5px; background: #F0F0F0; border-radius: 3px; overflow: hidden; }
.waste-bar-fill  { height: 100%; background: #E57373; border-radius: 3px; transition: width .4s ease; }

/* ── Quick Actions ── */
.action-list { display: flex; flex-direction: column; gap: 10px; }
.action-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    border: 1px solid #E8E8E8;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: border-color .2s, background .2s;
}
.action-card:hover { border-color: #2D7A3E; background: #F9FDF9; }
.action-card.primary { border-color: #2D7A3E; background: #F0F7F2; }
.action-icon  { font-size: 22px; flex-shrink: 0; }
.action-label { font-size: 14px; font-weight: 600; color: #222; }
.action-sub   { font-size: 12px; color: #888; margin-top: 2px; }

/* ── Entry table ── */
.entry-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.entry-table th { text-align: left; padding: 8px 10px; font-size: 12px; color: #999; border-bottom: 1px solid #F0F0F0; }
.entry-table td { padding: 10px 10px; border-bottom: 1px solid #F5F5F5; }
.entry-table tr:last-child td { border-bottom: none; }

.rate-badge {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.rate-badge.good    { background: #E8F5E9; color: #2D7A3E; }
.rate-badge.warning { background: #FFF8E1; color: #E65100; }
.rate-badge.danger  { background: #FFEBEE; color: #C62828; }

.link-btn { font-size: 13px; color: #2D7A3E; text-decoration: none; }
.link-btn:hover { text-decoration: underline; }

.empty-state { text-align: center; color: #999; font-size: 14px; }
</style>

@endsection