<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperLineType
 */
class LineType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
