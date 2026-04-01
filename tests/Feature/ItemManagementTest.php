<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_items_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/items');

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_item(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/items', [
            'name' => 'Test Item',
            'category' => 'Product',
            'price' => 10.50,
        ]);

        $response->assertRedirect('/items');
        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'category' => 'Product',
            'price' => 10.50,
            'user_id' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_update_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($user)->put("/items/{$item->id}", [
            'name' => 'Updated Name',
            'category' => 'Ingredient',
            'price' => 5.00,
        ]);

        $response->assertRedirect('/items');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'Updated Name',
            'category' => 'Ingredient',
        ]);
    }

    public function test_authenticated_user_can_delete_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/items/{$item->id}");

        $response->assertRedirect('/items');
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_user_cannot_see_other_users_items(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $item1 = Item::factory()->create(['user_id' => $user1->id]);
        $item2 = Item::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1);
        $items = Item::all();

        $this->assertCount(1, $items);
        $this->assertTrue($items->contains($item1));
        $this->assertFalse($items->contains($item2));
    }
}
