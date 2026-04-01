@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Daily Entries</h1>
    <a href="{{ route('entries.create') }}" class="btn btn-primary">+ New Entry</a>
</div>

<div class="card">
    @if($entries->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Items Count</th>
                    <th>Waste Rate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td>{{ $entry->date->format('Y-m-d') }}</td>
                        <td>{{ $entry->entryItems->count() }} items</td>
                        <td>{{ number_format($entry->waste_rating, 2) }}%</td>
                        <td>
                            <a href="{{ route('entries.show', $entry) }}" class="btn btn-sm btn-secondary">View</a>
                            <form action="{{ route('entries.destroy', $entry) }}" method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Are you sure you want to delete this entry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper">
            {{ $entries->links() }}
        </div>
    @else
        <p class="text-center" style="padding: 40px; color: #757575;">
            No entries yet. <a href="{{ route('entries.create') }}">Create your first entry</a>
        </p>
    @endif
</div>

<style>
.pagination-wrapper {
    padding: 20px;
    text-align: center;
}
</style>
@endsection
