<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperUserSalespersonConfiguration
 */
class UserSalespersonConfiguration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commission_method_id',
        'cashing_method_id',
        'commission',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashingMethod()
    {
        return $this->belongsTo(CashingMethod::class);
    }

    public function commissionMethod()
    {
        return $this->belongsTo(CommissionMethod::class);
    }
}
