<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperTaxationGroup
 */
class TaxationGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'active',
    ];

    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where(function ($query) use ($filter) {
                $query->where('code', 'like', '%'.$filter.'%')
                    ->orWhere('name', 'like', '%'.$filter.'%');
            });
        }
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
//
    public function taxRates()
    {
        return $this->hasMany(TaxRate::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function isUsed()
    {
        //check if this group is used anywhere in the system so it cannot be deleted
        return $this->items()->exists();
    }
}
