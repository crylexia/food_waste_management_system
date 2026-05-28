<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StorageItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorageControllerTest extends TestCase
{
    use RefreshDatabase;

    // ── Authentication guard ──────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_storage_index(): void
    {
        $response = $this->get('/storage');
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_post_to_storage(): void
    {
        $response = $this->post('/storage', []);
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_deplete_storage_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $si   = StorageItem::factory()->create(['user_id' => $user->id, 'item_id' => $item->id]);

        $response = $this->patch("/storage/{$si->id}/deplete");
        $response->assertRedirect('/login');
    }

    // ── Basic CRUD ────────────────────────────────────────────────

    public function test_authenticated_user_can_view_storage_index(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/storage');
        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_storage_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/storage', [
            'item_id'         => $item->id,
            'quantity'        => 10.5,
            'expiration_date' => now()->addDays(30)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('storage_items', [
            'user_id'  => $user->id,
            'item_id'  => $item->id,
            'quantity' => 10.5,
            'status'   => 'active',
        ]);
    }

    public function test_authenticated_user_can_deplete_their_storage_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $si   = StorageItem::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'status'  => 'active',
        ]);

        $this->actingAs($user)->patch("/storage/{$si->id}/deplete");

        $this->assertDatabaseHas('storage_items', [
            'id'     => $si->id,
            'status' => 'depleted',
        ]);
    }

    public function test_authenticated_user_can_restore_their_storage_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $si   = StorageItem::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'status'  => 'depleted',
        ]);

        $this->actingAs($user)->patch("/storage/{$si->id}/restore");

        $this->assertDatabaseHas('storage_items', [
            'id'     => $si->id,
            'status' => 'active',
        ]);
    }

    public function test_authenticated_user_can_delete_their_storage_item(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $user->id]);
        $si   = StorageItem::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user)->delete("/storage/{$si->id}");

        $this->assertDatabaseMissing('storage_items', ['id' => $si->id]);
    }

    // ── Cross-user isolation (IDOR prevention) ────────────────────

    public function test_user_cannot_deplete_another_users_storage_item(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $item     = Item::factory()->create(['user_id' => $owner->id]);
        $si       = StorageItem::factory()->create([
            'user_id' => $owner->id,
            'item_id' => $item->id,
            'status'  => 'active',
        ]);

        // Attacker tries to deplete owner's item — should 404 via UserScope
        $response = $this->actingAs($attacker)->patch("/storage/{$si->id}/deplete");
        $response->assertStatus(404);

        // Record must remain unchanged
        $this->assertDatabaseHas('storage_items', [
            'id'     => $si->id,
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_restore_another_users_storage_item(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $item     = Item::factory()->create(['user_id' => $owner->id]);
        $si       = StorageItem::factory()->create([
            'user_id' => $owner->id,
            'item_id' => $item->id,
            'status'  => 'depleted',
        ]);

        $response = $this->actingAs($attacker)->patch("/storage/{$si->id}/restore");
        $response->assertStatus(404);
    }

    public function test_user_cannot_delete_another_users_storage_item(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $item     = Item::factory()->create(['user_id' => $owner->id]);
        $si       = StorageItem::factory()->create([
            'user_id' => $owner->id,
            'item_id' => $item->id,
        ]);

        $response = $this->actingAs($attacker)->delete("/storage/{$si->id}");
        $response->assertStatus(404);

        $this->assertDatabaseHas('storage_items', ['id' => $si->id]);
    }

    public function test_user_cannot_update_another_users_storage_item(): void
    {
        $owner    = User::factory()->create();
        $attacker = User::factory()->create();
        $item     = Item::factory()->create(['user_id' => $owner->id]);
        $si       = StorageItem::factory()->create([
            'user_id'  => $owner->id,
            'item_id'  => $item->id,
            'quantity' => 50,
        ]);

        $response = $this->actingAs($attacker)->put("/storage/{$si->id}", [
            'quantity' => 999,
            'status'   => 'discarded',
        ]);
        $response->assertStatus(404);

        $this->assertDatabaseHas('storage_items', [
            'id'       => $si->id,
            'quantity' => 50,
        ]);
    }

    public function test_user_only_sees_their_own_storage_items_in_index(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $item1 = Item::factory()->create(['user_id' => $user1->id]);
        $item2 = Item::factory()->create(['user_id' => $user2->id]);

        StorageItem::factory()->create(['user_id' => $user1->id, 'item_id' => $item1->id]);
        StorageItem::factory()->create(['user_id' => $user2->id, 'item_id' => $item2->id]);

        $this->actingAs($user1);
        $visible = StorageItem::all();

        $this->assertCount(1, $visible);
        $this->assertEquals($user1->id, $visible->first()->user_id);
    }
}
