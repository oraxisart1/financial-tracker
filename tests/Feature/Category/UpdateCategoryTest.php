<?php

namespace Tests\Feature\Category;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Tests\TestCase;

class UpdateCategoryTest extends TestCase
{
    public function test_user_can_update_their_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'type' => CategoryType::EXPENSE,
            'color' => '#FF0000',
        ]);

        $response = $this->actingAs($user)->from(route('dashboard'))->patch(
            route('categories.update', ['category' => $category]),
            [
                'title' => 'New Title',
                'type' => CategoryType::INCOME->value,
                'color' => '#0000FF',
            ]
        );

        $response->assertRedirectToRoute('dashboard');
        $response->assertSessionHasNoErrors();
        tap($category->fresh(), function (Category $category) use ($user) {
            $this->assertTrue($category->user->is($user));
            $this->assertEquals('New Title', $category->title);
            $this->assertEquals(CategoryType::INCOME, $category->type);
            $this->assertEquals('#0000FF', $category->color);
        });
    }

    public function test_user_cannot_update_other_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $category = Category::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Old Title',
            'type' => CategoryType::EXPENSE,
            'color' => '#FF0000',
        ]);

        $response = $this->actingAs($user)->patch(
            route('categories.update', ['category' => $category]),
            [
                'title' => 'New Title',
                'type' => CategoryType::INCOME->value,
                'color' => '#0000FF',
            ]
        );

        $response->assertStatus(404);
        tap($category->fresh(), function (Category $category) {
            $this->assertEquals('Old Title', $category->title);
            $this->assertEquals(CategoryType::EXPENSE, $category->type);
            $this->assertEquals('#FF0000', $category->color);
        });
    }

    public function test_guest_cannot_update_any_category(): void
    {
        $category = Category::factory()->create([
            'title' => 'Old Title',
            'type' => CategoryType::EXPENSE,
            'color' => '#FF0000',
        ]);

        $response = $this->patch(
            route('categories.update', ['category' => $category]),
            [
                'title' => 'New Title',
                'type' => CategoryType::INCOME->value,
                'color' => '#0000FF',
            ]
        );

        $response->assertRedirectToRoute('login');
        tap($category->fresh(), function (Category $category) {
            $this->assertEquals('Old Title', $category->title);
            $this->assertEquals(CategoryType::EXPENSE, $category->type);
            $this->assertEquals('#FF0000', $category->color);
        });
    }

    public function test_title_is_required(): void
    {
        $category = Category::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($category->user)->patch(
            route('categories.update', ['category' => $category]),
            $this->validParams([
                'title' => '',
            ])
        );

        $response->assertSessionHasErrors('title');
        tap($category->fresh(), function (Category $category) {
            $this->assertEquals('Old Title', $category->title);
        });
    }

    public function test_type_is_required(): void
    {
        $category = Category::factory()->create(['type' => CategoryType::EXPENSE]);

        $response = $this->actingAs($category->user)->patch(
            route('categories.update', ['category' => $category]),
            $this->validParams([
                'type' => '',
            ])
        );

        $response->assertSessionHasErrors('type');
        tap($category->fresh(), function (Category $category) {
            $this->assertEquals(CategoryType::EXPENSE, $category->type);
        });
    }

    public function test_type_must_be_valid_category_type(): void
    {
        $category = Category::factory()->create(['type' => CategoryType::EXPENSE,]);

        $response = $this->actingAs($category->user)->patch(
            route('categories.update', ['category' => $category]),
            $this->validParams([
                'type' => 'not-valid-type',
            ])
        );

        $response->assertSessionHasErrors('type');
        tap($category->fresh(), function (Category $category) {
            $this->assertEquals(CategoryType::EXPENSE, $category->type);
        });
    }

    public function test_color_is_required(): void
    {
        $category = Category::factory()->create(['color' => '#FF0000']);

        $response = $this->actingAs($category->user)->patch(
            route('categories.update', ['category' => $category]),
            $this->validParams([
                'color' => '',
            ])
        );

        $response->assertSessionHasErrors('color');
        tap($category->fresh(), function (Category $category) {
            $this->assertEquals('#FF0000', $category->color);
        });
    }

    public function test_color_must_be_valid_hex_color(): void
    {
        $category = Category::factory()->create(['color' => '#FF0000']);

        $response = $this->actingAs($category->user)->patch(
            route('categories.update', ['category' => $category]),
            $this->validParams([
                'color' => 'not-valid-color',
            ])
        );

        $response->assertSessionHasErrors('color');
        tap($category->fresh(), function (Category $category) {
            $this->assertEquals('#FF0000', $category->color);
        });
    }

    private function validParams(array $overrides = []): array
    {
        return [
            'title' => 'New Title',
            'type' => CategoryType::INCOME->value,
            'color' => '#0000FF',
            ...$overrides,
        ];
    }
}
