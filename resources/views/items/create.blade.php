@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Create New Item</h1>
        <p class="page-subtitle">Add a new item to your inventory tracking list</p>
    </div>
    <a href="{{ route('items.index') }}" class="back-link">← Back to Items</a>
</div>

<div class="two-col" style="align-items:flex-start;">

    <div class="a-card">
        <h3 class="section-title">📦 Item Details</h3>
        <p class="section-desc">Fill in the details for the new inventory item</p>

        <form method="POST" action="{{ route('items.store') }}">
            @csrf

            <div class="form-group">
                <label class="field-label" for="name">Item Name *</label>
                <input type="text" id="name" name="name"
                       class="field-input @error('name') field-error @enderror"
                       value="{{ old('name') }}" required
                       placeholder="e.g., Chicken Breast">
                @error('name')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label class="field-label" for="category">Category *</label>
                <input type="text" id="category" name="category"
                       class="field-input @error('category') field-error @enderror"
                       value="{{ old('category') }}" required
                       placeholder="e.g., Meat, Produce, Dairy">
                @error('category')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
                <div class="field-hint">Used to group items in analytics breakdowns.</div>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label class="field-label" for="unit">Default Unit *</label>

                <select id="unit" name="unit"
                        class="field-input @error('unit') field-error @enderror"
                        required>

                    <option value="" disabled {{ old('unit') ? '' : 'selected' }}>
                        Select Unit
                    </option>

                    <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>
                        Kilogram (kg)
                    </option>

                    <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>
                        Gram (g)
                    </option>

                    <option value="L" {{ old('unit') == 'L' ? 'selected' : '' }}>
                        Liter (L)
                    </option>

                    <option value="mL" {{ old('unit') == 'mL' ? 'selected' : '' }}>
                        Milliliter (mL)
                    </option>

                    <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>
                        Pieces (pcs)
                    </option>

                    <option value="packs" {{ old('unit') == 'packs' ? 'selected' : '' }}>
                        Packs
                    </option>

                </select>

                @error('unit')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror

                <div class="field-hint">
                    Select the default measurement unit for this item.
                </div>
            </div>

            <div class="form-group" style="margin-top:16px;">
                <label class="field-label" for="price">Price per Unit (optional)</label>
                <div class="input-prefix-wrap">
                    <span class="input-prefix">₱</span>
                    <input type="number" id="price" name="price"
                           class="field-input field-input-prefixed @error('price') field-error @enderror"
                           value="{{ old('price') }}" step="0.01" min="0"
                           placeholder="0.00">
                </div>
                @error('price')
                    <div class="field-msg-error">{{ $message }}</div>
                @enderror
                <div class="field-hint">Used to calculate monetary waste value in analytics.</div>
            </div>

            <div style="display:flex; gap:10px; margin-top:24px;">
                <button type="submit" class="btn-main">Create Item</button>
                <a href="{{ route('items.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    <div style="display:flex; flex-direction:column; gap:12px;">
        <div class="ins-box ins-success">
            <span class="ins-icon">📊</span>
            <div>
                <div class="ins-title">Why price matters</div>
                <div class="ins-msg">Setting a price per unit allows the analytics engine to calculate revenue loss, cost per waste unit, and financial impact estimations.</div>
            </div>
        </div>
        <div class="ins-box ins-info">
            <span class="ins-icon">🗂️</span>
            <div>
                <div class="ins-title">Use consistent categories</div>
                <div class="ins-msg">Category names are case-sensitive. Use consistent naming like "Meat" or "Produce" so the Category Breakdown in analytics groups items correctly.</div>
            </div>
        </div>
    </div>

</div>

<style>
.page-header   { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.page-subtitle { font-size:13px; color:#888; margin-top:2px; }
.back-link     { font-size:13px; color:#2D7A3E; text-decoration:none; margin-top:4px; }
.back-link:hover { text-decoration:underline; }
.a-card        { background:#fff; border:1px solid #ebebeb; border-radius:12px; padding:24px; }
.two-col       { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.section-title { font-size:15px; font-weight:700; color:#111; margin:0 0 3px; }
.section-desc  { font-size:12px; color:#999; margin:0 0 20px; }

.field-label  { display:block; font-size:11px; font-weight:600; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
.field-input  { width:100%; padding:10px 13px; border:1px solid #ddd; border-radius:8px; font-size:14px; color:#111; box-sizing:border-box; transition:border-color .2s; }
.field-input:focus { outline:none; border-color:#2D7A3E; box-shadow:0 0 0 3px rgba(45,122,62,.08); }
.field-input.field-error { border-color:#C62828; }
.field-msg-error { font-size:11px; color:#C62828; margin-top:4px; }
.field-hint   { font-size:11px; color:#bbb; margin-top:5px; }

.input-prefix-wrap    { position:relative; }
.input-prefix { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #888; pointer-events: none; z-index: 2;}
.field-input-prefixed { padding-left: 38px !important; }

.btn-main  { padding:10px 22px; background:#2D7A3E; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; }
.btn-main:hover { background:#245f31; }
.btn-ghost { padding:10px 22px; background:#fff; color:#555; border:1px solid #ddd; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; transition:border-color .2s; display:inline-block; }
.btn-ghost:hover { border-color:#aaa; }

.ins-box   { display:flex; gap:12px; padding:18px; border-radius:12px; border:1px solid #ebebeb; }
.ins-icon  { font-size:22px; flex-shrink:0; }
.ins-title { font-size:13px; font-weight:700; color:#111; margin-bottom:4px; }
.ins-msg   { font-size:12px; color:#666; line-height:1.6; }
.ins-success { background:#f0fdf4; border-left:4px solid #2D7A3E; }
.ins-info    { background:#eff6ff; border-left:4px solid #1565C0; }

@media (max-width: 768px) {
    .two-col { grid-template-columns:1fr; }
    .page-header { flex-direction:column; gap:10px; }
}
</style>

@endsection