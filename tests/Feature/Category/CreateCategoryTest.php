<?php

namespace Tests\Feature\Category;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post('/categories', [
            'title' => 'Test category',
            'type' => CategoryType::INCOME->value,
            'color' => '#FF0000',
        ]);

        $response->assertRedirectToRoute('dashboard');
        $response->assertSessionHasNoErrors();
        tap(Category::first(), function (Category $category) use ($user) {
            $this->assertTrue($category->user->is($user));
            $this->assertEquals('Test category', $category->title);
            $this->assertEquals(CategoryType::INCOME, $category->type);
            $this->assertEquals('#FF0000', $category->color);
        });
    }

    public function test_guest_cannot_create_category(): void
    {
        $response = $this->post('/categories', [
            'title' => 'Test category',
            'type' => CategoryType::INCOME->value,
            'color' => '#FF0000',
        ]);

        $response->assertRedirectToRoute('login');
    }

    public function test_title_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/categories',
            $this->validParams([
                'title' => '',
            ])
        );

        $response->assertSessionHasErrors('title');
        $this->assertCount(0, Category::all());
    }

    public function test_type_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/categories',
            $this->validParams([
                'type' => '',
            ])
        );

        $response->assertSessionHasErrors('type');
        $this->assertCount(0, Category::all());
    }

    public function test_type_must_be_valid_category_type(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/categories',
            $this->validParams([
                'type' => 'not-valid-type',
            ])
        );

        $response->assertSessionHasErrors('type');
        $this->assertCount(0, Category::all());
    }

    public function test_color_is_required(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/categories',
            $this->validParams([
                'color' => '',
            ])
        );

        $response->assertSessionHasErrors('color');
        $this->assertCount(0, Category::all());
    }

    public function test_color_must_be_valid_hex_color(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/dashboard')->post(
            '/categories',
            $this->validParams([
                'color' => 'not-valid-color',
            ])
        );

        $response->assertSessionHasErrors('color');
        $this->assertCount(0, Category::all());
    }

    private function validParams(array $overrides = []): array
    {
        return [
            'title' => 'Test category',
            'type' => CategoryType::INCOME->value,
            'color' => '#FF0000',
            ...$overrides,
        ];
    }
}
