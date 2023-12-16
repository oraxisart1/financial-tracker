<?php

namespace App\Services;

use App\Contracts\CurrencyService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use JsonException;

class ApiCurrencyService implements CurrencyService
{
    public function __construct(private readonly Client $client)
    {
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function fetchCurrencyList(): Collection
    {
        $response = $this->client->get('https://openexchangerates.org/api/currencies.json')->getBody()->getContents();
        $currencyList = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        return collect($currencyList)
            ->map(fn(string $name, string $code) => ['name' => $name, 'code' => $code]);
    }
}
