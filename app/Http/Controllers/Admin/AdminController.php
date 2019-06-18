<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Drink;
use App\Models\Transaction;

use DB;
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

    public function index(Request $request)
    {
        $drink       = new Drink();
        $transaction = new Transaction();

        $drinks = $drink->get();
        $photo  = $transaction->latest()->first()->photo;

        $data = [
            'drinks' => $drinks,
            'photo' => str_replace("public", "storage", $photo)
        ];

        return view('admin/overview')->with($data);
    }

    public function logView(Request $request)
    {
    }

    public function overview()
    {
        $drink = new Drink();

        $drinks = $drink->get();

        return response()->json($drinks, 200);
    }

    public function photo()
    {
        $transaction = new Transaction();

        $photo = $transaction->latest()->first()->photo;

        $photo = str_replace("public", "storage", $photo);

        return response()->json($photo, 200);
    }

    protected function log(Request $request)
    {
        $transaction = new Transaction();

        $log = (int)$request['log'];

        $result = [];

        try {
            switch($log) {
                case 1:
                    $todayTransactions = $transaction->whereDate('created_at', Carbon::today())->get();

                    $result = $this->getTransactionArray($todayTransactions);
                    break;
                case 2:
                    break;
                case 3:
                    $query = DB::table('drinks_transactions')
                        ->join('drinks', 'drinks.id', '=', 'drinks_transactions.drink_id')
                        ->join('transactions', 'transactions.id', '=', 'drinks_transactions.transaction_id')
                        ->select(DB::raw('sum(drinks_transactions.amount) as amount, drinks.name, drinks.capacity, MONTHNAME(transactions.created_at) as month'))
                        ->whereRaw('MONTH(transactions.created_at) = MONTH(CURRENT_DATE())')
                        ->groupBy('drinks.name')
                        ->get();

                    $n = 0;

                    $result[0]['date'] = $query[0]->month;

                    $items = [];
                    $itemN = 0;

                    foreach ($query as $q) {
                        $items[$itemN]['name']     = $q->name;
                        $items[$itemN]['capacity'] = $q->capacity;
                        $items[$itemN]['amount']   = $q->amount;

                        $itemN++;
                    }

                    $result[0]['items'] = $items;

                    break;
                default:
                    $todayTransactions = $transaction->whereDate('created_at', Carbon::today())->get();

                    $result = $this->getTransactionArray($todayTransactions);
                    break;
            }
        } catch (Exception $e) {
            return response()->json(array(500));
        }

        if (in_array('web', $request->route()->action['middleware'])) {
            return view('admin/log')->with(
                array(
                    'log'    => $log,
                    'result' => $result
                )
            );
        }

        return response()->json($result);
    }

    protected function getTransactionArray($transactions, $groupBy = false)
    {
        $result = [];
        $n      = 0;

        foreach($transactions as $t) {
            $result[$n]['date'] = $t->created_at->format('Y-m-d H:i:s');

            $items = [];

            $transactionsDetail = $t->transactionsDetail()->get();

            $itemN = 0;

            foreach($transactionsDetail as $td) {
                $items[$itemN]['name']     = $td->name;
                $items[$itemN]['capacity'] = $td->capacity;
                $items[$itemN]['amount']   = $td->pivot->amount;

                $itemN++;
            }

            $result[$n]['items'] = $items;

            $n++;
        }

        return $result;
    }
}
