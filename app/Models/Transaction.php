<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    public function transactionsDetail()
    {
        return $this->belongsToMany(Drink::class, 'drinks_transactions')->withTimeStamps();
    }

    public function transaction($drinks)
    {
        foreach($drinks as $drink => $amount)
        {
            $this->transactionsDetail()->attach($drink, ['amount' => $amount]);
        }
    }
}
