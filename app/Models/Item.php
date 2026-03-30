<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $table = 'items';

    protected $fillable = [
        'company_id',
        'item_type_id',
        'category_id',
        'main_code',
        'auto_generated_code',
        'item_name',
        'short_description',
        'main_description',
        'second_language_description',
        'taxation_group_id',
        'print_main_code',
        'subref_id',
        'can_be_sold',
        'can_be_purchased',
        'warranty',
        'last_allowed_purchase_date',
        'unit_cost',
        'decimal_cost',
        'quantity',
        'decimal_quantity',
        'unit_price',
        'decimal_price',
        'line_discount_limit',
        'package_id',
        'default_transaction_package_id',
        'package_unit_name',
        'package_unit_quantity',
        'package_set_name',
        'package_set_quantity',
        'package_superset_name',
        'package_superset_quantity',
        'package_palette_name',
        'package_palette_quantity',
        'package_container_name',
        'package_container_quantity',
        'weight',
        'volume',
        'currency_id',
        'price_currency_id',
        'pos_currency_id',
        'show_on_pos',
        'discontinued',
        'blocked',
        'active',
    ];

    protected $casts = [
        'can_be_sold' => 'boolean',
        'can_be_purchased' => 'boolean',
        'warranty' => 'boolean',
        'print_main_code' => 'boolean',
        'show_on_pos' => 'boolean',
        'discontinued' => 'boolean',
        'blocked' => 'boolean',
        'active' => 'boolean',
        'unit_cost' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'line_discount_limit' => 'decimal:4',
        'weight' => 'decimal:4',
        'volume' => 'decimal:4',
        'quantity' => 'integer',
        'decimal_cost' => 'integer',
        'decimal_price' => 'integer',
        'decimal_quantity' => 'integer',
        'last_allowed_purchase_date' => 'date',
    ];

    /****************************
     * Relationships
     ****************************/

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    public function taxationGroup()
    {
        return $this->belongsTo(TaxationGroup::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function defaultTransactionPackage()
    {
        return $this->belongsTo(Package::class, 'default_transaction_package_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function subref()
    {
        return $this->belongsTo(Subref::class);
    }
    public function priceCurrency()
    {
        return $this->belongsTo(Currency::class, 'price_currency_id');
    }

    public function posCurrency()
    {
        return $this->belongsTo(Currency::class, 'pos_currency_id');
    }
    public function supplierCodes()
    {
        return $this->hasMany(SupplierCode::class);
    }
    public function alternativeCodes()
    {
        return $this->hasMany(AlternativeCode::class);
    }
    public function barCodes()
    {
        return $this->hasMany(BarCode::class);
    }
    public function itemGroups()
    {
        return $this->belongsToMany(ItemGroup::class, 'item_group_item', 'item_id', 'item_group_id');
    }
    public function itemImages()
    {
        return $this->hasMany(ItemImage::class);
    }

    public function scopeFilter($query, $filter,$searchByCat)
    {
        $query->when($searchByCat, function ($q) use ($searchByCat) {
            $q->where('category_id', '=', $searchByCat);
        });

        $query->when($filter, function ($q) use ($filter) {
            $q->where(function ($subQuery) use ($filter) {
                $subQuery->where('main_code', 'like', '%' . $filter . '%')
                    ->orWhereHas('barcodes', function ($subQuery) use ($filter) {
                        $subQuery->where('code', 'like', '%' . $filter . '%');
                    })
                    ->orWhereHas('supplierCodes', function ($subQuery) use ($filter) {
                        $subQuery->where('code', 'like', '%' . $filter . '%');
                    })
                    ->orWhereHas('alternativeCodes', function ($subQuery) use ($filter) {
                        $subQuery->where('code', 'like', '%' . $filter . '%');
                    })
                    ->orWhere('item_name', 'like', '%' . $filter . '%')
                    ->orWhere('main_description', 'like', '%' . $filter . '%');
            });
        });

        return $query;


    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    public function scopePos($query)
    {
        return $query->where('show_on_pos', true);
    }
    public function scopeBarcode($query,$filter)
    {

        return $query->whereHas('barCodes', function ($subQuery) use ($filter) {
            $subQuery->where('code',  $filter );
        });
    }
    public function transferItems()
    {
        return $this->hasMany(TransferItem::class);
    }

}
