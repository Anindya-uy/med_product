<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariant;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];

    public function records(){
       return $this->hasMany(ProductVariant::class,'variant_id','id');
    }

}
