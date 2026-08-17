<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

// Hoi quy FR6: nhan tin chi giua hai vai tro DOI DIEN creator <-> mentor.
// Creator-creator, mentor-mentor va admin deu bi chan (404).
// Dung DatabaseTransactions (khong migrate) de chay duoc tren DB dev MySQL.
class MessagingRoleRestrictionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_creator_can_message_mentor_and_vice_versa(): void
    {
        $creator = User::factory()->create();
        $mentor = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($creator)
            ->post(route('messages.store', $mentor), ['content' => 'Chào mentor!'])
            ->assertRedirect(route('messages.show', $mentor));

        $this->actingAs($mentor)
            ->post(route('messages.store', $creator), ['content' => 'Chào creator!'])
            ->assertRedirect(route('messages.show', $creator));

        $this->assertDatabaseHas('messages', [
            'sender_id' => $creator->id,
            'receiver_id' => $mentor->id,
            'content' => 'Chào mentor!',
        ]);
    }

    public function test_creator_cannot_message_creator(): void
    {
        $creator1 = User::factory()->create();
        $creator2 = User::factory()->create();

        $this->actingAs($creator1)->get(route('messages.show', $creator2))->assertNotFound();
        $this->actingAs($creator1)
            ->post(route('messages.store', $creator2), ['content' => 'Lách luật'])
            ->assertNotFound();

        $this->assertDatabaseMissing('messages', [
            'sender_id' => $creator1->id,
            'receiver_id' => $creator2->id,
        ]);
    }

    public function test_mentor_cannot_message_mentor(): void
    {
        $mentor1 = User::factory()->create(['role' => 'mentor']);
        $mentor2 = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($mentor1)
            ->post(route('messages.store', $mentor2), ['content' => 'Lách luật'])
            ->assertNotFound();
    }

    public function test_admin_cannot_message_anyone(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();

        // Route nhan tin nam trong nhom middleware 'role:creator,mentor' → admin bi chan 403
        $this->actingAs($admin)->get(route('messages.show', $creator))->assertForbidden();
        $this->actingAs($admin)
            ->post(route('messages.store', $creator), ['content' => 'Admin nhắn'])
            ->assertForbidden();
    }

    public function test_new_conversation_list_only_shows_opposite_role(): void
    {
        $creator = User::factory()->create();
        $otherCreator = User::factory()->create(['name' => 'Creator Cùng Vai Trò XYZ']);
        $mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Đối Diện XYZ']);

        $this->actingAs($creator)
            ->get(route('messages.index'))
            ->assertOk()
            ->assertSee('Mentor Đối Diện XYZ')
            ->assertDontSee('Creator Cùng Vai Trò XYZ');
    }
}
