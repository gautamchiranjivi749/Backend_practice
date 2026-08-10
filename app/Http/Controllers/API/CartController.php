<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;

class CartController extends Controller
{
    /**
     * Get customer's cart
     */
    public function index(Request $request)
    {
        $cart = Cart::with([
            'product',
            'variant'
        ])
        ->where(
            'user_id',
            $request->user()->id
        )
        ->get();

        return response()->json([
            'success' => true,
            'message' => 'Cart fetched successfully.',
            'data' => $cart
        ]);
    }

    /**
     * Add product to cart
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' =>
                'required|exists:products,id',

            'product_variant_id' =>
                'nullable|exists:product_variants,id',

            'quantity' =>
                'required|integer|min:1',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Get Product
        |--------------------------------------------------------------------------
        */

        $product = Product::findOrFail(
            $request->product_id
        );

        /*
        |--------------------------------------------------------------------------
        | Variant validation
        |--------------------------------------------------------------------------
        */

        $variant = null;

        if ($request->filled('product_variant_id')) {

            $variant = ProductVariant::where(
                'id',
                $request->product_variant_id
            )
            ->where(
                'product_id',
                $product->id
            )
            ->first();

            if (!$variant) {

                return response()->json([
                    'success' => false,
                    'message' =>
                        'Selected variant does not belong to this product.'
                ], 422);
            }

            /*
             * Check variant stock
             */
            if (
                $variant->stock <
                $request->quantity
            ) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Insufficient variant stock.'
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Normal product stock
        |--------------------------------------------------------------------------
        */

        else {

            if (
                $product->stock <
                $request->quantity
            ) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Insufficient product stock.'
                ], 422);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Find existing cart item
        |--------------------------------------------------------------------------
        */

        $cart = Cart::where(
            'user_id',
            auth()->id()
        )
        ->where(
            'product_id',
            $request->product_id
        )
        ->where(
            'product_variant_id',
            $request->product_variant_id
        )
        ->first();

        /*
        |--------------------------------------------------------------------------
        | Existing item
        |--------------------------------------------------------------------------
        */

        if ($cart) {

            $newQuantity =
                $cart->quantity +
                $request->quantity;

            /*
             * Check total quantity against stock
             */
            if ($variant) {

                if (
                    $variant->stock <
                    $newQuantity
                ) {
                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Requested quantity exceeds available variant stock.'
                    ], 422);
                }

            } else {

                if (
                    $product->stock <
                    $newQuantity
                ) {
                    return response()->json([
                        'success' => false,
                        'message' =>
                            'Requested quantity exceeds available product stock.'
                    ], 422);
                }
            }

            $cart->quantity =
                $newQuantity;

            $cart->save();

        }

        /*
        |--------------------------------------------------------------------------
        | New cart item
        |--------------------------------------------------------------------------
        */

        else {

            $cart = Cart::create([
                'user_id' =>
                    auth()->id(),

                'product_id' =>
                    $request->product_id,

                'product_variant_id' =>
                    $request->product_variant_id,

                'quantity' =>
                    $request->quantity,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Added to cart successfully.',
            'data' => $cart->load([
                'product',
                'variant'
            ])
        ], 201);
    }

    /**
     * Update cart quantity
     */
    public function update(
        Request $request,
        $id
    ) {
        $request->validate([
            'quantity' =>
                'required|integer|min:1'
        ]);

        $cart = Cart::where(
            'user_id',
            auth()->id()
        )
        ->findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Check stock
        |--------------------------------------------------------------------------
        */

        if ($cart->product_variant_id) {

            $variant =
                ProductVariant::findOrFail(
                    $cart->product_variant_id
                );

            if (
                $variant->stock <
                $request->quantity
            ) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Requested quantity exceeds variant stock.'
                ], 422);
            }

        } else {

            $product =
                Product::findOrFail(
                    $cart->product_id
                );

            if (
                $product->stock <
                $request->quantity
            ) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'Requested quantity exceeds product stock.'
                ], 422);
            }
        }

        $cart->update([
            'quantity' =>
                $request->quantity
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
            'data' => $cart->load([
                'product',
                'variant'
            ])
        ]);
    }

    /**
     * Remove cart item
     */
    public function destroy($id)
    {
        $cart = Cart::where(
            'user_id',
            auth()->id()
        )
        ->findOrFail($id);

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.'
        ]);
    }
}