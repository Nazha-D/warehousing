<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ExchangeRateSeeder extends Seeder
{
    public function run(): void
    {
        // استدعاء command مباشرة
        $this->callCommand();
    }

    protected function callCommand()
    {
        $exitCode = Artisan::call('exchange:update', [

        ]);

        echo Artisan::output();
    }
}
