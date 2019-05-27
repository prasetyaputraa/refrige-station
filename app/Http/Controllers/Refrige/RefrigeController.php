<?php

namespace App\Http\Controllers\Refrige;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Transaction;
use App\Models\Drink;

use DB;

class RefrigeController extends Controller
{
    private $drinks_name_id = [
        'Coca Cola' => 1,
        'Sprite'    => 2,
        'Fanta'     => 3,
        'Teh Pucuk' => 4,
    ];

    public function transaction(Request $request)
    {
        $input = $request->all();

        //$drinks = $input[$drinks];
        //
        $drinks_ = json_decode($request->getContent(), true);

        $drinks = [];

        foreach($drinks_ as $name => $amount) {
            $drinks[$this->drinks_name_id[$name]] = $amount;
        }

        try {
            DB::beginTransaction();

            $transaction = new Transaction();
            $drink       = new Drink();

            $transaction->save();

            $transaction->transaction($drinks);

            foreach($drinks as $id => $amount) {
                $currentAmount = (int) $drink->where('id', $id)->first()->amount + $amount;

                if ($currentAmount < 0) {
                    throw new \Exception("Drink of id {$id} is not in Refrige in the first place!");
                }

                $drink->where('id', $id)->update(['amount' => $currentAmount]);
            }
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => $e->getMessage()], 400);
        } finally {
            DB::commit();
        }

        return response()->json($this->successStatus);
    }
}
