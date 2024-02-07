<?php

namespace Tests\Unit\Services;

use App\DTO\AccountTransferDTO;
use App\Models\Account;
use App\Models\AccountTransfer;
use App\Services\AccountTransferService;
use Carbon\Carbon;
use Date;
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

        $createdTransfer = (new AccountTransferService())->transferBetweenAccounts($accountFrom, $accountTo, $transfer);

        $this->assertTrue($createdTransfer->is($transfer));
        $this->assertCount(
            1,
            AccountTransfer::where('account_from_id', $accountFrom->id)
                ->where('account_to_id', $accountTo->id)
                ->get()
        );
        $this->assertEqualsWithDelta(1000, $accountFrom->balance, 0);
        $this->assertEqualsWithDelta(1200, $accountTo->balance, 0);
    }

    public function test_deletes_transfer()
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

    public function test_updating_transfer()
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
            'date' => Carbon::parse('2024-01-01'),
            'description' => 'Old description',
        ]);
        $transferService->transferBetweenAccounts($accountFrom, $accountTo, $transfer);
        $this->assertEqualsWithDelta(1000, $accountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(1200, $accountTo->fresh()->balance, 0);

        $transferService->update(
            $transfer,
            new AccountTransferDTO(
                $accountFrom->id,
                $accountTo->id,
                500,
                600,
                Date::parse('2024-02-01'),
                'New description'
            )
        );

        tap($transfer->fresh(), function (AccountTransfer $transfer) use ($accountFrom, $accountTo) {
            $this->assertEqualsWithDelta(500, $transfer->amount, 0);
            $this->assertEqualsWithDelta(600, $transfer->converted_amount, 0);
            $this->assertEquals(Carbon::parse('2024-02-01'), $transfer->date);
            $this->assertEquals('New description', $transfer->description);
            $this->assertTrue($transfer->accountFrom->is($accountFrom));
            $this->assertTrue($transfer->accountTo->is($accountTo));
            $this->assertEqualsWithDelta(1500, $transfer->accountFrom->balance, 0);
            $this->assertEqualsWithDelta(600, $transfer->accountTo->balance, 0);
        });
    }

    public function test_updating_transfer_accounts()
    {
        $transferService = new AccountTransferService();

        $oldAccountFrom = Account::factory()->create([
            'balance' => 2000,
        ]);
        $oldAccountTo = Account::factory()->create([
            'balance' => 0,
        ]);
        $newAccountFrom = Account::factory()->create([
            'balance' => 2000,
        ]);
        $newAccountTo = Account::factory()->create([
            'balance' => 0,
        ]);
        $transfer = AccountTransfer::factory()->create([
            'amount' => 1000,
            'converted_amount' => 1200,
        ]);
        $transferService->transferBetweenAccounts($oldAccountFrom, $oldAccountTo, $transfer);
        $this->assertEqualsWithDelta(1000, $oldAccountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(1200, $oldAccountTo->fresh()->balance, 0);


        $transferService->update(
            $transfer,
            new AccountTransferDTO(
                $newAccountFrom->id,
                $newAccountTo->id,
                500,
                600,
                Date::parse('2024-02-01'),
                'New description'
            )
        );

        $this->assertEqualsWithDelta(2000, $oldAccountFrom->fresh()->balance, 0);
        $this->assertEqualsWithDelta(0, $oldAccountTo->fresh()->balance, 0);
        tap($transfer->fresh(), function (AccountTransfer $transfer) use ($newAccountFrom, $newAccountTo) {
            $this->assertTrue($transfer->accountFrom->is($newAccountFrom));
            $this->assertTrue($transfer->accountTo->is($newAccountTo));
            $this->assertEqualsWithDelta(1500, $transfer->accountFrom->balance, 0);
            $this->assertEqualsWithDelta(600, $transfer->accountTo->balance, 0);
        });
    }
}
