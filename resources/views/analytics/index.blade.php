@extends('layouts.app')

@section('content')

{{-- ═══════════════════════════════════════════════════════════════
     ANALYTICS DASHBOARD · REDESIGNED PRESENTATION LAYER
     Backend variables preserved exactly. Only frontend changed.
═══════════════════════════════════════════════════════════════ --}}

{{-- ── Hidden filter form (referenced by sticky nav controls) ── --}}
<form id="analytics-filter-form" method="GET" action="{{ route('analytics.index') }}" style="display:none;">
    <input type="hidden" name="date_range" id="ff-date-range" value="{{ $dateRange }}">
</form>

{{-- ══════════════════════════════════════════════════════════════
     EXECUTIVE HERO SECTION
══════════════════════════════════════════════════════════════ --}}
@php
    $heroStatus = match(true) {
        $kpis['efficiency_score'] >= 80 => ['label' => 'Healthy Operations', 'color' => 'hero-status-green', 'dot' => '#4ade80'],
        $kpis['efficiency_score'] >= 60 => ['label' => 'Needs Attention',    'color' => 'hero-status-amber', 'dot' => '#fbbf24'],
        default                          => ['label' => 'Critical State',    'color' => 'hero-status-red',   'dot' => '#f87171'],
    };
    $topInsight = '';
    if (!empty($insights)) {
        $topInsight = $insights[0]['title'] . ': ' . $insights[0]['message'];
    } elseif (!empty($recommendations)) {
        $topInsight = $recommendations[0]['action'];
    }
@endphp

<div class="exec-hero">
    <div class="exec-hero-bg"></div>
    <div class="exec-hero-inner">

        {{-- Left: Identity + status --}}
        <div class="hero-identity">
            <div class="hero-eyebrow">
                <span class="hero-status-dot" style="background:{{ $heroStatus['dot'] }};"></span>
                <span class="hero-status-label {{ $heroStatus['color'] }}">{{ $heroStatus['label'] }}</span>
            </div>
            <h1 class="hero-title">Analytics</h1>
            <p class="hero-subtitle">Diagnostic &amp; Prescriptive Inventory Intelligence</p>

            {{-- AI top insight --}}
            @if($topInsight)
            <div class="hero-ai-insight">
                <span class="hai-icon">✦</span>
                <span class="hai-text">{{ $topInsight }}</span>
            </div>
            @endif
        </div>

        {{-- Right: Hero KPIs --}}
        <div class="hero-kpis">

            {{-- Efficiency score ring --}}
            @php
                $ringClr = match($kpis['efficiency_color']) {
                    'success' => '#4ade80', 'info' => '#60a5fa',
                    'warning' => '#fbbf24', 'danger' => '#f87171',
                    default   => '#4ade80'
                };
            @endphp
            <div class="hero-kpi-ring">
                <div class="hkr-outer" style="background: conic-gradient({{ $ringClr }} {{ $kpis['efficiency_score'] }}%, rgba(255,255,255,.08) 0);">
                    <div class="hkr-inner">
                        <span class="hkr-num">{{ $kpis['efficiency_score'] }}</span>
                        <span class="hkr-sub">Efficiency</span>
                    </div>
                </div>
                <div class="hkr-label">{{ $kpis['efficiency_label'] }}</div>
            </div>

            <div class="hero-kpi-stack">
                <div class="hks-card hks-red">
                    <span class="hks-label">Current Waste Loss</span>
                    <span class="hks-val">₱{{ number_format($kpis['revenue_loss'], 2) }}</span>
                    <span class="hks-sub">Total monetary waste</span>
                </div>
                <div class="hks-card hks-amber">
                    <span class="hks-label">Waste Rate</span>
                    <span class="hks-val">{{ $kpis['waste_rate'] }}%</span>
                    <span class="hks-sub">{{ $kpis['tracked_items'] }} items tracked</span>
                </div>
                @if(isset($projectedLoss) && $projectedLoss['confidence'] !== 'insufficient')
                <div class="hks-card hks-blue">
                    <span class="hks-label">{{ $projectedLoss['days_ahead'] }}-Day Forecast</span>
                    <span class="hks-val">₱{{ number_format($projectedLoss['projected_loss'], 2) }}</span>
                    <span class="hks-sub">Projected loss</span>
                </div>
                @endif
                <div class="hks-card hks-green">
                    <span class="hks-label">Utilization</span>
                    <span class="hks-val">{{ $kpis['utilization_rate'] }}%</span>
                    <div class="hks-bar-track"><div class="hks-bar-fill" style="width:{{ $kpis['utilization_rate'] }}%;"></div></div>
                </div>
            </div>

        </div>
    </div>

    {{-- Filter controls in hero --}}
    <div class="hero-filter-strip">
        <div class="hfs-label">Range:</div>
        <button class="hfs-btn {{ !$dateRange ? 'hfs-btn-active' : '' }}"
                onclick="setFilter('date_range','')">All Time</button>
        <button class="hfs-btn {{ $dateRange === now()->subDays(7)->format('Y-m-d') ? 'hfs-btn-active' : '' }}"
                onclick="setFilter('date_range','{{ now()->subDays(7)->format('Y-m-d') }}')">7 Days</button>
        <button class="hfs-btn {{ $dateRange === now()->subDays(30)->format('Y-m-d') ? 'hfs-btn-active' : '' }}"
                onclick="setFilter('date_range','{{ now()->subDays(30)->format('Y-m-d') }}')">30 Days</button>
        <button class="hfs-btn {{ $dateRange === now()->subDays(90)->format('Y-m-d') ? 'hfs-btn-active' : '' }}"
                onclick="setFilter('date_range','{{ now()->subDays(90)->format('Y-m-d') }}')">90 Days</button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     STICKY ANALYTICS NAVIGATION
══════════════════════════════════════════════════════════════ --}}
<nav class="analytics-nav" id="analytics-nav">
    <div class="an-inner">
        <a href="#section-overview"     class="an-tab an-tab-active" data-section="section-overview">Overview</a>
        <a href="#section-waste"        class="an-tab"               data-section="section-waste">Waste Analysis</a>
        <a href="#section-predictive"   class="an-tab"               data-section="section-predictive">Predictive</a>
        <a href="#section-recommend"    class="an-tab"               data-section="section-recommend">Recommendations</a>
        <a href="#section-intelligence" class="an-tab"               data-section="section-intelligence">Item Intelligence</a>
        <a href="#section-deep"         class="an-tab"               data-section="section-deep">Deep Analytics</a>
    </div>
