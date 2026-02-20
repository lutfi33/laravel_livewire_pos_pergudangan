<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSelling extends Model
{
    //
    protected $fillable = [
        'transaction_id',
        'id_product',
        'product',
        'price',
        'qty',
    ];

    public function itemToTransaction(){
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
