<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantPrice;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function prices(){
       return $this->hasMany(ProductVariantPrice::class,'product_id','id');
    }
    public function product_variants()
    {
        return $this->hasMany('App\Models\ProductVariant');
    }

}
