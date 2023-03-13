<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function show()
    {
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
            'taste' => 'required|string',
            'images'=>'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'special_range' => 'required|date_format:Y-m-d',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'remark' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $item = new Item;
        $item->name = $request->name;
        $item->price = $request->price;
        $item->is_available = json_decode($request->is_available);
        $item->privacy = $request->privacy;
        $item->taste = $request->taste;
        $item->time_limited = date('Y-m-d');
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
        }else {
            return response()->json(['message' => "Item can't created"], 505);
        }

    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'price' => 'required|numeric',
            'is_available' => 'required|boolean',
            'privacy' => 'required|in:public,private',
            'taste' => 'required|string',
            'images' => 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'time_limited' => 'required|string',
            'special_range' => 'required|date_format:Y-m-d H:i:s',
            'view' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'remark' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }else {
            $item = Item::findOrFail($id);
            $item->name = $request->name;
            $item->price = $request->price;
            $item->is_available = $request->is_available;
            $item->privacy = $request->privacy;
            $item->taste = $request->taste;
            $item->time_limited = $request->time_limited;
            $item->special_range = $request->special_range;
            $item->view = $request->view;
            $item->category_id = $request->category_id;
            $item->description = $request->description;
            $item->remark = $request->remark;

            if ($request->hasFile('images')) {
                $images = unserialize($item->images);
                foreach ($images as $image) {
                    Storage::delete('public/images/'.$image);
                }

                $images = [];
                foreach ($request->images as $image) {
                    $path = $image->store('public/images');
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
    public function delete($id)
    {
        $item = Item::findOrFail($id);
        // $images = unserialize($item->images);
        // foreach ($images as $image) {
        //     Storage::delete('public/images/'.$image);
        // }
        $item->delete();

        return response()->json(['message' => 'Item deleted successfully'], 200);
    }

    public function restore($id)
    {
        $item = Item::onlyTrashed()->findOrFail($id);
        $item->restore();

        return response()->json(['message' => 'Item restored successfully'], 200);
    }


}
