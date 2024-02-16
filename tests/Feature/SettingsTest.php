<?php

namespace Tests\Feature;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    public function test_user_can_visit_settings_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertInertia(function (AssertableInertia $inertia) {
            $inertia->component('Settings');
        });
    }

    public function test_guest_can_not_visit_settings_page(): void
    {
        $response = $this->get(route('settings'));

        $response->assertRedirectToRoute('login');
    }

    public function test_settings_recieve_user_categories()
    {
        $user = User::factory()->create();
        $user->categories()->saveMany(Category::factory(3)->create());

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertInertia(function (AssertableInertia $inertia) {
            $inertia->has('categories', 3);
        });
    }

    public function test_settings_do_not_recieve_other_categories()
    {
        $user = User::factory()->create();
        $user->categories()->saveMany(Category::factory(3)->create());
        $otherUser = User::factory()->create();
        $otherUser->categories()->saveMany(Category::factory(3)->create());

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertInertia(function (AssertableInertia $inertia) {
            $inertia->has('categories', 3);
        });
    }

    public function test_filtering_categories_by_type()
    {
        $user = User::factory()->create();
        $user->categories()->saveMany(
            Category::factory(3)->create([
                'type' => CategoryType::INCOME->value,
            ])
        );
        $user->categories()->saveMany(
            Category::factory(3)->create([
                'type' => CategoryType::EXPENSE->value,
            ])
        );

        $response = $this->actingAs($user)->get(
            route('settings', ['category_type' => CategoryType::EXPENSE->value])
        );

        $response->assertInertia(function (AssertableInertia $inertia) {
            $inertia->has('categories', 3);
        });
    }

    public function test_settings_recieve_user_settings()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertInertia(function (AssertableInertia $inertia) {
            $inertia->has('userSettings');
        });
    }
}