</nav>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 1 · OVERVIEW  (Decision Cards + Impact Banner)
══════════════════════════════════════════════════════════════ --}}
<div id="section-overview" class="dash-section">

    <div class="section-header">
        <div class="sh-icon">🎯</div>
        <div>
            <div class="sh-title">Decision Cards</div>
            <div class="sh-desc">Priority actions surfaced from your inventory data</div>
        </div>
    </div>

    @if(!empty($decisionCards))
    <div class="decision-grid">
        @foreach($decisionCards as $card)
        <div class="dc2 dc2-{{ $card['type'] }}">
            <div class="dc2-header">
                <span class="dc2-icon">{{ $card['icon'] }}</span>
                <span class="dc2-badge">{{ $card['label'] }}</span>
            </div>
            <div class="dc2-title">{{ $card['title'] }}</div>
            <div class="dc2-detail">{{ $card['detail'] }}</div>
            <div class="dc2-action">{{ $card['action'] }} →</div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="es-icon">✅</div>
        <div class="es-title">All Clear</div>
        <div class="es-desc">No immediate decisions required. Your inventory is well-managed.</div>
    </div>
    @endif

    {{-- Impact Banner --}}
    @if($impact['current_loss'] > 0)
    <div class="impact2-banner">
        <div class="impact2-left">
            <div class="impact2-eyebrow">💸 Estimated Impact if All Recommendations Applied</div>
            <div class="impact2-flow">
                <div class="impact2-block">
                    <div class="impact2-lbl">Current Loss</div>
                    <div class="impact2-val impact2-red">₱{{ number_format($impact['current_loss'], 2) }}</div>
                </div>
                <div class="impact2-arrow">→</div>
                <div class="impact2-block">
                    <div class="impact2-lbl">After Changes</div>
                    <div class="impact2-val impact2-green">₱{{ number_format($impact['projected_loss_after'], 2) }}</div>
                </div>
                <div class="impact2-sep"></div>
                <div class="impact2-block impact2-highlight">
                    <div class="impact2-lbl">You Save</div>
                    <div class="impact2-val impact2-green">₱{{ number_format($impact['total_saving'], 2) }}</div>
                    <div class="impact2-pct">{{ number_format($impact['improvement_pct'], 1) }}% improvement</div>
                </div>
            </div>
            <div class="impact2-note">Based on {{ $impact['recommendation_count'] }} action(s) · Results depend on implementation</div>
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 2 · WASTE ANALYSIS  (Root cause + bar charts + insights)
══════════════════════════════════════════════════════════════ --}}
<div id="section-waste" class="dash-section">

    <div class="section-header">
        <div class="sh-icon">🔍</div>
        <div>
            <div class="sh-title">Waste Analysis</div>
            <div class="sh-desc">Root causes, patterns, and system insights</div>
        </div>
    </div>

    {{-- Root Cause + Insights side by side --}}
    <div class="grid-2col">

        {{-- Root Cause Analysis --}}
        <div class="analytics-card">
            <div class="ac-header">
                <span class="ac-title">Root Cause Analysis</span>
                <span class="ac-sub">Inferred from consumption patterns</span>
            </div>
            <div class="rc2-list">
                @foreach($rootCauses as $rc)
                <div class="rc2-item rc2-{{ $rc['color'] }} {{ $rc['count'] === 0 ? 'rc2-dim' : '' }}">
                    <div class="rc2-row">
                        <span class="rc2-icon">{{ $rc['icon'] }}</span>
                        <div class="rc2-body">
                            <div class="rc2-title">{{ $rc['cause'] }}</div>
                            <div class="rc2-count">
                                {{ $rc['count'] }} item{{ $rc['count'] !== 1 ? 's' : '' }}
                                @if($rc['wasted_value'] > 0)
                                    &middot; ₱{{ number_format($rc['wasted_value'], 2) }} lost
                                @endif
                            </div>
                            @if(!empty($rc['items']))
                            <div class="rc2-tags">
                                @foreach($rc['items'] as $name)
                                    <span class="tag">{{ $name }}</span>
                                @endforeach
                                @if($rc['count'] > 3)
                                    <span class="tag tag-dim">+{{ $rc['count'] - 3 }} more</span>
                                @endif
                            </div>
                            @endif
                        </div>
                        @if($rc['count'] > 0)
                        <span class="rc2-badge rc2-badge-{{ $rc['color'] }}">{{ $rc['count'] }}</span>
                        @endif
                    </div>
                    <div class="rc2-action">💡 {{ $rc['action'] }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- System Insights --}}
        @if(!empty($insights))
        <div class="analytics-card">
            <div class="ac-header">
                <span class="ac-title">System Insights</span>
                <span class="ac-sub">AI-surfaced patterns in your data</span>
            </div>
            <div class="ins2-list">
                @foreach($insights as $ins)
                <div class="ins2-item ins2-{{ $ins['type'] }}">
                    <span class="ins2-icon">{{ $ins['icon'] }}</span>
                    <div>
                        <div class="ins2-title">{{ $ins['title'] }}</div>
                        <div class="ins2-msg">{{ $ins['message'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- Bar Charts --}}
    <div class="grid-2col" style="margin-top:16px;">

        <div class="analytics-card">
            <div class="ac-header">
                <span class="ac-title">🗑️ Most Wasted Items</span>
                <span class="ac-sub">By quantity</span>
            </div>
            @if($mostWasted->count() > 0)
                @php $maxW = max($mostWasted->max('total_wasted'), 1); @endphp
                <div class="bar2-chart">
                    @foreach($mostWasted as $item)
                    <div class="bar2-row">
                        <div class="bar2-label" title="{{ $item->name }}">{{ $item->name }}</div>
                        <div class="bar2-track">
                            <div class="bar2-fill bar2-waste" data-w="{{ ($item->total_wasted / $maxW) * 100 }}"></div>
                        </div>
                        <div class="bar2-val">{{ number_format($item->total_wasted, 2) }}</div>
                    </div>
                    @endforeach
                </div>
            @else
            <div class="empty-state-inline">
                <span>📊</span> No waste data recorded yet.
            </div>
            @endif
        </div>

        <div class="analytics-card">
            <div class="ac-header">
                <span class="ac-title">✅ Most Used Items</span>
                <span class="ac-sub">By quantity consumed</span>
            </div>
            @if($mostUsed->count() > 0)
                @php $maxU = max($mostUsed->max('total_used'), 1); @endphp
                <div class="bar2-chart">
                    @foreach($mostUsed as $item)
                    <div class="bar2-row">
                        <div class="bar2-label" title="{{ $item->name }}">{{ $item->name }}</div>
                        <div class="bar2-track">
                            <div class="bar2-fill bar2-used" data-w="{{ ($item->total_used / $maxU) * 100 }}"></div>
                        </div>
                        <div class="bar2-val">{{ number_format($item->total_used, 2) }}</div>
                    </div>
                    @endforeach
                </div>
            @else
            <div class="empty-state-inline">
                <span>📊</span> No usage data recorded yet.
            </div>
            @endif
        </div>

    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 3 · PREDICTIVE ANALYTICS
══════════════════════════════════════════════════════════════ --}}
@php
    $confColors = [
        'high'         => ['bg' => '#dcfce7', 'text' => '#166534', 'label' => 'High Confidence'],
        'moderate'     => ['bg' => '#fef9c3', 'text' => '#713f12', 'label' => 'Moderate Confidence'],
        'low'          => ['bg' => '#ffedd5', 'text' => '#7c2d12', 'label' => 'Low Confidence'],
        'insufficient' => ['bg' => '#f1f5f9', 'text' => '#64748b', 'label' => 'Insufficient Data'],
    ];
    $forecastConf = $forecastedWaste->first()?->confidence ?? 'insufficient';
    $projConf     = $projectedLoss['confidence'];
    $confInfo     = $confColors[$forecastConf]  ?? $confColors['insufficient'];
    $projConfInfo = $confColors[$projConf]       ?? $confColors['insufficient'];

    $trendIcon = match($projectedLoss['trend_direction']) {
        'worsening' => '📈', 'improving' => '📉', default => '➡️'
    };
    $trendColor = match($projectedLoss['trend_direction']) {
        'worsening' => '#ef4444', 'improving' => '#22c55e', default => '#94a3b8'
    };

    $maxDow = max($dayOfWeekPatterns->max('avg_waste'), 1);
