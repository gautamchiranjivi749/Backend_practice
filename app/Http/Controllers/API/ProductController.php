<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\ProductResource;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Traits\ApiResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    use ApiResponse;
    public function index(Request $request)
{
    $products = Product::with('category')
        ->where('status', 1);
    
        if ($request->filled('search')) {
        $products->where('name', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('category')) {
    $products->where('category_id', $request->category);
}
    if ($request->filled('sort')) {

    switch ($request->sort) {

        case 'price_asc':
            $products->orderBy('price', 'asc');
            break;

        case 'price_desc':
            $products->orderBy('price', 'desc');
            break;

        case 'latest':
            $products->latest();
            break;
    }
}

    return $this->success(
    ProductResource::collection($products->paginate(2)),
    'Products fetched successfully.'
    );
}
   public function store(StoreProductRequest $request)
{
    $validated = $request->validated();

    $imagePath = null;

    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('products', 'public');
    }

    $product = Product::create([
        'category_id' => $validated['category_id'],
        'name' => $validated['name'],
        'slug' => Str::slug($validated['name']),
        'description' => $validated['description'] ?? null,
        'price' => $validated['price'],
        'stock' => $validated['stock'],
        'status' => $validated['status'],
        'image' => $imagePath,
    ]);

    return $this->success(
        new ProductResource($product),
        'Product Created Successfully'
    );
}

   public function show(Product $product)
{
    return $this->success(
    new ProductResource($product),
    'Product fetched successfully.'
);
}

  public function update(UpdateProductRequest $request, $id)
{
    $product = Product::findOrFail($id);

    $validated = $request->validated();

    if ($request->hasFile('image')) {

        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $validated['image'] = $request->file('image')->store('products', 'public');
    }

    if (isset($validated['name'])) {
        $validated['slug'] = Str::slug($validated['name']);
    }

    $product->update($validated);

    return $this->success(
        new ProductResource($product->fresh()),
        'Product updated successfully.'
    );
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

       return $this->success(
    null,
    'Product deleted successfully.'
        );
    }
   
}
