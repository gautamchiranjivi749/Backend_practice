<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Http\Resources\ProductVariantResource;
use App\Models\ProductVariant;
use App\Traits\ApiResponse;

class ProductVariantController extends Controller
{
    use ApiResponse;

      /**
     * Display all variants.
     */
    public function index()
    {
        $variants = ProductVariant::with('product')
            ->latest()
            ->paginate(10);

        return $this->success(
            ProductVariantResource::collection($variants),
            'Product variants fetched successfully.'
        );
    }

    /**
     * Store variant.
     */
    public function store(StoreProductVariantRequest $request)
    {
        $variant = ProductVariant::create(
            $request->validated()
        );

        $variant->load('product');

        return $this->success(
            new ProductVariantResource($variant),
            'Product variant created successfully.'
        );
    }

    /**
     * Display variant.
     */
    public function show(ProductVariant $product_variant)
    {
        $product_variant->load('product');

        return $this->success(
            new ProductVariantResource($product_variant),
            'Product variant fetched successfully.'
        );
    }

    /**
     * Update variant.
     */
    public function update(
        UpdateProductVariantRequest $request,
        ProductVariant $product_variant
    ) {
        $product_variant->update(
            $request->validated()
        );

        $product_variant->load('product');

        return $this->success(
            new ProductVariantResource($product_variant->fresh()),
            'Product variant updated successfully.'
        );
    }

    /**
     * Delete variant.
     */
    public function destroy(ProductVariant $product_variant)
    {
        $product_variant->delete();

        return $this->success(
            null,
            'Product variant deleted successfully.'
        );
    }
}