@endphp

<div id="section-predictive" class="dash-section">

    <div class="section-header">
        <div class="sh-icon">🔮</div>
        <div>
            <div class="sh-title">Predictive Analytics</div>
            <div class="sh-desc">Forecasts based on historical patterns · Not a guarantee</div>
        </div>
    </div>

    {{-- Projected Loss + Day of Week --}}
    <div class="grid-2col">

        {{-- Projected Loss Card --}}
        <div class="forecast-card">
            <div class="fc-top">
                <div>
                    <div class="fc-title">Projected Loss</div>
                    <div class="fc-sub">Next {{ $projectedLoss['days_ahead'] }} days · {{ $projectedLoss['weeks_of_data'] }} week(s) of data</div>
                </div>
                <span class="conf-chip" style="background:{{ $projConfInfo['bg'] }};color:{{ $projConfInfo['text'] }};">
                    {{ $projConfInfo['label'] }}
                </span>
            </div>

            @if($projectedLoss['confidence'] === 'insufficient')
            <div class="empty-state-inline">
                <span>🗓️</span> Log at least 7 days of entries to enable loss projections.
            </div>
            @else
            <div class="fc-metric-row">
                <div class="fc-metric">
                    <div class="fc-metric-lbl">Avg Weekly Loss</div>
                    <div class="fc-metric-val">₱{{ number_format($projectedLoss['avg_weekly_loss'], 2) }}</div>
                </div>
                <div class="fc-metric-arrow">→</div>
                <div class="fc-metric fc-metric-main">
                    <div class="fc-metric-lbl">{{ $projectedLoss['days_ahead'] }}-Day Projection</div>
                    <div class="fc-metric-val fc-metric-big">₱{{ number_format($projectedLoss['projected_loss'], 2) }}</div>
                </div>
            </div>
            <div class="fc-trend" style="color:{{ $trendColor }};">
                {{ $trendIcon }}
                @if($projectedLoss['trend_direction'] === 'stable')
                    Waste cost stable week-over-week
                @else
                    {{ ucfirst($projectedLoss['trend_direction']) }} — {{ $projectedLoss['trend_pct'] }}% vs prior weeks
                @endif
            </div>
            <div class="fc-helper">What this means: {{ $projectedLoss['trend_direction'] === 'worsening' ? 'Your waste costs are climbing. Act on high-priority recommendations now to reverse this trend.' : ($projectedLoss['trend_direction'] === 'improving' ? 'Great progress — your recent changes are reducing waste costs.' : 'Waste costs are holding steady. Small optimizations could reduce the baseline further.') }}</div>
            @endif
        </div>

        {{-- Day-of-Week Heatmap --}}
        <div class="analytics-card">
            <div class="ac-header">
                <span class="ac-title">📅 High-Waste Days</span>
                <span class="ac-sub">Plan procurement around peak waste days</span>
            </div>
            <div class="dow2-chart">
                @foreach($dayOfWeekPatterns as $day)
                    @php
                        $pct      = $maxDow > 0 ? ($day->avg_waste / $maxDow) * 100 : 0;
                        $isPeak   = $pct >= 80;
                        $barClr   = $isPeak ? '#ef4444' : ($pct >= 50 ? '#f97316' : '#22c55e');
                    @endphp
                    <div class="dow2-col">
                        <div class="dow2-val">{{ $day->avg_waste > 0 ? '₱'.number_format($day->avg_waste, 0) : '—' }}</div>
                        <div class="dow2-bar-wrap">
                            <div class="dow2-bar" style="height:{{ max($pct, 4) }}%; background:{{ $barClr }};"></div>
                        </div>
                        <div class="dow2-day {{ $isPeak ? 'dow2-peak' : '' }}">{{ $day->day_name }}</div>
                    </div>
                @endforeach
            </div>
            <div class="dow2-legend">
                <span class="leg-dot" style="background:#ef4444;"></span>Peak &nbsp;
                <span class="leg-dot" style="background:#f97316;"></span>Elevated &nbsp;
                <span class="leg-dot" style="background:#22c55e;"></span>Normal
            </div>
        </div>

    </div>

    {{-- Risk Scores + 7-Day Forecast --}}
    <div class="grid-2col" style="margin-top:16px;">

        {{-- Waste Trend Risk Scores --}}
        <div class="risk-card">
            <div class="ac-header">
                <span class="ac-title">⚠️ Waste Trend Risk</span>
                <span class="ac-sub">Early vs recent 15-day waste rate per item</span>
            </div>

            @if($riskScores->isEmpty())
            <div class="empty-state-inline">
                <span>📅</span> Need at least 30 days of entries to calculate risk trends.
            </div>
            @else
            <div class="risk2-list">
                @foreach($riskScores->take(8) as $item)
                <div class="risk2-row">
                    <div class="risk2-name">{{ $item->risk_icon }} <strong>{{ $item->name }}</strong></div>
                    <div class="risk2-rates">
                        <span class="risk2-lbl">Before</span>
                        <span class="risk2-num">{{ $item->early_rate }}%</span>
                        <span class="risk2-arrow">→</span>
                        <span class="risk2-lbl">Now</span>
                        <span class="risk2-num">{{ $item->recent_rate }}%</span>
                    </div>
                    <div class="risk2-delta" style="color:{{ $item->trend > 0 ? '#ef4444' : ($item->trend < 0 ? '#22c55e' : '#94a3b8') }}">
                        {{ $item->trend > 0 ? '+' : '' }}{{ $item->trend }}%
                    </div>
                    <span class="risk-badge risk-{{ $item->risk_color }}">{{ $item->risk_level }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- 7-Day Waste Forecast --}}
        <div class="forecast-card">
            <div class="fc-top">
                <div>
                    <div class="fc-title">🔭 7-Day Waste Forecast</div>
                    <div class="fc-sub">Projected waste per item (30-day avg)</div>
                </div>
                @if($forecastConf !== 'insufficient')
                <span class="conf-chip" style="background:{{ $confInfo['bg'] }};color:{{ $confInfo['text'] }};">
                    {{ $confInfo['label'] }}
                </span>
                @endif
            </div>

            @if($forecastedWaste->isEmpty())
            <div class="empty-state-inline">
                <span>🔭</span> Not enough waste history to generate item forecasts.
            </div>
            @else
            <div class="forecast2-list">
                @foreach($forecastedWaste->take(8) as $item)
                <div class="forecast2-row">
                    <div class="forecast2-name"><strong>{{ $item->name }}</strong></div>
                    <div class="forecast2-meta">
                        <span class="forecast2-sub">{{ $item->avg_daily_waste }}/day</span>
                        <span class="forecast2-units">{{ number_format($item->forecasted_waste, 2) }} units</span>
                    </div>
                    <div class="forecast2-val {{ $item->forecasted_waste_val >= 100 ? 'fv-red' : ($item->forecasted_waste_val >= 50 ? 'fv-amber' : 'fv-green') }}">
                        ₱{{ number_format($item->forecasted_waste_val, 2) }}
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- Procurement Suggestions --}}
    @if($procurementSuggestions->isNotEmpty())
    <div class="analytics-card" style="margin-top:16px;">
        <div class="ac-header">
            <span class="ac-title">🛒 Procurement Suggestions</span>
            <span class="ac-sub">Recommended daily order reductions based on actual consumption vs implied order (last 30 days)</span>
        </div>
        <div class="table-wrap">
            <table class="a-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Current Order/Day</th>
                        <th>Ideal Order/Day</th>
                        <th>Reduce By</th>
                        <th>Reduction %</th>
                        <th>Est. Monthly Saving</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($procurementSuggestions as $s)
                    <tr>
                        <td><strong>{{ $s->name }}</strong></td>
                        <td>{{ $s->current_daily_order }} units</td>
                        <td>{{ $s->ideal_daily_order }} units</td>
                        <td class="c-amber">↓ {{ $s->suggested_reduction }} units</td>
                        <td>
                            <span class="status-badge {{ $s->reduction_pct >= 30 ? 'sb-red' : ($s->reduction_pct >= 15 ? 'sb-amber' : 'sb-green') }}">
                                {{ $s->reduction_pct }}%
                            </span>
                        </td>
                        <td class="c-green"><strong>₱{{ number_format($s->saving_per_month, 2) }}</strong></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="table-note">* Suggestions surface only when implied reduction is ≥ 5%. Actual quantities depend on supplier minimums and buffer stock.</p>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 4 · RECOMMENDATIONS
