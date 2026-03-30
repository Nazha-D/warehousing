<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class QuotationLine extends Model
{
    protected $fillable = [
        'quotation_id',
        'line_type_id',
        'order_index',
        'item_id',
        'package_id',
        'combo_id',
        'title',
        'description',
        'note',
        'image_path',
        'quantity',
        'unit_price',
        'discount',
        'total',

    ];
    protected $appends=['combo_code','item_code'];


    public function getComboCodeAttribute()
    {
        if($this->combo()->exists())
            return $this->combo()->first()->code;
        else
            return '';


    }


    public function getItemCodeAttribute()
    {
        if($this->item()->exists())
            return $this->item()->first()->main_code;
        else
            return '';


    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }
    public  function lineType()
    {
        return $this->belongsTo(LineType::class);
    }
}
