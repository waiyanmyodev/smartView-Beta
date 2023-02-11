<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|min:6|max:120',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'token'=>NULL]);
        }

        $check = Auth::attempt(['username' => $request->username, 'password' => $request->password]);
        if($check){
            $user = Auth::user();
            return response()->json(['status'=>true,'token'=>$user->createToken($request->password)->plainTextToken]);
        }else {
            return response()->json(['status'=>false,'token'=>NULL]);
        }
    }
}