══════════════════════════════════════════════════════════════ --}}
<div id="section-recommend" class="dash-section">

    <div class="section-header">
        <div class="sh-icon">🚀</div>
        <div>
            <div class="sh-title">Recommendation Engine</div>
            <div class="sh-desc">Prioritized actions to reduce waste and operational cost</div>
        </div>
    </div>

    @if(!empty($recommendations))
    <div class="rec2-grid">
        @foreach($recommendations as $rec)
        <div class="rec2-card rec2-{{ $rec['priority'] }}">
            <div class="rec2-top">
                <span class="rec2-icon">{{ $rec['icon'] }}</span>
                <span class="rec2-badge rec2-badge-{{ $rec['priority'] }}">{{ strtoupper($rec['priority']) }}</span>
            </div>
            <div class="rec2-action">{{ $rec['action'] }}</div>
            <div class="rec2-reason">{{ $rec['reason'] }}</div>
            <div class="rec2-saving">Est. saving <strong>₱{{ number_format($rec['estimated_saving'], 2) }}</strong></div>
        </div>
        @endforeach
    </div>
    @else
    <div class="empty-state">
        <div class="es-icon">✅</div>
        <div class="es-title">No Actions Required</div>
        <div class="es-desc">Your inventory is well-managed. Check back as more data accumulates.</div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 5 · ITEM INTELLIGENCE
