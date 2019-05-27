<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Validator;

use App\Models\User;

class UserController extends Controller
{

    public function login()
    {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {

            $user = Auth::user();

            $success['token'] = $user->createToken('Refrige')->accessToken;

            return response()->json(['success' => $success], $this->successStatus);
        } else {

            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(), 
            [
                'name' => 'required',
                'email' => 'required|email',
                'password' => 'required',
                'c_password' => 'required|same:password'
            ]
        );

        if ($validator->fails()) {

            return response()->json(['errors' => $validator->errors()], 401);
        }

        $input = $request->all();

        $input['password'] = bcrypt($input['password']);

        try {
            $user = User::create($input);
        } catch (Exception $e) {
            return response()->json(array('errors' => array($e->getMessage()), 500));
        }

        $success['token'] = $user->createToken('Refrige')->accessToken;

        $success['name'] = $user->name;

        return response()->json(['success' => $success], $this->successStatus);
    }
    //
}
