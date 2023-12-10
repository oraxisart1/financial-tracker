<?php

namespace Tests\Unit\Traits;

use App\Traits\EnumValues;
use PHPUnit\Framework\TestCase;

class EnumValuesTest extends TestCase
{
    public function test_returns_all_backed_enum_values(): void
    {
        $enum = new class () {
            use EnumValues;

            public static function cases()
            {
                return [
                    ['name' => 'NAME_1', 'value' => 'VALUE_1'],
                    ['name' => 'NAME_2', 'value' => 'VALUE_2'],
                ];
            }
        };

        $this->assertSame(['VALUE_1', 'VALUE_2'], $enum::values());
    }
}
