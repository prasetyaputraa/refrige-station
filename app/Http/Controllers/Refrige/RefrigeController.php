<?php

namespace App\Http\Controllers\Refrige;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Transaction;
use App\Models\Drink;

class RefrigeController extends Controller
{
    public function transaction(Request $request)
    {
        $input = $request->all();

        $drinks = $input[$drinks];

        try {
            DB::beginTransaction();

            $transaction = new Transaction();
            $drink       = new Drink();

            $transaction->save();

            $transaction->transaction($drinks);

            foreach($drinks as $id => $amount) {
                $currentAmount = (int) $drink->where('id', $id)->first()-> amount + $amount;

                $drink->where('id', $id)->update(['amount' => $currentAmount]);
            }
        } catch (Exception $e) {
            DB::rollback();
        } finally {
            DB::commit();
        }
    }
}
