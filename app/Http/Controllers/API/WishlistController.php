<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wishlist;
use App\Models\Product;


class WishlistController extends Controller
{
      public function index(Request $request)
    {
        return Wishlist::with('product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();
    }

    // Add to Wishlist
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id
        ]);

        return response()->json([
            'message' => 'Product added to wishlist.',
            'wishlist' => $wishlist
        ]);
    }

    // Remove from Wishlist
    public function destroy(Request $request, $id)
    {
        $wishlist = Wishlist::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$wishlist) {
            return response()->json([
                'message' => 'Wishlist item not found.'
            ], 404);
        }

        $wishlist->delete();

        return response()->json([
            'message' => 'Removed from wishlist.'
        ]);
    }
}