══════════════════════════════════════════════════════════════ --}}
<div id="section-intelligence" class="dash-section">

    <div class="section-header">
        <div class="sh-icon">🧠</div>
        <div>
            <div class="sh-title">Item Performance Intelligence</div>
            <div class="sh-desc">Profitability score and classification per item</div>
        </div>
    </div>

    @if($itemIntelligence->isNotEmpty())

    {{-- Metric cards for top performers + worst offenders --}}
    @php
        $topPerformers = $itemIntelligence->sortByDesc('profitability_score')->take(3);
        $worstOffenders = $itemIntelligence->sortByDesc('waste_rating')->take(3);
    @endphp

    <div class="grid-2col" style="margin-bottom:16px;">
        <div class="analytics-card">
            <div class="ac-header">
                <span class="ac-title">🏆 Top Performers</span>
                <span class="ac-sub">Highest profitability score</span>
            </div>
            @foreach($topPerformers as $item)
            <div class="perf-row">
                <div class="perf-name"><strong>{{ $item->name }}</strong><span class="perf-cat">{{ $item->category ?: '—' }}</span></div>
                <div class="perf-bar-wrap">
                    <div class="perf-bar-track"><div class="perf-bar-fill" style="width:{{ $item->profitability_score }}%;"></div></div>
                    <span class="perf-pct">{{ $item->profitability_score }}%</span>
                </div>
                <span class="cls-badge cls-{{ $item->class_color }}">{{ $item->class_icon }} {{ $item->class_label }}</span>
            </div>
            @endforeach
        </div>
        <div class="analytics-card">
            <div class="ac-header">
                <span class="ac-title">⚠️ Highest Waste Items</span>
                <span class="ac-sub">Prioritize these for immediate action</span>
            </div>
            @foreach($worstOffenders as $item)
            <div class="perf-row">
                <div class="perf-name"><strong>{{ $item->name }}</strong><span class="perf-cat">{{ $item->category ?: '—' }}</span></div>
                <div class="perf-bar-wrap">
                    <div class="perf-bar-track"><div class="perf-bar-fill perf-bar-waste" style="width:{{ $item->waste_rating }}%;"></div></div>
                    <span class="perf-pct">{{ number_format($item->waste_rating, 1) }}%</span>
                </div>
                <span class="status-badge {{ $item->waste_rating >= 50 ? 'sb-red' : ($item->waste_rating >= 25 ? 'sb-amber' : 'sb-green') }}">
                    {{ number_format($item->waste_rating, 1) }}%
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Full table (collapsible) --}}
    <div class="collapsible-section">
        <button class="collapsible-trigger" onclick="toggleSection('item-intel-table')">
            <span>📋 View All Items Intelligence Table</span>
            <span class="collapse-arrow" id="item-intel-table-arrow">▼</span>
        </button>
        <div id="item-intel-table" class="collapsible-body" style="display:none;">
            <div class="table-wrap">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th>Item</th><th>Category</th><th>Used</th><th>Wasted</th>
                            <th>Profitability</th><th>Waste Rate</th><th>Classification</th>
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
                                    <div class="prof-track"><div class="prof-fill" style="width:{{ $item->profitability_score }}%"></div></div>
                                    <span class="prof-pct">{{ $item->profitability_score }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge {{ $item->waste_rating >= 50 ? 'sb-red' : ($item->waste_rating >= 25 ? 'sb-amber' : 'sb-green') }}">
                                    {{ number_format($item->waste_rating, 1) }}%
                                </span>
                            </td>
                            <td>
                                <span class="cls-badge cls-{{ $item->class_color }}">{{ $item->class_icon }} {{ $item->class_label }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @else
    <div class="empty-state">
        <div class="es-icon">🧠</div>
        <div class="es-title">No Item Data Yet</div>
        <div class="es-desc">Start logging inventory usage and waste to see per-item intelligence here.</div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════
     SECTION 6 · DEEP ANALYTICS  (Category + Period + Full Comparison)
══════════════════════════════════════════════════════════════ --}}
<div id="section-deep" class="dash-section">

    <div class="section-header">
        <div class="sh-icon">📂</div>
        <div>
            <div class="sh-title">Deep Analytics</div>
            <div class="sh-desc">Category breakdown, period timeline, and full item comparison</div>
        </div>
    </div>

    {{-- Category Breakdown (collapsible) --}}
    @if($categoryBreakdown->isNotEmpty())
    <div class="collapsible-section">
        <button class="collapsible-trigger" onclick="toggleSection('cat-breakdown')">
            <span>📂 Category Breakdown</span>
            <span class="collapse-arrow" id="cat-breakdown-arrow">▼</span>
        </button>
        <div id="cat-breakdown" class="collapsible-body" style="display:none;">
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
                                <span class="status-badge {{ $cat['waste_rate'] >= 50 ? 'sb-red' : ($cat['waste_rate'] >= 25 ? 'sb-amber' : 'sb-green') }}">
                                    {{ $cat['waste_rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Period Timeline (collapsible) --}}
    <div class="collapsible-section">
        <button class="collapsible-trigger" onclick="toggleSection('period-timeline')">
            <span>📅 {{ ucfirst($timePeriod) }} Timeline</span>
            <span class="collapse-arrow" id="period-timeline-arrow">▼</span>
        </button>
        <div id="period-timeline" class="collapsible-body" style="display:none;">
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
                                <span class="status-badge {{ $row['waste_rate'] >= 50 ? 'sb-red' : ($row['waste_rate'] >= 25 ? 'sb-amber' : 'sb-green') }}">
                                    {{ $row['waste_rate'] }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state-inline"><span>📅</span> No period data available.</div>
            @endif
        </div>
    </div>

    {{-- Full Item Comparison (collapsible) --}}
    <div class="collapsible-section">
        <button class="collapsible-trigger" onclick="toggleSection('full-comparison')">
            <span>📋 Full Item Comparison</span>
            <span class="collapse-arrow" id="full-comparison-arrow">▼</span>
        </button>
        <div id="full-comparison" class="collapsible-body" style="display:none;">
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
                                <span class="status-badge {{ $item->waste_rating >= 50 ? 'sb-red' : ($item->waste_rating >= 25 ? 'sb-amber' : 'sb-green') }}">
                                    {{ number_format($item->waste_rating, 1) }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state-inline"><span>📋</span> No comparison data available.</div>
            @endif
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════
     DESIGN SYSTEM CSS
═══════════════════════════════════════════════════════════════ --}}
<style>
/* ── Fonts ── */
@import url('https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&family=Geist+Mono:wght@400;500&display=swap');

*, *::before, *::after { box-sizing: border-box; }

.analytics-wrap {
    font-family: 'Geist', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    font-size: 14px;
    color: #0f172a;
    background: #f8fafc;
}

/* ───────────────────────────────────────────────
   EXECUTIVE HERO
─────────────────────────────────────────────── */
.exec-hero {
    position: relative;
    background: #0a0f1e;
    border-radius: 16px;
    overflow: hidden;
    margin-top: 24px;
    margin-bottom: 24px;
    padding: 0;
}

.exec-hero-bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 65% 85% at 85% 15%, rgba(34,197,94,.18) 0%, transparent 60%),
        radial-gradient(ellipse 45% 65% at 10% 85%, rgba(16,185,129,.12) 0%, transparent 55%),
        radial-gradient(ellipse 35% 45% at 50% 50%, rgba(134,239,172,.10) 0%, transparent 60%),
        linear-gradient(135deg, #052e16 0%, #064e3b 45%, #065f46 100%);
    pointer-events: none;
}

.exec-hero-inner {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 40px;
    padding: 40px 40px 28px;
    flex-wrap: wrap;
}

/* Identity */
.hero-identity { flex: 1; min-width: 240px; }

.hero-eyebrow {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
}
.hero-status-dot {
    width: 8px; height: 8px; border-radius: 50%;
    animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
    0%,100% { opacity:1; transform:scale(1); }
    50%      { opacity:.6; transform:scale(1.3); }
}
.hero-status-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
}
.hero-status-green { color: #4ade80; }
.hero-status-amber { color: #fbbf24; }
.hero-status-red   { color: #f87171; }

.hero-title {
    font-size: 42px;
    font-weight: 900;
    color: #fff;
    line-height: 1.05;
    letter-spacing: -.02em;
    margin: 0 0 6px;
}
.hero-subtitle {
    font-size: 14px;
    color: rgba(255,255,255,.45);
    margin: 0 0 20px;
    font-weight: 400;
}

.hero-ai-insight {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px;
    padding: 12px 16px;
    max-width: 480px;
}
.hai-icon {
    font-size: 14px;
    color: #818cf8;
    flex-shrink: 0;
    margin-top: 1px;
}
.hai-text {
    font-size: 13px;
    color: rgba(255,255,255,.75);
    line-height: 1.5;
}

/* Hero KPIs */
.hero-kpis {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.hero-kpi-ring { display: flex; flex-direction: column; align-items: center; gap: 8px; }
.hkr-outer {
    width: 100px; height: 100px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.hkr-inner {
    width: 76px; height: 76px; background: #065f46; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.hkr-num { font-size: 24px; font-weight: 900; color: #fff; line-height: 1; }
.hkr-sub { font-size: 9px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .06em; }
.hkr-label { font-size: 11px; font-weight: 600; color: rgba(255,255,255,.55); }

.hero-kpi-stack { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.hks-card {
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 3px;
    min-width: 130px;
    border: 1px solid rgba(255,255,255,.08);
}
.hks-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; }
.hks-val   { font-size: 20px; font-weight: 800; line-height: 1.1; }
.hks-sub   { font-size: 10px; opacity: .6; }
.hks-red   { background: rgba(239,68,68,.12); }
.hks-red   .hks-label { color: #fca5a5; }
.hks-red   .hks-val   { color: #f87171; }
.hks-amber { background: rgba(251,191,36,.1); }
.hks-amber .hks-label { color: #fde68a; }
.hks-amber .hks-val   { color: #fbbf24; }
.hks-blue  { background: rgba(96,165,250,.1); }
.hks-blue  .hks-label { color: #bfdbfe; }
.hks-blue  .hks-val   { color: #60a5fa; }
.hks-green { background: rgba(34,197,94,.1); }
.hks-green .hks-label { color: #bbf7d0; }
.hks-green .hks-val   { color: #4ade80; }

.hks-bar-track { height: 3px; background: rgba(255,255,255,.1); border-radius: 99px; margin-top: 6px; overflow:hidden; }
.hks-bar-fill  { height: 100%; background: #4ade80; border-radius: 99px; }

/* Hero filter strip */
.hero-filter-strip {
    position: relative;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 12px 40px;
    border-top: 1px solid rgba(255,255,255,.07);
    flex-wrap: wrap;
}
.hfs-label { font-size: 11px; color: rgba(255,255,255,.3); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; margin-right: 2px; }
.hfs-sep   { width: 1px; height: 16px; background: rgba(255,255,255,.1); margin: 0 6px; }
.hfs-btn {
    font-size: 12px; font-weight: 500; color: rgba(255,255,255,.5);
    background: transparent; border: 1px solid rgba(255,255,255,.1);
    border-radius: 6px; padding: 4px 12px; cursor: pointer;
    transition: all .15s;
}
.hfs-btn:hover { color: #fff; border-color: rgba(255,255,255,.3); }
.hfs-btn-active { color: #fff !important; background: rgba(255,255,255,.12) !important; border-color: rgba(255,255,255,.25) !important; }

/* ───────────────────────────────────────────────
   STICKY NAV
─────────────────────────────────────────────── */
.analytics-nav {
    position: sticky;
    top: var(--header-height);
    z-index: 100;
    background: rgba(248,250,252,.95);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e2e8f0;
    margin: 0 -4px;
    padding: 0 4px;
    border-radius: 0;
}
.an-inner {
    display: flex;
    gap: 0;
    overflow-x: auto;
    scrollbar-width: none;
}
.an-inner::-webkit-scrollbar { display: none; }
.an-tab {
    display: inline-flex;
    align-items: center;
    padding: 16px 20px;
    font-size: 15px;
    font-weight: 500;
    color: #64748b;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    transition: color .15s, border-color .15s;
}
.an-tab:hover { color: #0f172a; }
.an-tab-active { color: #0f172a !important; border-bottom-color: #0f172a !important; font-weight: 600; }

/* ───────────────────────────────────────────────
   SECTIONS
─────────────────────────────────────────────── */
.dash-section {
    padding: 32px 0;
    border-bottom: 1px solid #e2e8f0;
}
.dash-section:last-child { border-bottom: none; }

.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
}
.sh-icon {
    width: 40px; height: 40px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.sh-title { font-size: 18px; font-weight: 700; color: #0f172a; letter-spacing: -.01em; }
.sh-desc  { font-size: 13px; color: #94a3b8; margin-top: 2px; }

/* ───────────────────────────────────────────────
   GRID SYSTEM
─────────────────────────────────────────────── */
.grid-2col { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

/* ───────────────────────────────────────────────
   CARD COMPONENTS
─────────────────────────────────────────────── */
.analytics-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px;
    transition: box-shadow .2s;
}
.analytics-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }

.ac-header { margin-bottom: 18px; }
.ac-title  { display: block; font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
.ac-sub    { display: block; font-size: 12px; color: #94a3b8; }

.forecast-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px;
}
.risk-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px;
}

/* ───────────────────────────────────────────────
   DECISION CARDS
─────────────────────────────────────────────── */
.decision-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}
.dc2 {
    border-radius: 12px;
    padding: 18px;
    border: 1px solid transparent;
    transition: transform .15s, box-shadow .15s;
}
.dc2:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(0,0,0,.07); }
.dc2-header { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.dc2-icon   { font-size: 16px; }
.dc2-badge  {
    font-size: 9px; font-weight: 800;
    text-transform: uppercase; letter-spacing: .08em;
    padding: 2px 8px; border-radius: 99px;
}
.dc2-title  { font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.4; margin-bottom: 6px; }
.dc2-detail { font-size: 12px; color: #64748b; line-height: 1.5; margin-bottom: 10px; }
.dc2-action { font-size: 12px; font-weight: 600; }

.dc2-critical    { background:#fff5f5; border-color:#fecaca; }
.dc2-critical    .dc2-badge, .dc2-critical    .dc2-action { color:#dc2626; }
.dc2-critical    .dc2-badge { background:#fecaca; }
.dc2-warning     { background:#fffbeb; border-color:#fde68a; }
.dc2-warning     .dc2-badge, .dc2-warning     .dc2-action { color:#d97706; }
.dc2-warning     .dc2-badge { background:#fde68a; }
.dc2-improvement { background:#f0fdf4; border-color:#bbf7d0; }
.dc2-improvement .dc2-badge, .dc2-improvement .dc2-action { color:#16a34a; }
.dc2-improvement .dc2-badge { background:#bbf7d0; }
.dc2-success     { background:#f0fdf4; border-color:#bbf7d0; }
.dc2-success     .dc2-badge, .dc2-success     .dc2-action { color:#16a34a; }
.dc2-success     .dc2-badge { background:#bbf7d0; }
.dc2-info        { background:#eff6ff; border-color:#bfdbfe; }
.dc2-info        .dc2-badge, .dc2-info        .dc2-action { color:#1d4ed8; }
.dc2-info        .dc2-badge { background:#bfdbfe; }
.dc2-danger      { background:#fff5f5; border-color:#fecaca; }
.dc2-danger      .dc2-badge, .dc2-danger      .dc2-action { color:#dc2626; }
.dc2-danger      .dc2-badge { background:#fecaca; }

/* ───────────────────────────────────────────────
   IMPACT BANNER
─────────────────────────────────────────────── */
.impact2-banner {
    background: linear-gradient(135deg, #0f2d1c 0%, #14532d 100%);
    border-radius: 14px;
    padding: 24px 28px;
    margin-top: 20px;
}
.impact2-eyebrow { font-size: 12px; font-weight: 600; color: rgba(255,255,255,.55); margin-bottom: 16px; }
.impact2-flow    { display: flex; align-items: center; gap: 24px; flex-wrap: wrap; margin-bottom: 12px; }
.impact2-block   { display: flex; flex-direction: column; gap: 3px; }
.impact2-highlight { margin-left: auto; }
.impact2-lbl     { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.4); }
.impact2-val     { font-size: 28px; font-weight: 900; line-height: 1.1; }
.impact2-red     { color: #fca5a5; }
.impact2-green   { color: #6ee7b7; }
.impact2-pct     { font-size: 12px; color: rgba(255,255,255,.5); }
.impact2-arrow   { font-size: 20px; color: rgba(255,255,255,.3); }
.impact2-sep     { width: 1px; height: 48px; background: rgba(255,255,255,.15); }
.impact2-note    { font-size: 11px; color: rgba(255,255,255,.3); }

/* ───────────────────────────────────────────────
   ROOT CAUSE
─────────────────────────────────────────────── */
.rc2-list { display: flex; flex-direction: column; gap: 10px; }
.rc2-item {
    padding: 14px;
    border-radius: 10px;
    border-left: 3px solid transparent;
    transition: opacity .2s;
}
.rc2-dim { opacity: .5; }
.rc2-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 8px; }
.rc2-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
.rc2-body { flex: 1; }
.rc2-title { font-size: 13px; font-weight: 700; color: #0f172a; }
.rc2-count { font-size: 11px; color: #94a3b8; margin-top: 2px; }
.rc2-tags  { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px; }
.rc2-badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; flex-shrink: 0; }
.rc2-action { font-size: 12px; font-weight: 600; color: #16a34a; padding-left: 26px; }

.rc2-danger  { background: #fff5f5; border-left-color: #dc2626; }
.rc2-warning { background: #fffbeb; border-left-color: #d97706; }
.rc2-success { background: #f0fdf4; border-left-color: #16a34a; }
.rc2-badge-danger  { background: #fecaca; color: #dc2626; }
.rc2-badge-warning { background: #fde68a; color: #92400e; }
.rc2-badge-success { background: #bbf7d0; color: #166534; }

/* ───────────────────────────────────────────────
   INSIGHTS
─────────────────────────────────────────────── */
.ins2-list { display: flex; flex-direction: column; gap: 10px; }
.ins2-item {
    display: flex; gap: 12px;
    padding: 14px; border-radius: 10px;
    border-left: 3px solid transparent;
}
.ins2-icon  { font-size: 18px; flex-shrink: 0; }
.ins2-title { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
.ins2-msg   { font-size: 12px; color: #64748b; line-height: 1.5; }
.ins2-success { background: #f0fdf4; border-left-color: #16a34a; }
.ins2-warning { background: #fffbeb; border-left-color: #d97706; }
.ins2-danger  { background: #fff5f5; border-left-color: #dc2626; }
.ins2-info    { background: #eff6ff; border-left-color: #1d4ed8; }

/* ───────────────────────────────────────────────
   BAR CHARTS
─────────────────────────────────────────────── */
.bar2-chart { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
.bar2-row   { display: grid; grid-template-columns: 120px 1fr 64px; gap: 10px; align-items: center; }
.bar2-label { font-size: 12px; font-weight: 500; color: #374151; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
.bar2-track { height: 8px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.bar2-fill  { height: 100%; border-radius: 99px; width: 0; transition: width .6s cubic-bezier(.4,0,.2,1); }
.bar2-waste { background: linear-gradient(90deg, #dc2626, #ef4444); }
.bar2-used  { background: linear-gradient(90deg, #16a34a, #22c55e); }
.bar2-val   { font-size: 12px; font-weight: 700; text-align: right; color: #0f172a; }

/* ───────────────────────────────────────────────
   FORECAST CARD
─────────────────────────────────────────────── */
.fc-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
.fc-title { font-size: 14px; font-weight: 700; color: #0f172a; }
.fc-sub   { font-size: 12px; color: #94a3b8; margin-top: 2px; }

.conf-chip {
    font-size: 10px; font-weight: 700;
    padding: 3px 10px; border-radius: 99px;
    white-space: nowrap; flex-shrink: 0;
}

.fc-metric-row { display: flex; align-items: center; gap: 16px; margin-bottom: 14px; flex-wrap: wrap; }
.fc-metric     { display: flex; flex-direction: column; gap: 3px; }
.fc-metric-main { background: #f8fafc; border-radius: 10px; padding: 12px 16px; }
.fc-metric-lbl  { font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
.fc-metric-val  { font-size: 22px; font-weight: 800; color: #0f172a; }
.fc-metric-big  { font-size: 28px; color: #dc2626; }
.fc-metric-arrow { font-size: 16px; color: #cbd5e1; }
.fc-trend  { font-size: 13px; font-weight: 600; padding: 8px 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 8px; }
.fc-helper { font-size: 12px; color: #94a3b8; line-height: 1.5; font-style: italic; }

/* ───────────────────────────────────────────────
   DAY-OF-WEEK CHART
─────────────────────────────────────────────── */
.dow2-chart {
    display: flex;
    align-items: flex-end;
    gap: 6px;
    height: 110px;
    margin: 16px 0 6px;
}
.dow2-col { display: flex; flex-direction: column; align-items: center; flex: 1; height: 100%; }
.dow2-bar-wrap { flex: 1; width: 100%; display: flex; align-items: flex-end; }
.dow2-bar { width: 100%; border-radius: 5px 5px 0 0; min-height: 4px; transition: height .4s ease; }
.dow2-val { font-size: 9px; color: #94a3b8; margin-top: 4px; font-family: 'Geist Mono', monospace; }
.dow2-day { font-size: 10px; font-weight: 700; color: #64748b; margin-top: 2px; }
.dow2-peak { color: #dc2626 !important; }
.dow2-legend { display: flex; align-items: center; font-size: 11px; color: #94a3b8; gap: 4px; margin-top: 6px; }
.leg-dot { display: inline-block; width: 7px; height: 7px; border-radius: 50%; }

/* ───────────────────────────────────────────────
   RISK SCORES
─────────────────────────────────────────────── */
.risk2-list { display: flex; flex-direction: column; }
.risk2-row {
    display: grid;
    grid-template-columns: 1fr auto auto auto;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.risk2-row:last-child { border-bottom: none; }
.risk2-name  { display: flex; align-items: center; gap: 6px; font-size: 13px; overflow: hidden; }
.risk2-name strong { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.risk2-rates { display: flex; align-items: center; gap: 4px; font-size: 11px; white-space: nowrap; }
.risk2-lbl   { color: #94a3b8; }
.risk2-num   { font-weight: 700; color: #374151; }
.risk2-arrow { color: #e2e8f0; }
.risk2-delta { font-size: 12px; font-weight: 700; white-space: nowrap; }
.risk-badge  { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 99px; white-space: nowrap; }
.risk-danger  { background: #fecaca; color: #dc2626; }
.risk-warning { background: #fde68a; color: #92400e; }
.risk-success { background: #dcfce7; color: #166534; }
.risk-neutral { background: #f1f5f9; color: #64748b; }

/* ───────────────────────────────────────────────
   FORECAST LIST
─────────────────────────────────────────────── */
.forecast2-list { display: flex; flex-direction: column; }
.forecast2-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.forecast2-row:last-child { border-bottom: none; }
.forecast2-name { font-size: 13px; overflow: hidden; }
.forecast2-name strong { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.forecast2-meta { display: flex; flex-direction: column; align-items: flex-end; gap: 1px; }
.forecast2-sub   { font-size: 10px; color: #94a3b8; }
.forecast2-units { font-size: 11px; color: #64748b; }
.forecast2-val { font-size: 14px; font-weight: 700; font-family: 'Geist Mono', monospace; }
.fv-red   { color: #dc2626; }
.fv-amber { color: #d97706; }
.fv-green { color: #16a34a; }

/* ───────────────────────────────────────────────
   RECOMMENDATIONS
─────────────────────────────────────────────── */
.rec2-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.rec2-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform .15s, box-shadow .15s;
}
.rec2-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,.08); }
.rec2-top    { display: flex; align-items: center; gap: 8px; padding: 16px 16px 0; }
.rec2-icon   { font-size: 18px; }
.rec2-badge  { font-size: 9px; font-weight: 800; padding: 2px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: .07em; }
.rec2-badge-high   { background: #fecaca; color: #dc2626; }
.rec2-badge-medium { background: #fde68a; color: #92400e; }
.rec2-badge-low    { background: #bbf7d0; color: #166534; }
.rec2-action { font-size: 13px; font-weight: 700; color: #0f172a; line-height: 1.4; padding: 10px 16px 6px; }
.rec2-reason { font-size: 12px; color: #64748b; line-height: 1.5; padding: 0 16px 10px; flex: 1; }
.rec2-saving { font-size: 12px; color: #16a34a; font-weight: 600; padding: 10px 16px; border-top: 1px solid #f1f5f9; margin-top: auto; }
.rec2-high   { border-left: 3px solid #dc2626; }
.rec2-medium { border-left: 3px solid #d97706; }
.rec2-low    { border-left: 3px solid #16a34a; }

/* ───────────────────────────────────────────────
   ITEM INTELLIGENCE
─────────────────────────────────────────────── */
.perf-row {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.perf-row:last-child { border-bottom: none; }
.perf-name { font-size: 13px; overflow: hidden; }
.perf-name strong { display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.perf-cat  { font-size: 11px; color: #94a3b8; }
.perf-bar-wrap  { display: flex; align-items: center; gap: 8px; }
.perf-bar-track { flex: 1; height: 5px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.perf-bar-fill  { height: 100%; background: #16a34a; border-radius: 99px; }
.perf-bar-waste { background: #ef4444; }
.perf-pct  { font-size: 11px; font-weight: 700; color: #374151; white-space: nowrap; }

/* ───────────────────────────────────────────────
   COLLAPSIBLE SECTIONS
─────────────────────────────────────────────── */
.collapsible-section {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 12px;
}
.collapsible-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #fff;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    text-align: left;
    transition: background .15s;
    font-family: inherit;
}
.collapsible-trigger:hover { background: #f8fafc; }
.collapse-arrow { font-size: 11px; color: #94a3b8; transition: transform .2s; }
.collapsible-body { padding: 0 20px 20px; background: #fff; }

/* ───────────────────────────────────────────────
   STATUS & UTILITY BADGES
─────────────────────────────────────────────── */
.status-badge {
    display: inline-block;
    font-size: 11px; font-weight: 700;
    padding: 3px 9px; border-radius: 99px;
}
.sb-red   { background: #fecaca; color: #dc2626; }
.sb-amber { background: #fde68a; color: #92400e; }
.sb-green { background: #dcfce7; color: #166534; }

.tag     { font-size: 11px; padding: 2px 8px; border-radius: 99px; background: rgba(0,0,0,.05); color: #374151; }
.tag-dim { color: #94a3b8; }

.cls-badge  { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:700; padding:3px 9px; border-radius:99px; white-space:nowrap; }
.cls-success { background:#dcfce7; color:#16a34a; }
.cls-danger  { background:#fecaca; color:#dc2626; }
.cls-warning { background:#fde68a; color:#92400e; }
.cls-neutral { background:#f1f5f9; color:#64748b; }

/* ───────────────────────────────────────────────
   EMPTY STATES
─────────────────────────────────────────────── */
.empty-state {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
    padding: 48px 24px;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px dashed #e2e8f0;
}
.es-icon  { font-size: 32px; margin-bottom: 12px; }
.es-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
.es-desc  { font-size: 13px; color: #94a3b8; max-width: 300px; line-height: 1.5; }

.empty-state-inline {
    display: flex; align-items: center; gap: 8px;
    padding: 16px; border-radius: 10px;
    background: #f8fafc; border: 1px dashed #e2e8f0;
    font-size: 13px; color: #94a3b8;
}

/* ───────────────────────────────────────────────
   TABLES
─────────────────────────────────────────────── */
.table-wrap { overflow-x: auto; margin-top: 12px; }
.a-table { width: 100%; border-collapse: collapse; }
.a-table th,
.a-table td { padding: 11px 12px; text-align: left; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
.a-table th  { background: #f8fafc; color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.a-table tr:last-child td { border-bottom: none; }
.a-table tr:hover td { background: #fafafa; }

.prof-wrap  { display: flex; align-items: center; gap: 8px; }
.prof-track { width: 56px; height: 5px; background: #f0f0f0; border-radius: 3px; overflow: hidden; }
.prof-fill  { height: 100%; background: #16a34a; border-radius: 3px; }
.prof-pct   { font-size: 12px; color: #374151; }

.table-note { font-size: 11px; color: #94a3b8; margin-top: 12px; }

/* ───────────────────────────────────────────────
   COLOR UTILITIES
─────────────────────────────────────────────── */
.c-green { color: #16a34a; }
.c-amber { color: #d97706; }
.c-red   { color: #dc2626; }

/* ───────────────────────────────────────────────
   RESPONSIVE
─────────────────────────────────────────────── */
@media (max-width: 900px) {
    .grid-2col { grid-template-columns: 1fr; }
    .hero-kpis { width: 100%; }
    .hero-kpi-stack { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
    .exec-hero-inner { padding: 24px 20px 16px; flex-direction: column; gap: 24px; }
    .hero-filter-strip { padding: 10px 20px; }
    .hero-title { font-size: 32px; }
    .hero-kpi-stack { grid-template-columns: 1fr 1fr; }
    .hero-kpi-ring .hkr-outer { width: 80px; height: 80px; }
    .hero-kpi-ring .hkr-inner { width: 60px; height: 60px; }
    .hkr-num { font-size: 20px; }
    .impact2-flow { flex-direction: column; gap: 12px; }
    .impact2-highlight { margin-left: 0; }
    .impact2-sep { display: none; }
    .bar2-row { grid-template-columns: 90px 1fr 56px; }
    .risk2-row { grid-template-columns: 1fr auto; }
    .risk2-rates { display: none; }
    .forecast2-row { grid-template-columns: 1fr auto; }
    .forecast2-meta { display: none; }
    .perf-row { grid-template-columns: 1fr auto; }
    .perf-bar-wrap { display: none; }
    .rec2-grid { grid-template-columns: 1fr; }
    .decision-grid { grid-template-columns: 1fr; }
    .an-tab { padding: 12px 14px; font-size: 12px; }
}
</style>

<script>
/* ── Filter form helpers ── */
function setFilter(key, val) {
    document.getElementById('ff-' + key.replace('_', '-')).value = val;
    document.getElementById('analytics-filter-form').submit();
}

/* ── Bar chart animations ── */
document.addEventListener('DOMContentLoaded', () => {
    const bars = document.querySelectorAll('.bar2-fill[data-w]');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.width = parseFloat(e.target.dataset.w || 0) + '%';
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });
    bars.forEach(b => { b.style.width = '0%'; observer.observe(b); });
});

/* ── Sticky nav active state on scroll ── */
document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('.dash-section[id]');
    const tabs     = document.querySelectorAll('.an-tab[data-section]');

    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                tabs.forEach(t => t.classList.remove('an-tab-active'));
                const match = document.querySelector(`.an-tab[data-section="${e.target.id}"]`);
                if (match) match.classList.add('an-tab-active');
            }
        });
    }, { rootMargin: '-30% 0px -60% 0px' });

    sections.forEach(s => io.observe(s));
});

/* ── Collapsible sections ── */
function toggleSection(id) {
    const body  = document.getElementById(id);
    const arrow = document.getElementById(id + '-arrow');
    const open  = body.style.display !== 'none';
    body.style.display  = open ? 'none' : 'block';
    if (arrow) arrow.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
}

/* ── Smooth scroll for nav tabs ── */
document.querySelectorAll('.an-tab[href^="#"]').forEach(tab => {
    tab.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(tab.getAttribute('href'));
        if (target) {
            const offset = document.getElementById('analytics-nav').offsetHeight + 16;
            const top    = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        }
    });
});
</script>

@endsection