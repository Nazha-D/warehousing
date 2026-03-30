<?php

namespace App\Models;

use App\Traits\Observable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperSupplierCode
 */
class SupplierCode extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'supplier_codes';

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
