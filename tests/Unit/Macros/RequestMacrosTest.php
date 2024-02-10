<?php

namespace Tests\Unit\Macros;

use Illuminate\Http\Request;
use Tests\TestCase;

class RequestMacrosTest extends TestCase
{
    public function test_per_page_macros_returns_specified_value(): void
    {
        $request = new Request([
            'per_page' => 1,
        ]);

        $this->assertEquals(1, $request->perPage());
    }

    public function test_per_page_macros_returns_default_value_in_not_specified(): void
    {
        $request = new Request([]);

        $this->assertEquals(config('app.pagination_size'), $request->perPage());
    }
}
