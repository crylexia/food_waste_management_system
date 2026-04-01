@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Create New Item</h1>
</div>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('items.store') }}">
        @csrf

        <div class="form-group">
            <label for="name">Item Name *</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-control @error('name') error @enderror" 
                value="{{ old('name') }}" 
                required
            >
            @error('name')
                <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="category">Category *</label>
            <input 
                type="text" 
                id="category" 
                name="category" 
                class="form-control @error('category') error @enderror" 
                value="{{ old('category') }}" 
                placeholder="e.g., Product, Ingredient"
                required
            >
            @error('category')
                <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="price">Price (optional)</label>
            <input 
                type="number" 
                id="price" 
                name="price" 
                class="form-control @error('price') error @enderror" 
                value="{{ old('price') }}" 
                step="0.01"
                min="0"
                placeholder="0.00"
            >
            @error('price')
                <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex gap-10">
            <button type="submit" class="btn btn-primary">Create Item</button>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
