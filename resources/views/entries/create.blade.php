@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create Daily Entry</h1>
</div>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('entries.store') }}">
        @csrf

        <div class="form-group">
            <label for="date">Date *</label>
            <input 
                type="date" 
                id="date" 
                name="date" 
                class="form-control @error('date') error @enderror" 
                value="{{ old('date', date('Y-m-d')) }}" 
                required
            >
            @error('date')
                <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
            @enderror
            <small style="color: #757575;">You can only create one entry per date.</small>
        </div>

        <div class="flex gap-10">
            <button type="submit" class="btn btn-primary">Create Entry</button>
            <a href="{{ route('entries.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<div class="card" style="max-width: 600px; margin-top: 20px; background: #E8F5E9; border-left: 4px solid #2D7A3E;">
    <p style="margin: 0; color: #1B5E20;">
        <strong>Note:</strong> After creating the entry, you'll be able to add items with their quantities.
    </p>
</div>
@endsection
