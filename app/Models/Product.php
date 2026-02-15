<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'code_product',
        'name',
        'stock',
        'harga_beli',
        'harga_jual',
        'supplier_id',
    ];

    
    public function productToSupplier(){
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
    //
}
