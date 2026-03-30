<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CarBrandsAndModelsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $brandsWithModels = [
            'Toyota' => ['Corolla', 'Camry', 'Yaris', 'Land Cruiser', 'Hilux', 'RAV4'],
            'Nissan' => ['Sunny', 'Altima', 'Maxima', 'Patrol', 'X-Trail', 'Micra'],
            'Honda' => ['Civic', 'Accord', 'CR-V', 'Jazz', 'Pilot'],
            'Mitsubishi' => ['Lancer', 'Pajero', 'Outlander', 'ASX'],
            'Mazda' => ['Mazda 3', 'Mazda 6', 'CX-5', 'CX-9'],
            'Subaru' => ['Impreza', 'Forester', 'Outback', 'Legacy'],
            'Hyundai' => ['Elantra', 'Sonata', 'Accent', 'Tucson', 'Santa Fe'],
            'Kia' => ['Rio', 'Cerato', 'Sportage', 'Sorento', 'Picanto', 'Optima'],
            'Mercedes-Benz' => ['C-Class', 'E-Class', 'S-Class', 'GLC', 'GLE'],
            'BMW' => ['3 Series', '5 Series', '7 Series', 'X3', 'X5'],
            'Audi' => ['A3', 'A4', 'A6', 'Q5', 'Q7'],
            'Volkswagen' => ['Golf', 'Passat', 'Jetta', 'Tiguan'],
            'Peugeot' => ['206', '207', '208', '308', '3008'],
            'Renault' => ['Clio', 'Megane', 'Symbol', 'Duster'],
            'Citroën' => ['C3', 'C4', 'C5', 'Berlingo'],
            'Volvo' => ['S40', 'S60', 'XC60', 'XC90'],
            'Chevrolet' => ['Aveo', 'Optra', 'Cruze', 'Tahoe', 'Trailblazer'],
            'Ford' => ['Focus', 'Fiesta', 'Fusion', 'Explorer', 'Edge'],
            'Jeep' => ['Cherokee', 'Grand Cherokee', 'Wrangler', 'Compass'],
            'Dodge' => ['Charger', 'Challenger', 'Durango'],
            'GMC' => ['Terrain', 'Acadia', 'Yukon'],
        ];

        foreach ($brandsWithModels as $brand => $models) {
            $brandId = DB::table('car_brands')->insertGetId([
                'name' => $brand,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($models as $model) {
                DB::table('car_models')->insert([
                    'car_brand_id' => $brandId,
                    'name' => $model,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
