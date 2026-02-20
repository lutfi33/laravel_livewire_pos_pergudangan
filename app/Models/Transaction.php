<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'name_transaction',
        'total_amount',
        'cash',
        'return',
        'payment',
        'cashier',
    ];

      public function transactionToItem(){
        return $this->hashMany(ItemSelling::class, 'id', 'transaction_id');
    }
    //
}
