@extends('layouts.app')

@section('content')

{{-- ── Page Header ── --}}
<div class="page-header">
    <div>
        <h1 class="page-title">Analytics</h1>
        <p class="page-subtitle">Diagnostic & Prescriptive Inventory Intelligence</p>
    </div>
</div>

{{-- ── Filter Bar ── --}}
<div class="a-card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('analytics.index') }}">
        <div class="filter-row">
            <div class="form-group">
                <label>View Period</label>
                <select name="time_period" class="form-control" onchange="this.form.submit()">
                    <option value="daily"   {{ $timePeriod === 'daily'   ? 'selected' : '' }}>Daily</option>
                    <option value="weekly"  {{ $timePeriod === 'weekly'  ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $timePeriod === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date From</label>
                <select name="date_range" class="form-control" onchange="this.form.submit()">
                    <option value="">All Time</option>
                    <option value="{{ now()->subDays(7)->format('Y-m-d') }}"
                        {{ $dateRange === now()->subDays(7)->format('Y-m-d')  ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="{{ now()->subDays(30)->format('Y-m-d') }}"
                        {{ $dateRange === now()->subDays(30)->format('Y-m-d') ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="{{ now()->subDays(90)->format('Y-m-d') }}"
                        {{ $dateRange === now()->subDays(90)->format('Y-m-d') ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </div>
        </div>
    </form>
</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 1 · Business KPIs
══════════════════════════════════════════════════════════════ --}}
<div class="section-label">📊 Business KPIs</div>
<div class="kpi-row">

    {{-- Efficiency Score with circular gauge --}}
    @php
        $gaugeColors = ['success' => '#2D7A3E', 'info' => '#1565C0', 'warning' => '#E65100', 'danger' => '#C62828'];
        $gaugeColor  = $gaugeColors[$kpis['efficiency_color']] ?? '#2D7A3E';
    @endphp
    <div class="a-card kpi-score-card">
        <div class="score-circle"
             style="background: conic-gradient({{ $gaugeColor }} {{ $kpis['efficiency_score'] }}%, #e9ecef 0);">
            <div class="score-inner">
                <span class="score-num">{{ $kpis['efficiency_score'] }}</span>
                <span class="score-sub">/100</span>
            </div>
        </div>
        <div>
            <div class="kpi-label">Efficiency Score</div>
            <div class="kpi-badge kpi-badge-{{ $kpis['efficiency_color'] }}">
                {{ $kpis['efficiency_label'] }}
            </div>
        </div>
    </div>

    <div class="a-card kpi-card">
        <div class="kpi-label">Inventory Utilization</div>
        <div class="kpi-value {{ $kpis['utilization_rate'] >= 85 ? 'c-success' : ($kpis['utilization_rate'] >= 70 ? 'c-warning' : 'c-danger') }}">
            {{ $kpis['utilization_rate'] }}%
        </div>
        <div class="kpi-track">
            <div class="kpi-fill" style="width:{{ $kpis['utilization_rate'] }}%;
                background: {{ $kpis['utilization_rate'] >= 85 ? '#2D7A3E' : ($kpis['utilization_rate'] >= 70 ? '#E65100' : '#C62828') }};"></div>
        </div>
        <div class="kpi-hint">of inventory consumed</div>
    </div>

    <div class="a-card kpi-card">
        <div class="kpi-label">Revenue Loss</div>
        <div class="kpi-value c-danger">₱{{ number_format($kpis['revenue_loss'], 2) }}</div>
        <div class="kpi-hint">Total monetary waste</div>
    </div>

    <div class="a-card kpi-card">
        <div class="kpi-label">Cost per Waste Unit</div>
        <div class="kpi-value">₱{{ number_format($kpis['cost_per_waste'], 2) }}</div>
        <div class="kpi-hint">Average cost of 1 wasted unit</div>
    </div>

    <div class="a-card kpi-card">
        <div class="kpi-label">Overall Waste Rate</div>
        <div class="kpi-value {{ $kpis['waste_rate'] >= 20 ? 'c-danger' : ($kpis['waste_rate'] >= 10 ? 'c-warning' : 'c-success') }}">
            {{ $kpis['waste_rate'] }}%
        </div>
        <div class="kpi-hint">{{ $kpis['tracked_items'] }} items tracked</div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 2 · Decision Cards
══════════════════════════════════════════════════════════════ --}}
@if(!empty($decisionCards))
<div class="section-label" style="margin-top:26px;">🎯 Decision Cards</div>
<div class="decision-grid">
    @foreach($decisionCards as $card)
    <div class="decision-card dc-{{ $card['type'] }}">
        <div class="dc-header">
            <span class="dc-icon">{{ $card['icon'] }}</span>
            <span class="dc-label">{{ $card['label'] }}</span>
        </div>
        <div class="dc-title">{{ $card['title'] }}</div>
        <div class="dc-detail">{{ $card['detail'] }}</div>
        <div class="dc-action">→ {{ $card['action'] }}</div>
    </div>
    @endforeach
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3 · Root Cause + Recommendations
══════════════════════════════════════════════════════════════ --}}
<div class="two-col" style="margin-top:20px;">

    <div class="a-card">
        <h3 class="section-title">🔍 Root Cause Analysis</h3>
        <p class="section-desc">Waste categories inferred from consumption patterns</p>
        <div class="rc-list">
            @foreach($rootCauses as $rc)
            <div class="rc-item rc-{{ $rc['color'] }} {{ $rc['count'] === 0 ? 'rc-dim' : '' }}">
                <div class="rc-top">
                    <span class="rc-icon">{{ $rc['icon'] }}</span>
                    <div class="rc-meta">
                        <div class="rc-title">{{ $rc['cause'] }}</div>
                        <div class="rc-count">
                            {{ $rc['count'] }} item{{ $rc['count'] !== 1 ? 's' : '' }}
                            @if($rc['wasted_value'] > 0)
                                · ₱{{ number_format($rc['wasted_value'], 2) }} lost
                            @endif
                        </div>
                    </div>
                    @if($rc['count'] > 0)
                        <span class="rc-badge rc-badge-{{ $rc['color'] }}">{{ $rc['count'] }}</span>
                    @endif
                </div>
                @if(!empty($rc['items']))
                    <div class="rc-tags">
                        @foreach($rc['items'] as $name)
                            <span class="rc-tag">{{ $name }}</span>
                        @endforeach
                        @if($rc['count'] > 3)
                            <span class="rc-tag rc-tag-dim">+{{ $rc['count'] - 3 }} more</span>
                        @endif
                    </div>
                @endif
                <div class="rc-desc">{{ $rc['description'] }}</div>
                <div class="rc-action">💡 {{ $rc['action'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="a-card">
        <h3 class="section-title">🚀 Recommendation Engine</h3>
        <p class="section-desc">Prioritized actions to reduce waste and cost</p>
        @if(!empty($recommendations))
            <div class="rec-list">
                @foreach($recommendations as $rec)
                <div class="rec-item rec-{{ $rec['priority'] }}">
                    <div class="rec-bar"></div>
                    <div class="rec-body">
                        <div class="rec-top">
                            <span>{{ $rec['icon'] }}</span>
                            <span class="rec-badge rec-badge-{{ $rec['priority'] }}">{{ strtoupper($rec['priority']) }}</span>
                        </div>
                        <div class="rec-action">{{ $rec['action'] }}</div>
                        <div class="rec-reason">{{ $rec['reason'] }}</div>
                        <div class="rec-saving">
                            Est. saving: <strong>₱{{ number_format($rec['estimated_saving'], 2) }}</strong>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <p class="empty-msg">No recommendations — inventory is well-managed. ✅</p>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4 · Impact Estimation Banner
══════════════════════════════════════════════════════════════ --}}
@if($impact['current_loss'] > 0)
<div class="impact-banner" style="margin-top:20px;">
    <div class="impact-title">💸 Estimated Impact if All Recommendations Are Applied</div>
    <div class="impact-row">
        <div class="impact-block">
            <div class="impact-label">Current Loss</div>
            <div class="impact-val impact-red">₱{{ number_format($impact['current_loss'], 2) }}</div>
        </div>
        <div class="impact-arrow">→</div>
        <div class="impact-block">
            <div class="impact-label">Projected After Changes</div>
            <div class="impact-val impact-green">₱{{ number_format($impact['projected_loss_after'], 2) }}</div>
        </div>
        <div class="impact-sep"></div>
        <div class="impact-block impact-highlight">
            <div class="impact-label">Estimated Savings</div>
            <div class="impact-val impact-green">₱{{ number_format($impact['total_saving'], 2) }}</div>
            <div class="impact-pct">{{ number_format($impact['improvement_pct'], 1) }}% improvement</div>
        </div>
    </div>
    <div class="impact-note">
        * Based on {{ $impact['recommendation_count'] }} recommended action(s).
        Actual results depend on implementation.
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5 · Item Performance Intelligence
══════════════════════════════════════════════════════════════ --}}
<div class="a-card" style="margin-top:20px;">
    <h3 class="section-title">🧠 Item Performance Intelligence</h3>
    <p class="section-desc">Profitability score and classification per item</p>

    @if($itemIntelligence->isNotEmpty())
    <div class="table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Used</th>
                    <th>Wasted</th>
                    <th>Profitability</th>
                    <th>Waste Rate</th>
                    <th>Classification</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemIntelligence as $item)
                <tr>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>{{ $item->category ?: '—' }}</td>
                    <td>{{ number_format($item->total_used, 2) }}</td>
                    <td>{{ number_format($item->total_wasted, 2) }}</td>
                    <td>
                        <div class="prof-wrap">
                            <div class="prof-track">
                                <div class="prof-fill" style="width:{{ $item->profitability_score }}%"></div>
                            </div>
                            <span class="prof-pct">{{ $item->profitability_score }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="w-badge {{ $item->waste_rating >= 50 ? 'wb-high' : ($item->waste_rating >= 25 ? 'wb-med' : 'wb-low') }}">
                            {{ number_format($item->waste_rating, 1) }}%
                        </span>
                    </td>
                    <td>
                        <span class="cls-badge cls-{{ $item->class_color }}">
                            {{ $item->class_icon }} {{ $item->class_label }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <p class="empty-msg">No data available yet.</p>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6 · Category Breakdown
══════════════════════════════════════════════════════════════ --}}
@if($categoryBreakdown->isNotEmpty())
<div class="a-card" style="margin-top:20px;">
    <h3 class="section-title">📂 Category Breakdown</h3>
    <div class="table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Category</th><th>Items</th><th>Total Used</th>
                    <th>Total Wasted</th><th>Used Value</th>
                    <th>Wasted Value</th><th>Waste Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryBreakdown as $cat)
                <tr>
                    <td><strong>{{ $cat['category'] }}</strong></td>
                    <td>{{ $cat['item_count'] }}</td>
                    <td>{{ number_format($cat['total_used'], 2) }}</td>
                    <td>{{ number_format($cat['total_wasted'], 2) }}</td>
                    <td>₱{{ number_format($cat['used_value'], 2) }}</td>
                    <td>₱{{ number_format($cat['wasted_value'], 2) }}</td>
                    <td>
                        <span class="w-badge {{ $cat['waste_rate'] >= 50 ? 'wb-high' : ($cat['waste_rate'] >= 25 ? 'wb-med' : 'wb-low') }}">
                            {{ $cat['waste_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     SECTION 7 · Bar Charts
══════════════════════════════════════════════════════════════ --}}
<div class="two-col" style="margin-top:20px;">

    <div class="a-card">
        <h3 class="section-title">🗑️ Most Wasted Items</h3>
        @if($mostWasted->count() > 0)
            @php $maxW = max($mostWasted->max('total_wasted'), 1); @endphp
            <div class="bar-chart">
                @foreach($mostWasted as $item)
                <div class="bar-row">
                    <div class="bar-label" title="{{ $item->name }}">{{ $item->name }}</div>
                    <div class="bar-track">
                        <div class="bar-fill bar-waste" data-w="{{ ($item->total_wasted / $maxW) * 100 }}"></div>
                    </div>
                    <div class="bar-val">{{ number_format($item->total_wasted, 2) }}</div>
                </div>
                @endforeach
            </div>
        @else
            <p class="empty-msg">No waste data.</p>
        @endif
    </div>

    <div class="a-card">
        <h3 class="section-title">✅ Most Used Items</h3>
        @if($mostUsed->count() > 0)
            @php $maxU = max($mostUsed->max('total_used'), 1); @endphp
            <div class="bar-chart">
                @foreach($mostUsed as $item)
                <div class="bar-row">
                    <div class="bar-label" title="{{ $item->name }}">{{ $item->name }}</div>
                    <div class="bar-track">
                        <div class="bar-fill bar-used" data-w="{{ ($item->total_used / $maxU) * 100 }}"></div>
                    </div>
                    <div class="bar-val">{{ number_format($item->total_used, 2) }}</div>
                </div>
                @endforeach
            </div>
        @else
            <p class="empty-msg">No usage data.</p>
        @endif
    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 8 · Period Timeline
══════════════════════════════════════════════════════════════ --}}
<div class="a-card" style="margin-top:20px;">
    <h3 class="section-title">📅 {{ ucfirst($timePeriod) }} Timeline</h3>
    <p class="section-desc">Aggregated totals per {{ $timePeriod }} period</p>
    @if($periodSummary->isNotEmpty())
    <div class="table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Period</th><th>Items</th><th>Total Used</th>
                    <th>Total Wasted</th><th>Used Value</th>
                    <th>Wasted Value</th><th>Waste Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($periodSummary as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['date'])->format('M j, Y') }}</td>
                    <td>{{ $row['item_count'] }}</td>
                    <td>{{ number_format($row['total_used'], 2) }}</td>
                    <td>{{ number_format($row['total_wasted'], 2) }}</td>
                    <td>₱{{ number_format($row['used_value'], 2) }}</td>
                    <td>₱{{ number_format($row['wasted_value'], 2) }}</td>
                    <td>
                        <span class="w-badge {{ $row['waste_rate'] >= 50 ? 'wb-high' : ($row['waste_rate'] >= 25 ? 'wb-med' : 'wb-low') }}">
                            {{ $row['waste_rate'] }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <p class="empty-msg">No period data available.</p>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 9 · Full Item Comparison
══════════════════════════════════════════════════════════════ --}}
<div class="a-card" style="margin-top:20px;">
    <h3 class="section-title">📋 Full Item Comparison</h3>
    @if($usageComparison->count() > 0)
    <div class="table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Item</th><th>Category</th><th>Used Qty</th>
                    <th>Wasted Qty</th><th>Used Value</th>
                    <th>Waste Cost</th><th>Waste Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usageComparison->sortByDesc('wasted_value') as $item)
                <tr>
                    <td><strong>{{ $item->name }}</strong></td>
                    <td>{{ $item->category ?: '—' }}</td>
                    <td>{{ number_format($item->total_used, 2) }}</td>
                    <td>{{ number_format($item->total_wasted, 2) }}</td>
                    <td>₱{{ number_format($item->used_value, 2) }}</td>
                    <td>₱{{ number_format($item->wasted_value, 2) }}</td>
                    <td>
                        <span class="w-badge {{ $item->waste_rating >= 50 ? 'wb-high' : ($item->waste_rating >= 25 ? 'wb-med' : 'wb-low') }}">
                            {{ number_format($item->waste_rating, 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
        <p class="empty-msg">No comparison data available.</p>
    @endif
</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 10 · System Insights
══════════════════════════════════════════════════════════════ --}}
@if(!empty($insights))
<div class="a-card" style="margin-top:20px;">
    <h3 class="section-title">💡 System Insights</h3>
    <div class="insights-grid">
        @foreach($insights as $ins)
        <div class="ins-box ins-{{ $ins['type'] }}">
            <span class="ins-icon">{{ $ins['icon'] }}</span>
            <div>
                <div class="ins-title">{{ $ins['title'] }}</div>
                <div class="ins-msg">{{ $ins['message'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<style>
/* ── Base ── */
.page-subtitle { font-size: 13px; color: #888; margin-top: 2px; }
.section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #aaa; margin-bottom: 10px; }
.section-title { font-size: 15px; font-weight: 700; color: #111; margin: 0 0 3px; }
.section-desc  { font-size: 12px; color: #999; margin: 0 0 14px; }
.empty-msg     { font-size: 13px; color: #bbb; margin: 8px 0; }
.a-card        { background: #fff; border: 1px solid #ebebeb; border-radius: 12px; padding: 20px; }
.two-col       { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.table-wrap    { overflow-x: auto; margin-top: 12px; }

/* ── Filter ── */
.filter-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px; }
.form-control { width: 100%; padding: 9px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; }

/* ── KPI Row ── */
.kpi-row {
    display: grid;
    grid-template-columns: auto 1fr 1fr 1fr 1fr;
    gap: 14px;
}
.kpi-score-card { display: flex; align-items: center; gap: 16px; min-width: 190px; }
.score-circle {
    width: 84px; height: 84px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.score-inner {
    width: 64px; height: 64px; background: #fff; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.score-num { font-size: 20px; font-weight: 800; color: #111; line-height: 1.1; }
.score-sub { font-size: 10px; color: #bbb; }
.kpi-label { font-size: 11px; color: #aaa; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
.kpi-badge { font-size: 14px; font-weight: 700; margin-top: 4px; }
.kpi-badge-success { color: #2D7A3E; }
.kpi-badge-info    { color: #1565C0; }
.kpi-badge-warning { color: #E65100; }
.kpi-badge-danger  { color: #C62828; }
.kpi-card  { display: flex; flex-direction: column; }
.kpi-value { font-size: 24px; font-weight: 700; color: #111; margin-bottom: 4px; }
.c-success { color: #2D7A3E; } .c-warning { color: #E65100; } .c-danger { color: #C62828; }
.kpi-track { height: 5px; background: #f0f0f0; border-radius: 3px; margin-bottom: 4px; overflow: hidden; }
.kpi-fill  { height: 100%; border-radius: 3px; }
.kpi-hint  { font-size: 11px; color: #bbb; margin-top: auto; }

/* ── Decision Cards ── */
.decision-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 14px; }
.decision-card { border-radius: 12px; padding: 18px; border: 1px solid transparent; }
.dc-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.dc-icon   { font-size: 18px; }
.dc-label  { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; }
.dc-title  { font-size: 13px; font-weight: 700; color: #111; line-height: 1.4; margin-bottom: 6px; }
.dc-detail { font-size: 12px; color: #666; line-height: 1.5; margin-bottom: 10px; }
.dc-action { font-size: 12px; font-style: italic; }

.dc-critical    { background: #fff0f0; border-color: #fecaca; }
.dc-critical    .dc-label, .dc-critical    .dc-action { color: #C62828; }
.dc-warning     { background: #fffbeb; border-color: #fde68a; }
.dc-warning     .dc-label, .dc-warning     .dc-action { color: #E65100; }
.dc-improvement { background: #f0fdf4; border-color: #bbf7d0; }
.dc-improvement .dc-label, .dc-improvement .dc-action { color: #2D7A3E; }
.dc-success     { background: #f0fdf4; border-color: #bbf7d0; }
.dc-success     .dc-label, .dc-success     .dc-action { color: #2D7A3E; }
.dc-info        { background: #eff6ff; border-color: #bfdbfe; }
.dc-info        .dc-label, .dc-info        .dc-action { color: #1565C0; }
.dc-danger      { background: #fff0f0; border-color: #fecaca; }
.dc-danger      .dc-label, .dc-danger      .dc-action { color: #C62828; }

/* ── Root Cause ── */
.rc-list { display: flex; flex-direction: column; gap: 12px; }
.rc-item { padding: 14px; border-radius: 10px; border-left: 4px solid transparent; }
.rc-dim  { opacity: .55; }
.rc-top  { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; }
.rc-icon { font-size: 18px; flex-shrink: 0; }
.rc-meta { flex: 1; }
.rc-title { font-size: 13px; font-weight: 700; color: #111; }
.rc-count { font-size: 11px; color: #888; margin-top: 1px; }
.rc-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; flex-shrink: 0; }
.rc-tags  { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px; }
.rc-tag   { font-size: 11px; padding: 2px 8px; border-radius: 99px; background: rgba(0,0,0,.06); color: #555; }
.rc-tag-dim { color: #bbb; }
.rc-desc  { font-size: 12px; color: #666; line-height: 1.5; margin-bottom: 5px; }
.rc-action { font-size: 12px; font-weight: 600; color: #2D7A3E; }

.rc-danger  { background: #fff0f0; border-color: #C62828; }
.rc-warning { background: #fffbeb; border-color: #E65100; }
.rc-success { background: #f0fdf4; border-color: #2D7A3E; }
.rc-badge-danger  { background: #fecaca; color: #C62828; }
.rc-badge-warning { background: #fde68a; color: #92400e; }
.rc-badge-success { background: #bbf7d0; color: #166534; }

/* ── Recommendations ── */
.rec-list { display: flex; flex-direction: column; gap: 10px; }
.rec-item { display: flex; border-radius: 10px; border: 1px solid #ebebeb; overflow: hidden; }
.rec-bar  { width: 5px; flex-shrink: 0; }
.rec-high   .rec-bar { background: #C62828; }
.rec-medium .rec-bar { background: #E65100; }
.rec-low    .rec-bar { background: #2D7A3E; }
.rec-body   { padding: 12px; flex: 1; }
.rec-top    { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
.rec-badge  { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 99px; }
.rec-badge-high   { background: #fecaca; color: #C62828; }
.rec-badge-medium { background: #fde68a; color: #92400e; }
.rec-badge-low    { background: #bbf7d0; color: #166534; }
.rec-action { font-size: 13px; font-weight: 700; color: #111; line-height: 1.4; margin-bottom: 4px; }
.rec-reason { font-size: 12px; color: #888; margin-bottom: 4px; }
.rec-saving { font-size: 12px; color: #2D7A3E; }

/* ── Impact Banner ── */
.impact-banner {
    background: linear-gradient(135deg, #163d22 0%, #2D7A3E 100%);
    border-radius: 14px; padding: 22px 24px; color: #fff;
}
.impact-title { font-size: 13px; font-weight: 700; opacity: .85; margin-bottom: 18px; }
.impact-row   { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; }
.impact-block { display: flex; flex-direction: column; gap: 3px; }
.impact-highlight { margin-left: auto; }
.impact-label { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; opacity: .65; }
.impact-val   { font-size: 26px; font-weight: 800; line-height: 1.1; }
.impact-red   { color: #fca5a5; }
.impact-green { color: #6ee7b7; }
.impact-pct   { font-size: 12px; opacity: .75; }
.impact-arrow { font-size: 22px; opacity: .5; }
.impact-sep   { width: 1px; height: 48px; background: rgba(255,255,255,.2); }
.impact-note  { font-size: 11px; opacity: .45; margin-top: 16px; }

/* ── Item Intelligence ── */
.prof-wrap  { display: flex; align-items: center; gap: 8px; }
.prof-track { width: 60px; height: 5px; background: #f0f0f0; border-radius: 3px; overflow: hidden; }
.prof-fill  { height: 100%; background: #2D7A3E; }
.prof-pct   { font-size: 12px; color: #555; }
.cls-badge  { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 99px; white-space: nowrap; }
.cls-success { background: #dcfce7; color: #2D7A3E; }
.cls-danger  { background: #fecaca; color: #C62828; }
.cls-warning { background: #fde68a; color: #92400e; }
.cls-neutral { background: #f3f4f6; color: #555; }

/* ── Bar Charts ── */
.bar-chart { display: flex; flex-direction: column; gap: 12px; margin-top: 14px; }
.bar-row   { display: grid; grid-template-columns: 110px 1fr 70px; gap: 10px; align-items: center; }
.bar-label { font-size: 12px; font-weight: 600; color: #374151; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.bar-track { height: 10px; background: #f0f0f0; border-radius: 99px; overflow: hidden; }
.bar-fill  { height: 100%; border-radius: 99px; }
.bar-waste { background: linear-gradient(90deg, #C62828, #ef4444); }
.bar-used  { background: linear-gradient(90deg, #2D7A3E, #22c55e); }
.bar-val   { font-size: 12px; font-weight: 700; text-align: right; color: #111; }

/* ── Tables ── */
.a-table { width: 100%; border-collapse: collapse; }
.a-table th,
.a-table td { padding: 11px 10px; text-align: left; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
.a-table th { background: #f8fafc; color: #666; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.a-table tr:last-child td { border-bottom: none; }
.a-table tr:hover td { background: #fafafa; }
.w-badge { display: inline-block; padding: 3px 9px; border-radius: 99px; font-size: 11px; font-weight: 700; }
.wb-high { background: #fecaca; color: #b91c1c; }
.wb-med  { background: #fde68a; color: #92400e; }
.wb-low  { background: #dcfce7; color: #166534; }

/* ── Insights ── */
.insights-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; margin-top: 14px; }
.ins-box  { display: flex; gap: 12px; padding: 14px; border-radius: 10px; border-left: 4px solid transparent; border: 1px solid #ebebeb; }
.ins-icon { font-size: 22px; flex-shrink: 0; }
.ins-title { font-size: 13px; font-weight: 700; color: #111; margin-bottom: 4px; }
.ins-msg   { font-size: 12px; color: #666; line-height: 1.5; }
.ins-success { background: #f0fdf4; border-left: 4px solid #2D7A3E; }
.ins-warning { background: #fffbeb; border-left: 4px solid #E65100; }
.ins-danger  { background: #fff0f0; border-left: 4px solid #C62828; }
.ins-info    { background: #eff6ff; border-left: 4px solid #1565C0; }

/* ── Responsive ── */
@media (max-width: 1100px) {
    .kpi-row { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
    .two-col, .filter-row, .kpi-row { grid-template-columns: 1fr; }
    .bar-row { grid-template-columns: 1fr; }
    .impact-row { flex-direction: column; }
    .impact-highlight { margin-left: 0; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bar-fill[data-w]').forEach(el => {
        el.style.width = parseFloat(el.dataset.w || 0) + '%';
    });
});
</script>

@endsection