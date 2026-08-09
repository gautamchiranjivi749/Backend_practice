<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    use ApiResponse;

    /**
     * Display inventory.
     */
    public function index(Request $request)
    {
        $products = Product::with([
            'category',
            'brand',
            'variants'
        ]);

        if ($request->filled('search')) {
            $products->where(
                'name',
                'like',
                '%' . $request->search . '%'
            );
        }

        if ($request->filled('category_id')) {
            $products->where(
                'category_id',
                $request->category_id
            );
        }

        if ($request->filled('low_stock')) {
            $products->where(
                'stock',
                '<=',
                $request->low_stock
            );
        }

        return $this->success(
            $products->latest()->paginate(10),
            'Inventory fetched successfully.'
        );
    }

    /**
     * Increase product stock.
     */
    public function addStock(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($productId);

        $product->increment(
            'stock',
            $request->quantity
        );

        return $this->success(
            $product->fresh(),
            'Product stock increased successfully.'
        );
    }

    /**
     * Reduce product stock.
     */
    public function removeStock(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($productId);

        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock.',
            ], 422);
        }

        $product->decrement(
            'stock',
            $request->quantity
        );

        return $this->success(
            $product->fresh(),
            'Product stock reduced successfully.'
        );
    }

    /**
     * Set product stock.
     */
    public function setStock(Request $request, $productId)
    {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($productId);

        $product->update([
            'stock' => $request->stock,
        ]);

        return $this->success(
            $product->fresh(),
            'Product stock updated successfully.'
        );
    }

    /**
     * Increase variant stock.
     */
    public function addVariantStock(
        Request $request,
        $variantId
    ) {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($variantId);

        $variant->increment(
            'stock',
            $request->quantity
        );

        return $this->success(
            $variant->fresh()->load('product'),
            'Variant stock increased successfully.'
        );
    }

    /**
     * Reduce variant stock.
     */
    public function removeVariantStock(
        Request $request,
        $variantId
    ) {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $variant = ProductVariant::findOrFail($variantId);

        if ($variant->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient variant stock.',
            ], 422);
        }

        $variant->decrement(
            'stock',
            $request->quantity
        );

        return $this->success(
            $variant->fresh()->load('product'),
            'Variant stock reduced successfully.'
        );
    }

    /**
     * Set variant stock.
     */
    public function setVariantStock(
        Request $request,
        $variantId
    ) {
        $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $variant = ProductVariant::findOrFail($variantId);

        $variant->update([
            'stock' => $request->stock,
        ]);

        return $this->success(
            $variant->fresh()->load('product'),
            'Variant stock updated successfully.'
        );
    }

    /**
     * Low stock products.
     */
   public function lowStock(Request $request)
{
    $threshold = (int) $request->input('threshold', 10);

    $products = Product::with([
        'category',
        'brand',
        'variants'
    ])
    ->where('stock', '>', 0)
    ->where('stock', '<=', $threshold)
    ->get();

    $variants = ProductVariant::with('product')
        ->where('stock', '>', 0)
        ->where('stock', '<=', $threshold)
        ->get();

    return $this->success(
        [
            'products' => $products,
            'variants' => $variants,
        ],
        'Low stock inventory fetched successfully.'
    );
}

    /**
     * Out of stock products.
     */
   public function outOfStock()
{
    $products = Product::with([
        'category',
        'brand',
        'variants'
    ])
    ->where('stock', 0)
    ->get();

    $variants = ProductVariant::with('product')
        ->where('stock', 0)
        ->get();

    return $this->success(
        [
            'products' => $products,
            'variants' => $variants,
        ],
        'Out of stock inventory fetched successfully.'
    );
}
}
