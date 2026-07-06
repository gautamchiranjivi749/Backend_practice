<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function index(Request $request)
{
    $cart = Cart::with('product')
        ->where('user_id', $request->user()->id)
        ->get();

    return response()->json($cart);
}
    public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1'
    ]);

    $cart = Cart::where('user_id', auth()->id())
        ->where('product_id', $request->product_id)
        ->first();

    if ($cart) {

        $cart->quantity += $request->quantity;
        $cart->save();

    } else {

        $cart = Cart::create([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
            'quantity' => $request->quantity
        ]);

    }

    return response()->json([
        'message' => 'Added to cart',
        'data' => $cart
    ]);
}
public function update(Request $request, $id)
{
    $request->validate([
        'quantity' => 'required|integer|min:1'
    ]);

    $cart = Cart::where('user_id', auth()->id())
        ->findOrFail($id);

    $cart->update([
        'quantity' => $request->quantity
    ]);

    return response()->json([
        'message' => 'Cart updated',
        'data' => $cart
    ]);
}
public function destroy($id)
{
    $cart = Cart::where('user_id', auth()->id())
        ->findOrFail($id);

    $cart->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'Item removed'
    ]);
}
}
