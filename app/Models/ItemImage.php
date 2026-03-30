<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ItemImage extends Model
{
    use HasFactory;

    protected $table = 'item_images';

    protected $fillable = [
        'item_id',
        'path',
        'order_index',
    ];

    /**
     * العلاقة مع المنتج
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * حذف الصورة من التخزين عند حذف السجل
     */
    protected static function booted()
    {
        static::deleting(function ($image) {
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }
}
