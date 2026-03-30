<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'taxation_group_id',
        'start_date',
        'tax_rate',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function taxationGroup()
    {
        return $this->belongsTo(TaxationGroup::class);
    }

    public function company()
    {
        return $this->hasOneThrough(Company::class, TaxationGroup::class,
            'id', 'id', 'taxation_group_id', 'company_id');
    }
    public function isUsed()
    {
        //check if this group is used anywhere in the system so it cannot be deleted
      return false;
    }
}
