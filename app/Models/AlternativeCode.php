<?php

namespace App\Models;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperAlternativeCode
 */
class AlternativeCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'alternative_codes';

    protected $fillable = [
        'code',
        'print_code',
        'company_id',
        'item_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
