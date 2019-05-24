<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Drink;
use App\Models\Transaction;

use Carbon\Carbon;

class AdminController extends Controller
{
    private const DAILY   = 1;
    private const WEEKLY  = 2;
    private const MONTHLY = 3;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $drink = new Drink();

        $drinks = $drink->get();

        return view('admin/index.blade.php')->with($drinks);
    }

    protected function log(Request $request)
    {
        $transaction = new Transaction();

        $log = $request['log'];

        $result = [];

        switch($log) {
        case 1:
            $todayTransaction = $transaction->whereDate('created_at', Carbon::today())->get();

            $n = 0;

            foreach($todayTransaction as $t) {
                $result[$n]['date'] = $t->created_at->format('Y-m-d H:i:s');

                $items = [];

                $transactionsDetail = $t->transactionsDetail()->get();

                $itemN = 0;

                foreach($transactionsDetail as $td) {
                    $items[$itemN]['name']     = $td->name;
                    $items[$itemN]['capacity'] = $td->capacity;
                    $items[$itemN]['amount']   = $td->amount;

                    $itemN++;
                }

                $result[$n]['items'] = $items;

                $n++;
            }
            break;
        case WEEKLY:
            break;
        case MONTHLY:
            break;
        default:
            // default is daily log
            break;
        }

        return $result;
        //return view('admin/log.blade.php')->with($data);
    }
}
