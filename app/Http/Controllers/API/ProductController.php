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
use Intervention\Image\Laravel\Facades\Image;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * Display products.
     */
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->where('status', 1);

        if ($request->filled('search')) {
            $products->where(
                'name',
                'like',
                '%' . $request->search . '%'
            );
        }

        if ($request->filled('category')) {
            $products->where(
                'category_id',
                $request->category
            );
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
            ProductResource::collection(
                $products->get()
            ),
            'Products fetched successfully.'
        );
    }

    /**
     * Store product.
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;

        /*
        |--------------------------------------------------------------------------
        | Upload and optimize image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            // Generate unique filename
            $fileName = time() . '_' . uniqid() . '.webp';

            // Resize and compress
            $resizedImage = Image::read($image)
                ->cover(400, 400)
                ->toWebp(80);

            // Save optimized image
            Storage::disk('public')->put(
                'products/' . $fileName,
                $resizedImage
            );

            $imagePath = 'products/' . $fileName;
        }

        /*
        |--------------------------------------------------------------------------
        | Create product
        |--------------------------------------------------------------------------
        */

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

    /**
     * Display product.
     */
    public function show(Product $product)
    {
        return $this->success(
            new ProductResource($product),
            'Product fetched successfully.'
        );
    }

    /**
     * Update product.
     */
    public function update(
        UpdateProductRequest $request,
        $id
    ) {
        $product = Product::findOrFail($id);

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Replace product image
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('image')) {

            // Delete old image
            if (
                $product->image &&
                Storage::disk('public')->exists($product->image)
            ) {
                Storage::disk('public')
                    ->delete($product->image);
            }

            $image = $request->file('image');

            // Generate new filename
            $fileName = time() . '_' . uniqid() . '.webp';

            // Resize and compress
            $resizedImage = Image::read($image)
                ->cover(400, 400)
                ->toWebp(80);

            // Save optimized image
            Storage::disk('public')->put(
                'products/' . $fileName,
                $resizedImage
            );

            $validated['image'] = 'products/' . $fileName;
        }

        /*
        |--------------------------------------------------------------------------
        | Update slug when name changes
        |--------------------------------------------------------------------------
        */

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug(
                $validated['name']
            );
        }

        $product->update($validated);

        return $this->success(
            new ProductResource($product->fresh()),
            'Product updated successfully.'
        );
    }

    /**
     * Delete product.
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Delete product image
        |--------------------------------------------------------------------------
        */

        if (
            $product->image &&
            Storage::disk('public')->exists($product->image)
        ) {
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