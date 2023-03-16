<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    public function show(Request $req)
    {
        $users = User::with('shop:id,shop_name')->get();
        if($users)
        {
            return response()->json(['status'=>true,'users'=>$users]);
        }else {
            return response()->json(['status'=>false,"Message"=>"Can't get data because server network error!"]);
        }
    }
    public function add(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shop_id' => 'required',
            'username' => 'required|string',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        else {
            $user = User::where('username',$req->username)->first();
            if($user == null)
            {
                $user = new User;
                $user->shop_id = $req->shop_id;
                $user->username = $req->username;
                $user->password = Hash::make($req->password);
                $user->remark = " ";
                if($user->save())
                {
                    return response()->json(['status'=>true,'Message'=>"User Successfully Created!"]);
                }
                else
                {
                    return response()->json(['status'=>true,'Message'=>"Can't Created New User."]);
                }
            }
            else
            {
                return response()->json(['status'=>true,'Message'=>"The username already exit, can't add new."]);
            }
        }

    }
    public function update(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'shop_id' => 'required',
            'username' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        else {
            $user = User::find($req->id);
            $user->shop_id = $req->shop_id;
            $user->username = $req->username;
            if($req->password!=''){
                $user->password = Hash::make($req->password);
            }
            if($user->update())
            {
                return response()->json(['status'=>true,'Message'=>"User Edited!"]);
            }
            else
            {
                return response()->json(['status'=>true,'Message'=>"Can't Edit User"]);
            }
        }

    }
    public function delete(Request $req)
    {
        $user = User::find($req->id);
        if($user->delete())
        {
            return response()->json(['status'=>true,'Message'=>"User Deleted!"]);
        }
        else
        {
            return response()->json(['status'=>true,'Message'=>"User can't delete!, Try Again"]);
        }


    }
    public function restore(Request $req)
    {
        $shop = User::withTrashed()->find($req->shop_id);
        if($shop->restore()){
            return response()->json(['status'=>true,"Item restored."]);
        }else {
            return response()->json(['status'=>true,"Item can't restore!"]);
        }
    }
}
