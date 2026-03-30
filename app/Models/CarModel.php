<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $fillable = ['car_brand_id', 'user_id', 'name'];

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function addModel(int $brandId, string $name, ?int $userId = null): self
    {
        $name = trim($name);

        if (!preg_match('/^[\pL\s]+$/u', $name)) {
            throw new \InvalidArgumentException('Model name must contain only letters and spaces');
        }

        $name = ucfirst(strtolower($name));

        $model = self::where('car_brand_id', $brandId)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if (!$model) {
            $model = self::create([
                'car_brand_id' => $brandId,
                'user_id' => $userId,
                'name' => $name,
            ]);
        }

        return $model;
    }
}
