<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    use HasFactory;
    protected $fillable=['name','terms_and_conditions','company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public  function quotations()
    {
        return $this->hasMany(Quotation::class,'terms_and_conditions_id');
    }
    public  function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }
}
