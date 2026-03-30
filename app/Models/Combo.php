<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCombo
 */
class Combo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'total',
        'active',
        'currency_id',
        'brand',
        'img_path'
    ];
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('name', 'like', '%'.$filter.'%')
                ->orWhere('code', 'like', '%'.$filter.'%');
        }
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function items()
    {
        return $this->hasMany(ComboItem::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
