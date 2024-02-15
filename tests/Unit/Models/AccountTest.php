<?php

namespace Tests\Unit\Models;

use App\Enums\TransactionType;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_toggle_state()
    {
        $account = Account::factory()->create([
            'active' => true,
        ]);
        $this->assertEquals(true, $account->fresh()->active);

        $account->toggleState();

        $this->assertEquals(false, $account->fresh()->active);

        $account->toggleState();

        $this->assertEquals(true, $account->fresh()->active);
    }

    public function test_add_balance()
    {
        $account = Account::factory()->create([
            'balance' => 0,
        ]);
        $this->assertEqualsWithDelta(0, $account->balance, 0);

        $account->addBalance(1000);
        $this->assertEqualsWithDelta(1000, $account->fresh()->balance, 0);

        $account->addBalance(-500);
        $this->assertEqualsWithDelta(500, $account->fresh()->balance, 0);
    }
}
