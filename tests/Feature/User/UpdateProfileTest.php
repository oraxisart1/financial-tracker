<?php

namespace Tests\Feature\User;

use App\Models\User;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    public function test_user_can_update_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Old name',
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            ['name' => 'New name']
        );

        $response->assertSessionHasNoErrors();
        $this->assertEquals('New name', $user->fresh()->name);
    }

    public function test_name_is_required()
    {
        $user = User::factory()->create([
            'name' => 'Old name',
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            ['name' => '']
        );

        $response->assertSessionHasErrors('name');
        $this->assertEquals('Old name', $user->fresh()->name);
    }
}
