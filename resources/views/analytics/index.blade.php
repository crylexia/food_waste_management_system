@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Analytics</h1>
</div>

<div class="card">
    <form method="GET" action="{{ route('analytics.index') }}" class="filter-form">
        <div class="filter-row">
            <div class="form-group">
                <label for="time_period">Time Period</label>
                <select name="time_period" id="time_period" class="form-control" onchange="this.form.submit()">
                    <option value="daily" {{ $timePeriod == 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ $timePeriod == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ $timePeriod == 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>

            <div class="form-group">
                <label for="date_range">Date Range</label>
                <select name="date_range" id="date_range" class="form-control" onchange="this.form.submit()">
                    <option value="">All Time</option>
                    <option value="{{ now()->subDays(7)->format('Y-m-d') }}" {{ $dateRange == now()->subDays(7)->format('Y-m-d') ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="{{ now()->subDays(30)->format('Y-m-d') }}" {{ $dateRange == now()->subDays(30)->format('Y-m-d') ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="{{ now()->subDays(90)->format('Y-m-d') }}" {{ $dateRange == now()->subDays(90)->format('Y-m-d') ? 'selected' : '' }}>Last 90 Days</option>
                </select>
            </div>
        </div>
    </form>
</div>

@php
    $totalUsed = $usageComparison->sum('total_used');
    $totalWasted = $usageComparison->sum('total_wasted');
    $totalUsedValue = $usageComparison->sum('used_value');
    $totalWastedValue = $usageComparison->sum('wasted_value');
    $overallTotal = $totalUsed + $totalWasted;
    $wasteRate = $overallTotal > 0 ? ($totalWasted / $overallTotal) * 100 : 0;
@endphp

<div class="summary-grid" style="margin-top: 20px;">
    <div class="analytics-card summary-card">
        <h4>Total Used</h4>
        <p class="summary-value">{{ number_format($totalUsed, 2) }}</p>
    </div>

    <div class="analytics-card summary-card">
        <h4>Total Wasted</h4>
        <p class="summary-value">{{ number_format($totalWasted, 2) }}</p>
    </div>

    <div class="analytics-card summary-card">
        <h4>Total Used Value</h4>
        <p class="summary-value">₱{{ number_format($totalUsedValue, 2) }}</p>
    </div>

    <div class="analytics-card summary-card">
        <h4>Total Waste Cost</h4>
        <p class="summary-value">₱{{ number_format($totalWastedValue, 2) }}</p>
    </div>

    <div class="analytics-card summary-card">
        <h4>Waste Rate</h4>
        <p class="summary-value">{{ number_format($wasteRate, 1) }}%</p>
    </div>

    <div class="analytics-card summary-card">
        <h4>Tracked Items</h4>
        <p class="summary-value">{{ $usageComparison->count() }}</p>
    </div>
</div>

<div class="analytics-card insights-card" style="margin-top: 20px;">
    <h2 class="section-title">Deductive Insights & Recommendations</h2>

    @if(!empty($insights))
        <div class="insights-grid">
            @foreach($insights as $insight)
                <div class="insight-box insight-{{ $insight['type'] }}">
                    <div class="insight-icon">{{ $insight['icon'] }}</div>
                    <div class="insight-content">
                        <h3>{{ $insight['title'] }}</h3>
                        <p>{{ $insight['message'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="empty-state">No insights available yet. Add more entry records to generate recommendations.</p>
    @endif
</div>

<div class="chart-grid" style="margin-top: 20px;">
    <div class="analytics-card">
        <h3 class="section-title">Top Wasted Items</h3>
        @if($mostWasted->count() > 0)
            <div class="simple-bar-chart">
                @php
                    $maxWaste = max($mostWasted->max('total_wasted'), 1);
                @endphp

                @foreach($mostWasted as $item)
                    @php
                        $wasteWidth = $maxWaste > 0 ? ($item->total_wasted / $maxWaste) * 100 : 0;
                    @endphp
                    <div class="bar-row">
                        <div class="bar-label">{{ $item->name }}</div>
                        <div class="bar-track">
                            <div class="bar-fill waste-bar" data-width="{{ $wasteWidth }}"></div>
                        </div>
                        <div class="bar-value">{{ number_format($item->total_wasted, 2) }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="empty-state">No waste data available.</p>
        @endif
    </div>

    <div class="analytics-card">
        <h3 class="section-title">Top Used Items</h3>
        @if($mostUsed->count() > 0)
            <div class="simple-bar-chart">
                @php
                    $maxUsed = max($mostUsed->max('total_used'), 1);
                @endphp

                @foreach($mostUsed as $item)
                    @php
                        $usedWidth = $maxUsed > 0 ? ($item->total_used / $maxUsed) * 100 : 0;
                    @endphp
                    <div class="bar-row">
                        <div class="bar-label">{{ $item->name }}</div>
                        <div class="bar-track">
                            <div class="bar-fill used-bar" data-width="{{ $usedWidth }}"></div>
                        </div>
                        <div class="bar-value">{{ number_format($item->total_used, 2) }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="empty-state">No usage data available.</p>
        @endif
    </div>
</div>

<div class="analytics-card" style="margin-top: 20px;">
    <h3 class="section-title">Usage vs Waste Comparison</h3>

    @if($usageComparison->count() > 0)
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Used</th>
                        <th>Wasted</th>
                        <th>Used Value</th>
                        <th>Waste Cost</th>
                        <th>Waste Rating</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($usageComparison as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category }}</td>
                            <td>{{ number_format($item->total_used, 2) }}</td>
                            <td>{{ number_format($item->total_wasted, 2) }}</td>
                            <td>₱{{ number_format($item->used_value, 2) }}</td>
                            <td>₱{{ number_format($item->wasted_value, 2) }}</td>
                            <td>
                                <span class="waste-badge {{ $item->waste_rating > 50 ? 'high' : ($item->waste_rating > 25 ? 'medium' : 'low') }}">
                                    {{ number_format($item->waste_rating, 1) }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="empty-state">No comparison data available.</p>
    @endif
</div>

<div class="analytics-card" style="margin-top: 20px;">
    <h3 class="section-title">{{ ucfirst($timePeriod) }} Statistics</h3>

    @if($periodStats->count() > 0)
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Item</th>
                        <th>Used</th>
                        <th>Wasted</th>
                        <th>Used Value</th>
                        <th>Waste Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($periodStats as $stat)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($stat->period_date)->format('M d, Y') }}</td>
                            <td>{{ $stat->name }}</td>
                            <td>{{ number_format($stat->total_used, 2) }}</td>
                            <td>{{ number_format($stat->total_wasted, 2) }}</td>
                            <td>₱{{ number_format($stat->used_value, 2) }}</td>
                            <td>₱{{ number_format($stat->wasted_value, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="empty-state">No time period data available.</p>
    @endif
</div>

<style>
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.analytics-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
}

.filter-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    background: #fff;
    font-size: 14px;
}

.summary-card h4 {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
    font-weight: 600;
}

.summary-value {
    margin-top: 10px;
    font-size: 28px;
    font-weight: 700;
    color: #111827;
}

.section-title {
    margin: 0 0 16px 0;
    font-size: 20px;
    font-weight: 700;
    color: #111827;
}

.insights-card {
    margin-bottom: 24px;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 18px;
    margin-top: 18px;
}

.insight-box {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 18px;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.insight-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.06);
}

.insight-icon {
    font-size: 1.8rem;
    line-height: 1;
    margin-top: 2px;
}

.insight-content h3 {
    margin: 0 0 8px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #111827;
}

.insight-content p {
    margin: 0;
    font-size: 0.95rem;
    line-height: 1.6;
    color: #4b5563;
}

.insight-success {
    border-left: 5px solid #10b981;
    background: #ecfdf5;
}

.insight-warning {
    border-left: 5px solid #f59e0b;
    background: #fffbeb;
}

.insight-danger {
    border-left: 5px solid #ef4444;
    background: #fef2f2;
}

.insight-info {
    border-left: 5px solid #3b82f6;
    background: #eff6ff;
}

.chart-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.simple-bar-chart {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.bar-row {
    display: grid;
    grid-template-columns: 120px 1fr 80px;
    gap: 10px;
    align-items: center;
}

.bar-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.bar-track {
    width: 100%;
    height: 12px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    border-radius: 999px;
}

.waste-bar {
    background: linear-gradient(90deg, #ef4444, #f97316);
}

.used-bar {
    background: linear-gradient(90deg, #10b981, #22c55e);
}

.bar-value {
    text-align: right;
    font-size: 13px;
    font-weight: 700;
    color: #111827;
}

.table-responsive {
    overflow-x: auto;
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
}

.analytics-table th,
.analytics-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    font-size: 14px;
}

.analytics-table th {
    background: #f8fafc;
    color: #374151;
    font-weight: 700;
}

.waste-badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.waste-badge.high {
    background: #fee2e2;
    color: #b91c1c;
}

.waste-badge.medium {
    background: #fef3c7;
    color: #92400e;
}

.waste-badge.low {
    background: #dcfce7;
    color: #166534;
}

.empty-state {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

@media (max-width: 1024px) {
    .chart-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }

    .bar-row {
        grid-template-columns: 1fr;
        gap: 6px;
    }

    .bar-value {
        text-align: left;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.bar-fill').forEach(function (bar) {
        const width = parseFloat(bar.dataset.width || 0);
        bar.style.width = width + '%';
    });
});
</script>
@endsection