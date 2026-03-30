<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExchangeRateService;
use Illuminate\Support\Facades\Http;
use App\Models\Currency;

class UpdateExchangeRates extends Command
{
    protected $signature = 'exchange:update';
    protected $description = 'Fetch and update exchange rates from API';

    public function handle()
    {
        try {
            $apiUrl = config('exchangerate.url');
            $apiKey = config('exchangerate.key');
            $base   = config('exchangerate.base', 'USD'); // مثال: USD

            // 1) الحصول على الـ base currency ID
            $baseCurrency = Currency::where('code', $base)->first();

            if (!$baseCurrency) {
                $this->error("Base currency {$base} not found in database.");
                return Command::FAILURE;
            }

            // 2) استدعاء API
            $response = Http::get($apiUrl, [
                'access_key' => $apiKey,
            ]);

            if ($response->failed()) {
                $this->error("API request failed.");
                return Command::FAILURE;
            }

            $data = $response->json();

            if (!isset($data['quotes'])) {
                $this->error("Invalid API response.");
                return Command::FAILURE;
            }

            // 3) بناء مصفوفة الريتات بشكل IDs بدل الأكواد
            $rates = [];

            foreach ($data['quotes'] as $key => $value) {
                // key = "USDEUR" → نزيل أول 3 أحرف (USD)
                $targetCode = substr($key, 3);

                $targetCurrency = Currency::where('code', $targetCode)->first();

                if (!$targetCurrency) {
                    // إذا عملة غير موجودة بالسيستم، نتجاوزها
                    $this->warn("Currency not found in DB: {$targetCode}. Skipping.");
                    continue;
                }

                // الآن نمرر باستخدام IDs
                $rates[] = [
                    'from_currency_id' => $baseCurrency->id,
                    'to_currency_id'   => $targetCurrency->id,
                    'rate'             => $value,
                ];
            }

            if (empty($rates)) {
                $this->warn("No valid exchange rates found.");
                return Command::SUCCESS;
            }

            // 4) تمرير للـ Service
            $service = new ExchangeRateService();
            $service->updateRatesFromAPI($rates);

            $this->info("Exchange rates updated successfully.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
