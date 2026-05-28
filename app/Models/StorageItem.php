<?php

namespace App\Models;

use App\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorageItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'expiration_date',
        'received_date',
        'batch_number',
        'notes',
        'status',
    ];

    protected $casts = [
        'quantity'        => 'decimal:2',
        'expiration_date' => 'date',
        'received_date'   => 'date',
    ];

    /**
     * Apply the UserScope so every query is automatically scoped to the
     * authenticated user — prevents cross-user data leakage (IDOR).
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Expiry helpers ─────────────────────────────────────────

    public function getExpiryStatusAttribute(): string
    {
        if ($this->status !== 'active') return 'none';
        if (!$this->expiration_date) return 'none';
        if ($this->expiration_date->isPast()) return 'expired';
        if ($this->expiration_date->diffInDays(now()) <= 2) return 'critical';
        if ($this->expiration_date->diffInDays(now()) <= 7) return 'soon';

        return 'ok';
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if ($this->status !== 'active') return null;
        if (!$this->expiration_date) return null;

        return (int) now()
            ->startOfDay()
            ->diffInDays($this->expiration_date->startOfDay(), false);
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, int $days = 7)
    {
        return $query->active()
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', today()->addDays($days));
    }
}
