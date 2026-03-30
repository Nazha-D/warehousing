<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperPaymentTerm
 */
class PaymentTerm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'title',
        'code',
        'active',
    ];
protected $casts=[
    'active'=>'boolean'
];
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function scopeFilter($query, $filter)
    {
        if ($filter) {
            return $query->where('title', 'like', '%' . $filter . '%');
            // ->orWhere('code', 'like', '%' . $filter . '%');
        }
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
