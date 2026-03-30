<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSubref
 */
class Subref extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
