<?php

namespace Tests\Unit;

use App\Models\DailyEntry;
use App\Models\EntryItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_entry_has_fillable_fields(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dailyEntry = DailyEntry::create([
            'user_id' => $user->id,
            'date' => '2024-01-15',
        ]);

        $this->assertEquals($user->id, $dailyEntry->user_id);
        $this->assertEquals('2024-01-15', $dailyEntry->date->format('Y-m-d'));
    }

    public function test_date_is_cast_to_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dailyEntry = DailyEntry::create([
            'user_id' => $user->id,
            'date' => '2024-01-15',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $dailyEntry->date);
    }

    public function test_daily_entry_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dailyEntry = DailyEntry::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $dailyEntry->user);
        $this->assertEquals($user->id, $dailyEntry->user->id);
    }

    public function test_daily_entry_has_many_entry_items(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);
        $item = Item::factory()->create(['user_id' => $user->id]);
        
        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
        ]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $dailyEntry->entryItems);
        $this->assertCount(1, $dailyEntry->entryItems);
        $this->assertEquals($entryItem->id, $dailyEntry->entryItems->first()->id);
    }

    public function test_waste_rating_calculation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);
        $item = Item::factory()->create(['user_id' => $user->id]);
        
        // Create entry items: used=80, wasted=20
        // Expected waste rating: (20 / 100) * 100 = 20.00%
        EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 80,
            'wasted_quantity' => 20,
        ]);

        $this->assertEquals(20.0, $dailyEntry->waste_rating);
    }

    public function test_waste_rating_returns_zero_for_no_items(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $this->assertEquals(0.0, $dailyEntry->waste_rating);
    }

    public function test_user_scope_filters_by_authenticated_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $entry1 = DailyEntry::factory()->create(['user_id' => $user1->id]);
        $entry2 = DailyEntry::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);
        $entries = DailyEntry::all();

        $this->assertCount(1, $entries);
        $this->assertEquals($entry1->id, $entries->first()->id);
    }
}
