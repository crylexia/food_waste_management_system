@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">Quick Stats</div>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-label">Total Items</div>
                <div class="stat-value">{{ $totalItems }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Entries</div>
                <div class="stat-value">{{ $totalEntries }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Avg Waste Rate</div>
                <div class="stat-value">{{ number_format($avgWasteRate, 1) }}%</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Recent Entries</div>
        @if($recentEntries->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Waste Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentEntries as $entry)
                        <tr>
                            <td>{{ $entry->date->format('Y-m-d') }}</td>
                            <td>{{ $entry->entryItems->count() }} items</td>
                            <td>{{ number_format($entry->waste_rating, 1) }}%</td>
                            <td>
                                <a href="{{ route('entries.show', $entry) }}" class="btn btn-sm btn-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center" style="padding: 20px; color: #757575;">No entries yet. <a href="{{ route('entries.create') }}">Create your first entry</a></p>
        @endif
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    gap: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #F5F5F5;
    border-radius: 4px;
}

.stat-label {
    font-size: 14px;
    color: #757575;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 32px;
    font-weight: 600;
    color: #2D7A3E;
}
</style>
@endsection
