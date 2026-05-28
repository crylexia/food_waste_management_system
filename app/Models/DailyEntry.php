<?php

namespace App\Models;

use App\Scopes\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DailyEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new UserScope);
    }

    /**
     * Get the user that owns the daily entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the entry items for the daily entry.
     */
    public function entryItems(): HasMany
    {
        return $this->hasMany(EntryItem::class);
    }

    /**
     * Calculate the waste rating for this entry.
     * Waste rating = (total_wasted / (total_used + total_wasted)) × 100
     */
    protected function wasteRating(): Attribute
    {
        return Attribute::make(
            get: function (): float {
                $totalUsed = $this->entryItems->sum('used_quantity');
                $totalWasted = $this->entryItems->sum('wasted_quantity');
                $total = $totalUsed + $totalWasted;
                
                return $total > 0 ? ($totalWasted / $total) * 100 : 0;
            }
        );
    }
}
