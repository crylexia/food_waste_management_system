<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StorageItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'item_id'         => Item::factory(),
            'quantity'        => $this->faker->randomFloat(2, 1, 100),
            'expiration_date' => $this->faker->optional()->dateTimeBetween('now', '+60 days')?->format('Y-m-d'),
            'received_date'   => $this->faker->optional()->dateTimeBetween('-30 days', 'now')?->format('Y-m-d'),
            'batch_number'    => $this->faker->optional()->bothify('BATCH-###??'),
            'notes'           => $this->faker->optional()->sentence(),
            'status'          => 'active',
        ];
    }
}
