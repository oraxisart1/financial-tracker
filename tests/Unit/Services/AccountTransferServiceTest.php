<?php

namespace Tests\Unit\Services;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Services\AccountTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfering_between_accounts()
    {
        $accountFrom = Account::factory()->create([
            'balance' => 2000,
        ]);
        $accountTo = Account::factory()->create([
            'balance' => 0,
        ]);
        $transfer = AccountTransfer::factory()->create([
            'amount' => 1000,
            'converted_amount' => 1200,
        ]);
        $this->assertCount(
            0,
            AccountTransfer::where('account_from_id', $accountFrom->id)
                ->where('account_to_id', $accountTo->id)
                ->get()
        );
        $this->assertEqualsWithDelta(2000, $accountFrom->balance, 0);
        $this->assertEqualsWithDelta(0, $accountTo->balance, 0);

        (new AccountTransferService())->transferBetweenAccounts($accountFrom, $accountTo, $transfer);

        $this->assertCount(
            1,
            AccountTransfer::where('account_from_id', $accountFrom->id)
                ->where('account_to_id', $accountTo->id)
                ->get()
        );
        $this->assertEqualsWithDelta(1000, $accountFrom->balance, 0);
        $this->assertEqualsWithDelta(1200, $accountTo->balance, 0);
    }

    public function test_deletes_account_transfer()
    {
        $transferService = new AccountTransferService();

        $accountFrom = Account::factory()->create([
            'balance' => 2000,
        ]);
        $accountTo = Account::factory()->create([
            'balance' => 0,
        ]);
        $transfer = AccountTransfer::factory()->create([
            'amount' => 1000,
            'converted_amount' => 1200,
        ]);
        $transferService->transferBetweenAccounts($accountFrom, $accountTo, $transfer);
        $this->assertEqualsWithDelta(1000, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(1200, $accountTo->fresh()->balance, 0);

        $transferService->delete($transfer);

        $this->assertEqualsWithDelta(2000, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(0, $accountTo->fresh()->balance, 0);
    }
}
