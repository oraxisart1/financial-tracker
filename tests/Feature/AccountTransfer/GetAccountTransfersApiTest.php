<?php

namespace Tests\Feature\AccountTransfer;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetAccountTransfersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_account_transfers_list(): void
    {
        $user = User::factory()->create();
        $user->accountTransfers()->saveMany(
            AccountTransfer::factory(2)->create()
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                ['per_page' => 3]
            )
        );

        $response->assertOk();
        $this->assertCount(2, $response->json('accountTransfers'));
    }

    public function test_user_can_get_only_their_account_transfers(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transfer = $user->accountTransfers()->save(
            AccountTransfer::factory()->create()
        );
        $otherUser->accountTransfers()->save(
            AccountTransfer::factory()->create()
        );

        Sanctum::actingAs($user);
        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                [
                    'per_page' => 2,
                ]
            )
        );

        $this->assertTrue($transfer->is(AccountTransfer::find($response->json('accountTransfers')[0]['id'])));
    }

    public function test_guest_cannot_get_any_account_transfers(): void
    {
        $response = $this->getJson(route('api.account-transfers.index'));

        $response->assertUnauthorized();
    }

    public function test_paginating_account_transfers()
    {
        $user = User::factory()->create();
        $batchA = $user->accountTransfers()->save(
            AccountTransfer::factory()->create()
        );
        $batchB = $user->accountTransfers()->save(
            AccountTransfer::factory()->create()
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                [
                    'page' => 1,
                    'per_page' => 1,
                ]
            )
        );

        $idA = $response->json('accountTransfers')[0]['id'];
        $this->assertCount(1, $response->json('accountTransfers'));

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                [
                    'page' => 2,
                    'per_page' => 1,
                ]
            )
        );

        $idB = $response->json('accountTransfers')[0]['id'];
        $this->assertCount(1, $response->json('accountTransfers'));
        $this->assertNotEquals($idA, $idB);
    }

    public function test_filtering_by_account_id()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $accountA = $user->accounts()->save(
            Account::factory()->create()
        );
        $accountB = $user->accounts()->save(
            Account::factory()->create()
        );
        $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['account_from_id' => $accountA->id])
        );
        $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['account_to_id' => $accountA->id])
        );
        $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['account_from_id' => $accountB->id])
        );
        $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['account_to_id' => $accountB->id])
        );

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                [
                    'account_id' => $accountA->id,
                    'per_page' => 4,
                ]
            )
        );

        $this->assertCount(2, $response->json('accountTransfers'));
    }

    public function test_correct_ordering()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $transferA = $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['date' => Carbon::parse('2 days ago')])
        );
        $transferB = $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['date' => Carbon::parse('now')])
        );
        $transferC = $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['date' => Carbon::parse('1 day ago')])
        );
        $transferD = $user->accountTransfers()->save(
            AccountTransfer::factory()->create(['date' => Carbon::parse('now')])
        );

        $order = collect([
            $transferD,
            $transferB,
            $transferC,
            $transferA,
        ]);

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                [
                    'per_page' => 4,
                ]
            )
        );

        $this->assertEquals(
            $order->pluck('id'),
            collect($response->json('accountTransfers'))->pluck('id')
        );
    }
}
