<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAddress extends Model
{
    use HasFactory;
    protected $fillable=[
        'company_id','client_id','type','name',
        'title','job_position','phone_code','phone_number',
        'extension','mobile_code','mobile_number','email',
        'delivery_address','note','internal_note'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
