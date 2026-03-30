<?php

namespace App\Models;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperCommissionMethod
 */
class CommissionMethod extends Model
{
    use HasFactory,  SoftDeletes;

    protected $fillable = [
        'title',
        'active',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
