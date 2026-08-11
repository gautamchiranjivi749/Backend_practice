<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:cod,esewa',
        ]);

        $user = $request->user();

        $cartItems = Cart::with([
            'product',
            'productVariant'
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
            $orderItems = [];

            foreach ($cartItems as $cartItem) {

                $product = $cartItem->product;
                $variant = $cartItem->productVariant;

                /*
                |--------------------------------------------------------------------------
                | Check Product
                |--------------------------------------------------------------------------
                */

                if (!$product || !$product->status) {
                    throw new \Exception(
                        "Product {$cartItem->product_id} is unavailable."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Determine Price and Stock
                |--------------------------------------------------------------------------
                */

                if ($variant) {

                    if (!$variant->status) {
                        throw new \Exception(
                            "Selected product variant is unavailable."
                        );
                    }

                    $price = $variant->price ?? $product->price;
                    $stock = $variant->stock;

                } else {

                    $price = $product->price;
                    $stock = $product->stock;
                }

                /*
                |--------------------------------------------------------------------------
                | Check Stock
                |--------------------------------------------------------------------------
                */

                if ($stock < $cartItem->quantity) {

                    throw new \Exception(
                        "Insufficient stock for {$product->name}."
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Calculate Subtotal
                |--------------------------------------------------------------------------
                */

                $subtotal = $price * $cartItem->quantity;

                $total += $subtotal;

                /*
                |--------------------------------------------------------------------------
                | Variant Name
                |--------------------------------------------------------------------------
                */

                $variantName = null;

                if ($variant) {

                    $parts = [];

                    if ($variant->color) {
                        $parts[] = $variant->color;
                    }

                    if ($variant->size) {
                        $parts[] = $variant->size;
                    }

                    $variantName = implode(' / ', $parts);
                }

                $orderItems[] = [
                    'product_id' => $product->id,

                    'product_variant_id' =>
                        $variant?->id,

                    'product_name' =>
                        $product->name,

                    'variant_name' =>
                        $variantName,

                    'quantity' =>
                        $cartItem->quantity,

                    'price' =>
                        $price,

                    'subtotal' =>
                        $subtotal,
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Create Order
            |--------------------------------------------------------------------------
            */

            $order = Order::create([

                'user_id' =>
                    $user->id,

                'order_number' =>
                    'ORD-' . strtoupper(Str::random(10)),

                'total_amount' =>
                    $total,

                'status' =>
                    'Pending',

                'payment_method' =>
                    $request->payment_method,

                'payment_status' =>
                    'unpaid',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create Order Items + Reduce Stock
            |--------------------------------------------------------------------------
            */

            foreach ($cartItems as $index => $cartItem) {

                $item = $orderItems[$index];

                OrderItem::create([
                    'order_id' =>
                        $order->id,

                    'product_id' =>
                        $item['product_id'],

                    'product_variant_id' =>
                        $item['product_variant_id'],

                    'product_name' =>
                        $item['product_name'],

                    'variant_name' =>
                        $item['variant_name'],

                    'quantity' =>
                        $item['quantity'],

                    'price' =>
                        $item['price'],

                    'subtotal' =>
                        $item['subtotal'],
                ]);

                /*
                |--------------------------------------------------------------------------
                | Reduce Stock
                |--------------------------------------------------------------------------
                */

                if ($cartItem->productVariant) {

                    $cartItem->productVariant->decrement(
                        'stock',
                        $cartItem->quantity
                    );

                } else {

                    $cartItem->product->decrement(
                        'stock',
                        $cartItem->quantity
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Clear Cart
            |--------------------------------------------------------------------------
            */

            Cart::where('user_id', $user->id)->delete();

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Return Order
            |--------------------------------------------------------------------------
            */

            $order->load('items');

            return response()->json([

                'success' => true,

                'message' =>
                    'Order placed successfully.',

                'order' => $order,

            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([

                'success' => false,

                'message' =>
                    $e->getMessage(),

            ], 400);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Customer Orders
    |--------------------------------------------------------------------------
    */

    public function myOrders(Request $request)
    {
        $orders = Order::with('items')
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

            'data' => $orders,

        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Single Order
    |--------------------------------------------------------------------------
    */

    public function show(Request $request, $id)
    {
        $order = Order::with('items')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json([

            'success' => true,

            'data' => $order,

        ]);
    }
}