@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Create Daily Entry</h1>
        <p class="page-subtitle">Log a new inventory tracking entry</p>
    </div>
    <a href="{{ route('entries.index') }}" class="back-link">← Back to Entries</a>
</div>

<div class="two-col" style="align-items: flex-start;">

    <div class="a-card">
        <h3 class="section-title">📅 Entry Details</h3>
        <p class="section-desc">Each date can only have one entry</p>

        <form method="POST" action="{{ route('entries.store') }}">
            @csrf

            <div class="form-group">
                <label class="field-label" for="date">Date *</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    class="field-input @error('date') field-error @enderror"
                    value="{{ old('date', date('Y-m-d')) }}"
                    required
                >
                @error('date')
                    <div class="field-msg field-msg-error">{{ $message }}</div>
                @enderror
                <div class="field-hint">You can only create one entry per date.</div>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="btn-main">Create Entry</button>
                <a href="{{ route('entries.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>

    <div class="ins-box ins-success" style="align-self:flex-start;">
        <span class="ins-icon">💡</span>
        <div>
            <div class="ins-title">What happens next?</div>
            <div class="ins-msg">After creating the entry, you'll be taken directly to the entry page where you can add items with their used and wasted quantities.</div>
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

.field-label { display:block; font-size:12px; font-weight:600; color:#555; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
.field-input { width:100%; padding:10px 13px; border:1px solid #ddd; border-radius:8px; font-size:14px; color:#111; box-sizing:border-box; transition:border-color .2s; }
.field-input:focus { outline:none; border-color:#2D7A3E; box-shadow:0 0 0 3px rgba(45,122,62,.08); }
.field-input.field-error { border-color:#C62828; }
.field-msg-error { font-size:12px; color:#C62828; margin-top:5px; }
.field-hint { font-size:11px; color:#bbb; margin-top:5px; }

.btn-main  { padding:10px 22px; background:#2D7A3E; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; }
.btn-main:hover { background:#245f31; }
.btn-ghost { padding:10px 22px; background:#fff; color:#555; border:1px solid #ddd; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; transition:border-color .2s; }
.btn-ghost:hover { border-color:#aaa; }

.ins-box    { display:flex; gap:12px; padding:18px; border-radius:12px; border:1px solid #ebebeb; }
.ins-icon   { font-size:22px; flex-shrink:0; }
.ins-title  { font-size:13px; font-weight:700; color:#111; margin-bottom:4px; }
.ins-msg    { font-size:12px; color:#666; line-height:1.6; }
.ins-success { background:#f0fdf4; border-left:4px solid #2D7A3E; }

@media (max-width: 768px) {
    .two-col { grid-template-columns:1fr; }
    .page-header { flex-direction:column; gap:10px; }
}
</style>

@endsection