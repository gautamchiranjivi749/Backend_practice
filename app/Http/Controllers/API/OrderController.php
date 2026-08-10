<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Checkout
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cod,esewa',
        ]);

        $user = $request->user();

        $cartItems = Cart::with([
            'product',
            'variant'
        ])
        ->where('user_id', $user->id)
        ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty.'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $total = 0;

            /*
            |--------------------------------------------------------------------------
            | STEP 1: Validate stock
            |--------------------------------------------------------------------------
            */

            foreach ($cartItems as $item) {

                if ($item->variant) {

                    $variant = ProductVariant::where(
                        'id',
                        $item->variant->id
                    )
                    ->lockForUpdate()
                    ->first();

                    if (!$variant) {
                        throw new \Exception(
                            'Product variant not found.'
                        );
                    }

                    if ($variant->stock < $item->quantity) {

                        throw new \Exception(
                            "Insufficient stock for {$item->product->name}."
                        );
                    }

                    $price = $variant->price
                        ?? $item->product->price;

                } else {

                    $product = Product::where(
                        'id',
                        $item->product_id
                    )
                    ->lockForUpdate()
                    ->first();

                    if (!$product) {
                        throw new \Exception(
                            'Product not found.'
                        );
                    }

                    if ($product->stock < $item->quantity) {

                        throw new \Exception(
                            "Insufficient stock for {$product->name}."
                        );
                    }

                    $price = $product->price;
                }

                $total +=
                    $price * $item->quantity;
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 2: Create Order
            |--------------------------------------------------------------------------
            */

            $order = Order::create([
                'user_id' => $user->id,

                'order_number' =>
                    'ORD-' . strtoupper(
                        Str::random(10)
                    ),

                'total_amount' => $total,

                'status' => 'Pending',

                'payment_method' =>
                    $request->payment_method,

                'payment_status' => 'unpaid',
            ]);

            /*
            |--------------------------------------------------------------------------
            | STEP 3: Create Order Items
            |--------------------------------------------------------------------------
            */

            foreach ($cartItems as $item) {

                $product = Product::findOrFail(
                    $item->product_id
                );

                /*
                |--------------------------------------------------------------------------
                | Variant Product
                |--------------------------------------------------------------------------
                */

                if ($item->variant) {

                    $variant = ProductVariant::where(
                        'id',
                        $item->variant->id
                    )
                    ->lockForUpdate()
                    ->first();

                    $price =
                        $variant->price
                        ?? $product->price;

                    $variantName =
                        collect([
                            $variant->color ?? null,
                            $variant->size ?? null,
                        ])
                        ->filter()
                        ->implode(' / ');

                    $variant->decrement(
                        'stock',
                        $item->quantity
                    );

                }

                /*
                |--------------------------------------------------------------------------
                | Normal Product
                |--------------------------------------------------------------------------
                */

                else {

                    $product = Product::where(
                        'id',
                        $item->product_id
                    )
                    ->lockForUpdate()
                    ->first();

                    $price =
                        $product->price;

                    $variantName = null;

                    $product->decrement(
                        'stock',
                        $item->quantity
                    );
                }

                $subtotal =
                    $price * $item->quantity;

                OrderItem::create([
                    'order_id' =>
                        $order->id,

                    'product_id' =>
                        $product->id,

                    'product_variant_id' =>
                        $item->variant?->id,

                    'product_name' =>
                        $product->name,

                    'variant_name' =>
                        $variantName,

                    'quantity' =>
                        $item->quantity,

                    'price' =>
                        $price,

                    'subtotal' =>
                        $subtotal,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | STEP 4: Clear Cart
            |--------------------------------------------------------------------------
            */

            Cart::where(
                'user_id',
                $user->id
            )->delete();

            DB::commit();

            return response()->json([
                'success' => true,

                'message' =>
                    'Order placed successfully.',

                'order' =>
                    $order->load([
                        'items.product',
                        'items.variant'
                    ])
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Customer order history
     */
    public function myOrders(Request $request)
    {
        $orders = Order::with([
            'items.product',
            'items.variant',
            'payment'
        ])
        ->where(
            'user_id',
            $request->user()->id
        )
        ->latest()
        ->get();

        return response()->json([
            'success' => true,
            'message' =>
                'Orders fetched successfully.',
            'data' => $orders
        ]);
    }

    /**
     * Show one customer order
     */
    public function show(
        Request $request,
        $id
    ) {
        $order = Order::with([
            'items.product',
            'items.variant',
            'payment'
        ])
        ->where(
            'user_id',
            $request->user()->id
        )
        ->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' =>
                'Order fetched successfully.',
            'data' => $order
        ]);
    }
}