<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      public function index()
    {
        return response()->json(
            Product::with('category')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required',
            'price' => 'required',
            'stock' => 'required',
            'image' => 'nullable|image'
        ]);

        $imagePath = null;

        if($request->hasFile('image'))
        {
            $imagePath = $request
                ->file('image')
                ->store('products','public');
        }

        $product = Product::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'status' => $request->status ?? 1
        ]);

        return response()->json([
            'message' => 'Product Created',
            'data' => $product
        ]);
    }

    public function show($id)
    {
        return response()->json(
            Product::with('category')->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if($request->hasFile('image'))
        {
            if($product->image)
            {
                Storage::disk('public')
                    ->delete($product->image);
            }

            $product->image = $request
                ->file('image')
                ->store('products','public');
        }

        $product->update([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $request->status
        ]);

        $product->save();

        return response()->json([
            'message' => 'Product Updated'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if($product->image)
        {
            Storage::disk('public')
                ->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product Deleted'
        ]);
    }
   
}
