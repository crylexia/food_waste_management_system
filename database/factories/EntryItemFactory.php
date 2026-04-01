<?php

namespace Database\Factories;

use App\Models\DailyEntry;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EntryItem>
 */
class EntryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'daily_entry_id' => DailyEntry::factory(),
            'item_id' => Item::factory(),
            'used_quantity' => fake()->randomFloat(2, 0, 100),
            'wasted_quantity' => fake()->randomFloat(2, 0, 20),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
