<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drink extends Model
{
    public function transactionsHistory()
    {
        return $this->belongsToMany(Transaction::class, 'drinks_transactions')->withTimeStamps();
    }
}
