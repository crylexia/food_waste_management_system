<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EntryItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'daily_entry_id',
        'item_id',
        'used_quantity',
        'wasted_quantity',
        'waste_reason',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'used_quantity' => 'decimal:2',
        'wasted_quantity' => 'decimal:2',
    ];

    /**
     * Get the daily entry that owns the entry item.
     */
    public function dailyEntry(): BelongsTo
    {
        return $this->belongsTo(DailyEntry::class);
    }

    /**
     * Get the item that this entry item references.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calculate the waste rating for this entry item.
     * Waste rating = (wasted_quantity / (used_quantity + wasted_quantity)) × 100
     */
    protected function wasteRating(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $total = $this->used_quantity + $this->wasted_quantity;
                return $total > 0 ? ($this->wasted_quantity / $total) * 100 : 0;
            }
        );
    }
}
