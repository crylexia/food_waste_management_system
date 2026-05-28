<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'daily_entry_id',
        'item_id',
        'used_quantity',
        'wasted_quantity',
        'waste_reason',
        'notes',
    ];

    protected $casts = [
        'used_quantity'   => 'decimal:2',
        'wasted_quantity' => 'decimal:2',
    ];

    public function dailyEntry(): BelongsTo
    {
        return $this->belongsTo(DailyEntry::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function getWasteRatingAttribute(): float
    {
        $total = $this->used_quantity + $this->wasted_quantity;
        return $total > 0 ? ($this->wasted_quantity / $total) * 100 : 0;
    }
}