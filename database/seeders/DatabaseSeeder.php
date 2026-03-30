<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Http\Controllers\CarBrandController;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionsSeeder::class);
       $this->call(InitialSeeder::class);
       $this->call(PackageSeeder::class);
       $this->call(ItemTypeSeeder::class);
        $this->call(SubrefSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(ExchangeRateSeeder::class);
        $this->call(CarBrandsAndModelsSeeder::class);
        $this->call(CarColorsSeeder::class);
        $this->call(LineTypeSeeder::class);

    }
}
