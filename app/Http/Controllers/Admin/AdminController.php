<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Drink;
use App\Models\Transaction;

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

    public function log(Request $request)
    {
        $transaction = new Transaction();

        $log = $request['log'];

        $data = null;

        switch($log) {
        case DAILY:
            break;
        case WEEKLY:
            break;
        case MONTHLY:
            break;
        default:
            // default is daily log
            break;
        }

        return view('admin/log.blade.php')->with($data);
    }
}
