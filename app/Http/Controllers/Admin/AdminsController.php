<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth; 
use App\Models\User; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminsController extends Controller
{
    public function show(Request $req)
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $admins = User::where('shop_id',1)->get();
            if($admins)
            {
                return response()->json(['status'=>true,'admins'=>$admins]);
            }else {
                return response()->json(['status'=>false,"Message"=>"Can't get data because server network error!"]);
            }
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
        
        if(Gate::allows('admin-auth',Auth::user())){
            $admin = User::where('username',$req->username)->first();
            if($admin == null)
            {
                $admin = new User;
                $admin->shop_id = 1;
                $admin->username = $req->username;
                $admin->password = Hash::make($req->password);
                $admin->remark = " ";
                if($admin->save())
                {
                    $admin->assignRole('admin');
                    return response()->json(['status'=>true,'Message'=>"admin Successfully Created!"]);
                }
                else
                {
                    return response()->json(['status'=>true,'Message'=>"Can't Created New admin."]);
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
        if(Gate::allows('admin-auth',Auth::user())){
            $admin = User::find($req->id);
            $admin->shop_id = $req->shop_id;
            $admin->username = $req->username;
            if($req->password!=''){
                $admin->password = Hash::make($req->password);
            }
            if($admin->update())
            {
                return response()->json(['status'=>true,'Message'=>"Admin Edited!"]);
            }
            return response()->json(['status'=>true,'Message'=>"Can't Edit Admin"]);
            
        }

    }
    public function delete(Request $req)
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $user = User::find($req->id);
            if($user->delete())
            {
                return response()->json(['status'=>true,'message'=>"Admin Deleted!"]);
            }
            else
            {
                return response()->json(['status'=>true,'message'=>"User can't delete!, Try Again"]);
            }
        }


    }
    public function restore(Request $req)
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $admin = User::withTrashed()->find($req->id);
            if($admin->restore()){
                return response()->json(['status'=>true,'message'=>"Admin restored."]);
            }else {
                return response()->json(['status'=>true,'message'=>"Admin can't restore!"]);
            }
        }
    }

    public function trashshow()
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $admins = User::onlyTrashed()->get();
            return response()->json(['status'=>true,'admins'=>$admins]);
            
        }
    }

    public function restoreAll(Request $req)
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $admins =User::onlyTrashed();
            if($admins->restore()){
                return response()->json(['status'=>true,'message'=>"All Admins restored."]);
            }else {
                return response()->json(['status'=>false,'message'=>"Admins can't restore!"]);
            }
        }
        
    }
}
