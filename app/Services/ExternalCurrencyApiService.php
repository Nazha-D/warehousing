<?php


namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExternalCurrencyApiService
{
    protected $apiKey;
    protected $url;

    public function __construct()
    {
        $this->apiKey = config('services.currencylayer.key');
        $this->url = "https://api.currencylayer.com/live";
    }
    public function getAllUsdRates(): array
    {
        $response = Http::get($this->url, [
            'access_key' => $this->apiKey,
            'format' => 1
        ]);

        if (!$response->ok() || !$response['success']) {
            return [];
        }

        return $response['quotes'] ?? [];
    }


}
