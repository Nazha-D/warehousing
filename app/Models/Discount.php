<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'type', 'value'
    ];

    public function posInvoices()
    {
        return $this->hasMany(PosInvoice::class);
    }
}
