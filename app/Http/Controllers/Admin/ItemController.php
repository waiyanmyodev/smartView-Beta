<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function show()
    {
        if(!Gate::allows('admin-auth',Auth::user())){
            return response()->json(['status'=>false,"message"=>"Access denied!"]);
        }
        
        $items = Item::with('category','category.shop')->get()->groupBy('category.shop_id');

        if($items != null)
        {
            return response()->json(['status'=>true,'items'=>$items]);
        }
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'is_available' => 'required',
            'privacy' => 'required|in:public,private',
            // 'tag' => 'required|string',
            'images'=>'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'special_range' => 'required|date_format:Y-m-d',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        if(!Gate::allows('admin-auth',Auth::user())){
            return response()->json(['status'=>false,"message"=>"Access denied!"]);
        }


        $item = new Item;
        $item->name = $request->name;
        $item->price = $request->price;
        $item->currency = "MMK";
        $item->is_available = json_decode($request->is_available);
        $item->privacy = $request->privacy;
        $item->tag = "hi";
        $item->special_range = $request->special_range;
        $item->view = 0;
        $item->category_id = $request->category_id;
        $item->description = $request->description;
        $item->remark = $request->remark;
        $images = [];
        
        foreach ($request->file('images') as $image) {
            $path = $image->move(public_path('images/shop/item'),$image->getClientOriginalName());
            $images[] = basename($path);
        }

        $item->images = serialize($images);

        if($item->save()){
            return response()->json(['message' => 'Item created successfully'], 201);
        }

        return response()->json(['message' => "Item can't created"], 505);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'is_available' => 'required',
            'privacy' => 'required|in:public,private',
            'tag' => 'required|string',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        if(Gate::allows('admin-auth',Auth::user())) {
            $item = Item::find($request->id);
            $item->name = $request->name;
            $item->price = $request->price;
            $item->currency = "MMK";
            $item->is_available = $request->is_available;
            $item->privacy = $request->privacy;
            $item->tag = $request->tag;
            $item->category_id = $request->category_id;
            $item->description = $request->description;
            $item->remark = $request->remark;

            if ($request->hasFile('images')) {
                $images = unserialize($item->images);

                foreach ($images as $image) {
                    Storage::delete(public_path('images/shop/item').$image);
                }

                $images = [];

                foreach ($request->file('images') as $image) {
                    $path = $image->move(public_path('images/shop/item'),$image->getClientOriginalName());
                    $images[] = basename($path);
                }

                $item->images = serialize($images);
            }

            if($item->update()){
                return response()->json(['message' => 'Item updated successfully'], 200);
            }else {
                return response()->json(['message' => "Item can't updated"], 505);
            }
        }
    }

    public function delete(Request $req)
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $item = Item::find($req->id);
            $item->delete();
            return response()->json(['message' => 'Item deleted successfully'], 200);
        }
        
    }

    public function restore(Request $req)
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $item = Item::withTrashed()->find($req->id);
            if($item->restore()){
                return response()->json(['status'=>true,'message'=>"Items restored."]);
            }else {
                return response()->json(['status'=>true,'message'=>"Items can't restore!"]);
            }
        }
        
    }

    public function trashshow()
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $items = Item::onlyTrashed()->get();
            return response()->json(['status'=>true,'items'=>$items]);
            
        }
    }

    public function restoreAll(Request $req)
    {
        if(Gate::allows('admin-auth',Auth::user())){
            $items =Item::onlyTrashed();
            if($items->restore()){
                return response()->json(['status'=>true,'message'=>"All Items restored."]);
            }else {
                return response()->json(['status'=>false,'message'=>"Items can't restore!"]);
            }
        }
        
    }
}