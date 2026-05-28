<?php

namespace Tests\Unit;

use App\Models\DailyEntry;
use App\Models\EntryItem;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntryItemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that EntryItem has the correct fillable fields.
     * Requirements: 8.1-8.5, 9.1-9.5, 10.1-10.5, 24.1-24.7
     */
    public function test_entry_item_has_fillable_fields(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 50.25,
            'wasted_quantity' => 10.75,
            'notes' => 'Test notes',
        ]);

        $this->assertDatabaseHas('entry_items', [
            'id' => $entryItem->id,
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 50.25,
            'wasted_quantity' => 10.75,
            'notes' => 'Test notes',
        ]);
    }

    /**
     * Test that quantity fields are cast to decimal with 2 decimal places.
     * Requirements: 24.5
     */
    public function test_quantity_fields_are_cast_to_decimal(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 100.5,
            'wasted_quantity' => 25.3,
        ]);

        $this->assertIsString($entryItem->used_quantity);
        $this->assertIsString($entryItem->wasted_quantity);
        $this->assertEquals('100.50', $entryItem->used_quantity);
        $this->assertEquals('25.30', $entryItem->wasted_quantity);
    }

    /**
     * Test that EntryItem belongs to DailyEntry.
     * Requirements: 8.4, 24.3
     */
    public function test_entry_item_belongs_to_daily_entry(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
        ]);

        $this->assertInstanceOf(DailyEntry::class, $entryItem->dailyEntry);
        $this->assertEquals($dailyEntry->id, $entryItem->dailyEntry->id);
    }

    /**
     * Test that EntryItem belongs to Item.
     * Requirements: 8.4, 24.4
     */
    public function test_entry_item_belongs_to_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
        ]);

        $this->assertInstanceOf(Item::class, $entryItem->item);
        $this->assertEquals($item->id, $entryItem->item->id);
    }

    /**
     * Test waste rating calculation with normal values.
     * Formula: (wasted_quantity / (used_quantity + wasted_quantity)) × 100
     * Requirements: 14.1, 14.3, 14.4
     */
    public function test_waste_rating_calculation(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        // Test case: used=80, wasted=20
        // Expected: (20 / 100) * 100 = 20.00%
        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 80,
            'wasted_quantity' => 20,
        ]);

        $this->assertEquals(20.0, $entryItem->waste_rating);
    }

    /**
     * Test waste rating returns zero when both quantities are zero.
     * Requirements: 14.4
     */
    public function test_waste_rating_returns_zero_for_zero_quantities(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 0,
            'wasted_quantity' => 0,
        ]);

        $this->assertEquals(0.0, $entryItem->waste_rating);
    }

    /**
     * Test waste rating with only used quantity (no waste).
     * Requirements: 14.1
     */
    public function test_waste_rating_with_no_waste(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 100,
            'wasted_quantity' => 0,
        ]);

        $this->assertEquals(0.0, $entryItem->waste_rating);
    }

    /**
     * Test waste rating with only wasted quantity (100% waste).
     * Requirements: 14.1
     */
    public function test_waste_rating_with_all_waste(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 0,
            'wasted_quantity' => 50,
        ]);

        $this->assertEquals(100.0, $entryItem->waste_rating);
    }

    /**
     * Test that notes field is optional.
     * Requirements: 10.2
     */
    public function test_notes_field_is_optional(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $entryItem = EntryItem::create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 10,
            'wasted_quantity' => 5,
            // notes intentionally omitted
        ]);

        $this->assertNull($entryItem->notes);
        $this->assertDatabaseHas('entry_items', [
            'id' => $entryItem->id,
            'notes' => null,
        ]);
    }

    /**
     * Test that notes can store text.
     * Requirements: 10.3
     */
    public function test_notes_stores_text(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        $notes = 'Some items were damaged during delivery';
        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 10,
            'wasted_quantity' => 5,
            'notes' => $notes,
        ]);

        $this->assertEquals($notes, $entryItem->notes);
    }

    /**
     * Test waste rating with decimal quantities.
     * Requirements: 14.1, 24.5
     */
    public function test_waste_rating_with_decimal_quantities(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $dailyEntry = DailyEntry::factory()->create(['user_id' => $user->id]);

        // Test case: used=75.50, wasted=24.50
        // Expected: (24.50 / 100) * 100 = 24.50%
        $entryItem = EntryItem::factory()->create([
            'daily_entry_id' => $dailyEntry->id,
            'item_id' => $item->id,
            'used_quantity' => 75.50,
            'wasted_quantity' => 24.50,
        ]);

        $this->assertEquals(24.5, $entryItem->waste_rating);
    }
}
