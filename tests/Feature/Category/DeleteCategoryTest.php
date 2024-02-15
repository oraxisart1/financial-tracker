<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_their_own_category(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->save(Category::factory()->create());
        $this->assertCount(1, Category::all());

        $response = $this->actingAs($user)->from(route('settings'))->delete(
            route(
                'categories.destroy',
                ['category' => $category]
            )
        );

        $response->assertRedirectToRoute('settings');
        $this->assertCount(0, Category::all());
    }

    public function test_user_can_not_delete_other_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = $otherUser->categories()->save(Category::factory()->create());
        $this->assertCount(1, Category::all());

        $response = $this->actingAs($user)->delete(
            route(
                'categories.destroy',
                ['category' => $category]
            )
        );

        $response->assertNotFound();
        $this->assertCount(1, Category::all());
    }

    public function test_guest_can_not_delete_any_category(): void
    {
        $user = User::factory()->create();
        $category = $user->categories()->save(Category::factory()->create());
        $this->assertCount(1, Category::all());

        $response = $this->delete(
            route(
                'categories.destroy',
                ['category' => $category]
            )
        );

        $response->assertRedirectToRoute('login');
        $this->assertCount(1, Category::all());
    }
}
