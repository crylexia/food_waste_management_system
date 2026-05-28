<?php

namespace Tests\Unit;

use App\Models\DailyEntry;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserScopeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that UserScope filters items by authenticated user.
     */
    public function test_user_scope_filters_items_by_authenticated_user(): void
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create items for each user
        $item1 = Item::factory()->create([
            'user_id' => $user1->id,
            'name' => 'User 1 Item',
        ]);
        
        $item2 = Item::factory()->create([
            'user_id' => $user2->id,
            'name' => 'User 2 Item',
        ]);

        // Act as user 1 and query items
        $this->actingAs($user1);
        $user1Items = Item::all();

        // Assert user 1 only sees their own items
        $this->assertCount(1, $user1Items);
        $this->assertTrue($user1Items->contains($item1));
        $this->assertFalse($user1Items->contains($item2));

        // Act as user 2 and query items
        $this->actingAs($user2);
        $user2Items = Item::all();

        // Assert user 2 only sees their own items
        $this->assertCount(1, $user2Items);
        $this->assertTrue($user2Items->contains($item2));
        $this->assertFalse($user2Items->contains($item1));
    }

    /**
     * Test that UserScope filters daily entries by authenticated user.
     */
    public function test_user_scope_filters_daily_entries_by_authenticated_user(): void
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create daily entries for each user
        $entry1 = DailyEntry::factory()->create([
            'user_id' => $user1->id,
            'date' => '2024-01-15',
        ]);
        
        $entry2 = DailyEntry::factory()->create([
            'user_id' => $user2->id,
            'date' => '2024-01-16',
        ]);

        // Act as user 1 and query daily entries
        $this->actingAs($user1);
        $user1Entries = DailyEntry::all();

        // Assert user 1 only sees their own entries
        $this->assertCount(1, $user1Entries);
        $this->assertTrue($user1Entries->contains($entry1));
        $this->assertFalse($user1Entries->contains($entry2));

        // Act as user 2 and query daily entries
        $this->actingAs($user2);
        $user2Entries = DailyEntry::all();

        // Assert user 2 only sees their own entries
        $this->assertCount(1, $user2Entries);
        $this->assertTrue($user2Entries->contains($entry2));
        $this->assertFalse($user2Entries->contains($entry1));
    }

    /**
     * Test that UserScope does not filter when no user is authenticated.
     */
    public function test_user_scope_does_not_filter_when_not_authenticated(): void
    {
        // Create two users with items
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Item::factory()->create(['user_id' => $user1->id]);
        Item::factory()->create(['user_id' => $user2->id]);

        // Query items without authentication
        $items = Item::all();

        // Assert all items are returned (no filtering)
        $this->assertCount(2, $items);
    }

    /**
     * Test that UserScope works with find() method.
     */
    public function test_user_scope_works_with_find_method(): void
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create items for each user
        $item1 = Item::factory()->create(['user_id' => $user1->id]);
        $item2 = Item::factory()->create(['user_id' => $user2->id]);

        // Act as user 1 and try to find both items
        $this->actingAs($user1);
        
        // User 1 can find their own item
        $foundItem1 = Item::find($item1->id);
        $this->assertNotNull($foundItem1);
        $this->assertEquals($item1->id, $foundItem1->id);

        // User 1 cannot find user 2's item (returns null due to scope)
        $foundItem2 = Item::find($item2->id);
        $this->assertNull($foundItem2);
    }

    /**
     * Test that UserScope works with where() queries.
     */
    public function test_user_scope_works_with_where_queries(): void
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create items with same category for both users
        Item::factory()->create([
            'user_id' => $user1->id,
            'category' => 'ingredient',
        ]);
        
        Item::factory()->create([
            'user_id' => $user2->id,
            'category' => 'ingredient',
        ]);

        // Act as user 1 and query by category
        $this->actingAs($user1);
        $items = Item::where('category', 'ingredient')->get();

        // Assert user 1 only sees their own items
        $this->assertCount(1, $items);
        $this->assertEquals($user1->id, $items->first()->user_id);
    }

    /**
     * Test that UserScope prevents unauthorized updates.
     */
    public function test_user_scope_prevents_unauthorized_updates(): void
    {
        // Create two users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Create item for user 2
        $item = Item::factory()->create([
            'user_id' => $user2->id,
            'name' => 'Original Name',
        ]);

        // Act as user 1 and try to update user 2's item
        $this->actingAs($user1);
        
        // Try to find and update the item
        $foundItem = Item::find($item->id);
        
        // The item should not be found due to UserScope
        $this->assertNull($foundItem);
    }
}
