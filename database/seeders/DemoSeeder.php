<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── CHANGE THIS to your user's ID ──────────────────────────
        $userId = 1;

        // ── 1. INSERT ITEMS ────────────────────────────────────────
        $items = [
            ['name' => 'Bread Loaf',      'category' => 'Bakery',      'price' => 65.00],
            ['name' => 'Cheese',          'category' => 'Dairy',       'price' => 420.00],
            ['name' => 'Cheese Powder',   'category' => 'Ingredients', 'price' => 70.00],
            ['name' => 'Chicken Breast',  'category' => 'Meat',        'price' => 180.00],
            ['name' => 'Coffee Beans',    'category' => 'Beverage',    'price' => 550.00],
            ['name' => 'Cooking Oil',     'category' => 'Ingredient',  'price' => 110.00],
            ['name' => 'Ground Beef',     'category' => 'Meat',        'price' => 320.00],
            ['name' => 'Gummies',         'category' => 'Snacks',      'price' => 30.00],
            ['name' => 'Lettuce',         'category' => 'Produce',     'price' => 90.00],
            ['name' => 'Milk',            'category' => 'Dairy',       'price' => 95.00],
            ['name' => 'Paper Straw',     'category' => 'Packaging',   'price' => 2.00],
            ['name' => 'Plastic Cup',     'category' => 'Packaging',   'price' => 3.00],
            ['name' => 'Potato Chips',    'category' => 'Snacks',      'price' => 45.00],
            ['name' => 'Rice',            'category' => 'Grains',      'price' => 60.00],
            ['name' => 'Snack Container', 'category' => 'Packaging',   'price' => 135.00],
            ['name' => 'Sugar Syrup',     'category' => 'Ingredient',  'price' => 120.00],
            ['name' => 'Takeout Box',     'category' => 'Packaging',   'price' => 8.00],
            ['name' => 'Tapioca Pearls',  'category' => 'Ingredient',  'price' => 150.00],
            ['name' => 'Tomatoes',        'category' => 'Produce',     'price' => 70.00],
            ['name' => 'Vanilla Syrup',   'category' => 'Ingredient',  'price' => 55.00],
        ];

        $itemIds = [];
        foreach ($items as $item) {
            // insertOrIgnore skips duplicates safely
            DB::table('items')->insertOrIgnore([
                'user_id'    => $userId,
                'name'       => $item['name'],
                'category'   => $item['category'],
                'price'      => $item['price'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $id = DB::table('items')
                ->where('user_id', $userId)
                ->where('name', $item['name'])
                ->value('id');

            $itemIds[] = $id;
        }

        // ── 2. INSERT 20 DAILY ENTRIES (last 20 days) ──────────────
        $wasteReasons = ['expired', 'overproduced', 'spoiled', 'leftover', 'other'];

        for ($i = 0; $i < 20; $i++) {
            $date = Carbon::today()->subDays($i)->toDateString();

            // Skip if entry already exists for this date
            $exists = DB::table('daily_entries')
                ->where('user_id', $userId)
                ->where('date', $date)
                ->exists();

            if ($exists) continue;

            $entryId = DB::table('daily_entries')->insertGetId([
                'user_id'    => $userId,
                'date'       => $date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Pick 2–5 random items per entry (no duplicates within one entry)
            $count = rand(2, 5);
            $selectedIds = collect($itemIds)->shuffle()->take($count);

            foreach ($selectedIds as $itemId) {
                $usedQty   = round(rand(1, 20) + rand(0, 9) / 10, 1);
                $wastedQty = round(rand(0, 5) + rand(0, 9) / 10, 1);

                // Ensure at least one is > 0 (DB constraint)
                if ($usedQty == 0 && $wastedQty == 0) {
                    $usedQty = 1.0;
                }

                DB::table('entry_items')->insert([
                    'daily_entry_id'  => $entryId,
                    'item_id'         => $itemId,
                    'used_quantity'   => $usedQty,
                    'wasted_quantity' => $wastedQty,
                    'waste_reason'    => $wastedQty > 0
                        ? $wasteReasons[array_rand($wasteReasons)]
                        : null,
                    'notes'           => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        $this->command->info('Done! 20 entries seeded with randomized items.');
    }
}