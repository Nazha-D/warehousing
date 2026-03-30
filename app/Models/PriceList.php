<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceList extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'company_id',
        'client_id',
        'currency_id',
        'parent_id',
        'active',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function scopeFilter($query, string $filter)
    {
        if ($filter) {
            return $query->where('name', 'like', '%' . $filter . '%');
        }
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function rules()
    {
        return $this->hasMany(PriceListRule::class);
    }
    public function items()
    {
        return $this->hasMany(PriceListItem::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function parent()
    {
        return $this->belongsTo(PriceList::class,'parent_id');
    }
}
