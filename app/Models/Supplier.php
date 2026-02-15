<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'address',
        'contact',
        'email',
    ];

    public function supplierToProduct(){
        return $this->hasMany(Product::class, 'id', 'supplier_id');
    }
    //
}
