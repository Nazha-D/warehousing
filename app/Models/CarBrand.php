<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarBrand extends Model
{
    protected $fillable = ['name', 'user_id'];

    /**
     * Each brand can have many models.
     */
    public function carModels(): HasMany
    {
        return $this->hasMany(CarModel::class, 'car_brand_id');
    }

    /**
     * Add a new brand safely.
     */
    public static function addBrand(string $name, ?int $userId = null): self
    {
        $name = trim($name);

        // Reject invalid names (letters + spaces only)
        if (!preg_match('/^[\pL\s]+$/u', $name)) {
            throw new \InvalidArgumentException('Brand name contains invalid characters');
        }

        $name = ucfirst(strtolower($name));

        // Check if exists (case-insensitive)
        $brand = self::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if (!$brand) {
            $brand = self::create([
                'name' => $name,
                'user_id' => $userId,
            ]);
        }

        return $brand;
    }
}
