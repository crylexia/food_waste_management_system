@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">Edit Item</h1>
</div>

<div class="card" style="max-width: 600px;">
    <form method="POST" action="{{ route('items.update', $item) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Item Name *</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                class="form-control @error('name') error @enderror" 
                value="{{ old('name', $item->name) }}" 
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
                value="{{ old('category', $item->category) }}" 
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
                value="{{ old('price', $item->price) }}" 
                step="0.01"
                min="0"
            >
            @error('price')
                <div class="alert alert-error" style="margin-top: 5px; padding: 8px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex gap-10">
            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
