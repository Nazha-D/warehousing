<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarColorsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $colors = [
            'White',
            'Black',
            'Silver',
            'Gray',
            'Blue',
            'Red',
            'Green',
            'Beige',
            'Brown',
            'Yellow',
            'Orange',
            'Gold',
            'Maroon',
            'Purple',
            'Turquoise',
            'Pink',
        ];

        foreach ($colors as $color) {
            DB::table('car_colors')->insert([
                'name' => $color,
                'user_id'=>null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
