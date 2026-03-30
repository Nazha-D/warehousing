<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTerm extends Model
{
    use HasFactory;
    protected $fillable=['company_id','name','code'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
