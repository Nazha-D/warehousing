<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarColor extends Model
{
    protected $fillable = ['name','user_id'];

    public static function addColor(string $name, ?int $userId = null): self
    {
        $name = trim($name);

        // Reject invalid names (letters + spaces only)
        if (!preg_match('/^[\pL\s]+$/u', $name)) {
            throw new \InvalidArgumentException('Color name contains invalid characters');
        }

        $name = ucfirst(strtolower($name));

        $color = self::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if (!$color) {
            $color = self::create([
                'name' => $name,
                'user_id' => $userId
            ]);
        }

        return $color;
    }

}
