<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'car_brand_id',
        'car_model_id',
        'car_color_id',
        'car_technician_id',
        'plate_number',
        'chassis_number',
        'car_fax',
        'year',
        'rating',
        'odometer',
        'comment',
    ];
protected $appends=['brand_name','model_name','tech_name','color_name'];
    /**
     * Relationships
     */
     
     
     
    public function getBrandNameAttribute()
    {
        return $this->carBrand()->first()->name ?? '';
    }
    public function getModelNameAttribute()
    {
        return $this->carModel()->first()->name ?? '';
    }
    public function getTechNameAttribute()
    {
        return $this->carTechnician()->first()->name ?? '';
    }
    public function getColorNameAttribute()
    {
        return $this->carColor()->first()->name ?? '';
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function carBrand()
    {
        return $this->belongsTo(CarBrand::class);
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class);
    }

    public function carColor()
    {
        return $this->belongsTo(CarColor::class);
    }

    public function carTechnician()
    {
        return $this->belongsTo(CarTechnician::class);
    }
}
