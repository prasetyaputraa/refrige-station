<?php

namespace App\Http\Controllers\Refrige;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Transaction;
use App\Models\Drink;

use DB;
use Arr;
use Storage;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

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
        //$drinks_ = json_decode($request->getContent(), true);
        $drinks_ = json_decode($request->drinks, true);

        $drinks = [];

        $messageBody = [];

        foreach($drinks_ as $name => $amount) {
            $drinks[$this->drinks_name_id[$name]] = $amount;

            $action = "ditaruh";

            if ($amount < 0) {
                $action = "diambil";
            }

            $absAmount = abs($amount);

            array_push($messageBody, "{$absAmount} botol {$name} {$action}");

            //$messageBody = Arr::add($messageBody, "{$absAmount} botol {$name} {$action}");
        }

        try {
            DB::beginTransaction();

            $transaction = new Transaction();
            $drink       = new Drink();

            $photo = $request->file('photo')->store('public/photos');

            $transaction->photo = $photo;

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

        $this->notification(implode(", ", $messageBody));

        return response()->json(['photo' => asset($photo)], $this->successStatus);
        //return response()->json(['photo' => Storage::url($photo)], $this->successStatus);
    }

    public function notification($messageBody)
    {
        $optionsBuilder = new OptionsBuilder();
        $optionsBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder('Transaksi pada Smart Refrige kamu!');
        $notificationBuilder->setBody($messageBody)->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['adata' => 'mydata']);

        $option = $optionsBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = 'ewj5FxBIui4:APA91bFzsTCi9JEtSnjRdOxDk4D8qy_pC5tuEbYvmHAB6I6oueTqb28WFRsWv2RTA7MP1zHudq3IteRSuUbW9m4S_AJw21DOVeEmUIjHEkyucGV6mlYsxzJbwQ9-zrZNQkln7nLx1buT';

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        $downstreamResponse->tokensToDelete();

        $downstreamResponse->tokensToModify();

        $downstreamResponse->tokensToRetry();

    }
}
