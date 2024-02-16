<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UpdateProfileTest extends TestCase
{
    public function test_user_can_update_account(): void
    {
        $user = User::factory()->create([
            'name' => 'Old name',
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            [
                'name' => 'New name',
                'current_password' => 'old_password',
                'new_password' => 'new_password',
                'new_password_confirmation' => 'new_password',
            ]
        );

        $response->assertSessionHasNoErrors();
        tap($user->fresh(), function (User $user) {
            $this->assertEquals('New name', $user->name);
            $this->assertTrue(Hash::check('new_password', $user->password));
        });
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

    public function test_current_password_is_optional_when_new_password_not_specified()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            $this->validParams([
                'current_password' => '',
                'new_password' => '',
            ])
        );

        $response->assertSessionHasNoErrors();
        tap($user->fresh(), function (User $user) {
            $this->assertTrue(Hash::check('old_password', $user->password));
        });
    }

    public function test_current_password_is_required_when_new_password_specified()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            $this->validParams([
                'current_password' => '',
                'new_password' => 'new_password',
            ])
        );

        $response->assertSessionHasErrors('current_password');
        tap($user->fresh(), function (User $user) {
            $this->assertTrue(Hash::check('old_password', $user->password));
        });
    }

    public function test_current_password_must_be_valid()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            $this->validParams([
                'current_password' => 'not_valid_password',
                'new_password' => 'new_password',
            ])
        );

        $response->assertSessionHasErrors('current_password');
        tap($user->fresh(), function (User $user) {
            $this->assertTrue(Hash::check('old_password', $user->password));
        });
    }

    public function test_current_password_validates_only_when_new_password_specified()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            $this->validParams([
                'current_password' => 'not_valid_password',
                'new_password' => '',
            ])
        );

        $response->assertSessionHasNoErrors();
        tap($user->fresh(), function (User $user) {
            $this->assertTrue(Hash::check('old_password', $user->password));
        });
    }

    public function test_new_password_must_be_confirmed()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            $this->validParams([
                'current_password' => 'old_password',
                'new_password' => 'new_password',
                'new_password_confirmation' => '',
            ])
        );

        $response->assertSessionHasErrors('new_password');
        tap($user->fresh(), function (User $user) {
            $this->assertTrue(Hash::check('old_password', $user->password));
        });
    }

    public function test_new_password_must_be_different_from_the_current_one()
    {
        $user = User::factory()->create([
            'password' => bcrypt('old_password'),
        ]);

        $response = $this->actingAs($user)->patch(
            route('profile.update'),
            $this->validParams([
                'current_password' => 'old_password',
                'new_password' => 'old_password',
                'new_password_confirmation' => 'old_password',
            ])
        );

        $response->assertSessionHasErrors('new_password');
        tap($user->fresh(), function (User $user) {
            $this->assertTrue(Hash::check('old_password', $user->password));
        });
    }

    public function validParams(array $overrides = []): array
    {
        return [
            'name' => 'New name',
            'current_password' => 'old_password',
            'new_password' => 'new_password',
            'new_password_confirmation' => 'new_password',
            ...$overrides,
        ];
    }
}
