<?php

namespace Tests\Feature\AccountTransfer;

use App\Models\Account;
use App\Models\AccountTransfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetAccountTransfersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_account_transfers_list(): void
    {
        $user = User::factory()->create();
        $transfers = $user->accountTransfers()->saveMany(
            AccountTransfer::factory(config('app.pagination_size'))->create()
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('api.account-transfers.index')
        );

        $response->assertOk();
        $this->assertEquals(
            $transfers->pluck('id'),
            collect($response->json('accountTransfers'))->pluck('id')
        );
    }

    public function test_user_can_get_only_their_account_transfers(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $transfers = $user->accountTransfers()->saveMany(
            AccountTransfer::factory(config('app.pagination_size'))->create()
        );
        $otherUser->accountTransfers()->saveMany(
            AccountTransfer::factory(config('app.pagination_size'))->create()
        );

        Sanctum::actingAs($user);
        $response = $this->getJson(
            route('api.account-transfers.index')
        );

        $this->assertEquals(
            $transfers->pluck('id'),
            collect($response->json('accountTransfers'))->pluck('id')
        );
    }

    public function test_guest_cannot_get_any_account_transfers(): void
    {
        AccountTransfer::factory(config('app.pagination_size'))->create();

        $response = $this->getJson(route('api.account-transfers.index'));

        $response->assertUnauthorized();
    }

    public function test_paginating_account_transfers()
    {
        $user = User::factory()->create();
        $batchA = $user->accountTransfers()->saveMany(
            AccountTransfer::factory(config('app.pagination_size'))->create()
        );
        $batchB = $user->accountTransfers()->saveMany(
            AccountTransfer::factory(config('app.pagination_size'))->create()
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                ['page' => 1]
            )
        );

        $this->assertEquals(
            $batchA->pluck('id'),
            collect($response->json('accountTransfers'))->pluck('id')
        );

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                ['page' => 2]
            )
        );

        $this->assertEquals(
            $batchB->pluck('id'),
            collect($response->json('accountTransfers'))->pluck('id')
        );
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
        $transfersAccountA = $accountA->transfersFrom()->saveMany(
            AccountTransfer::factory(1)->create([
                'user_id' => $user->id,
            ])
        );
        $accountB->transfersFrom()->saveMany(
            AccountTransfer::factory(1)->create([
                'user_id' => $user->id,
            ])
        );

        $response = $this->getJson(
            route(
                'api.account-transfers.index',
                ['account_id' => $accountA->id]
            )
        );
        $this->assertEquals(
            $transfersAccountA->pluck('id'),
            collect($response->json('accountTransfers'))->pluck('id')
        );
    }
}
