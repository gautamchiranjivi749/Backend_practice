<?php

namespace App\Http\Controllers\API\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
// use App\Models\Product;
// use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
         return response()->json([
            'total_users' => User::count(),
            // 'total_products' => Product::count(),
            // 'total_orders' => Order::count(),

            // 'recent_orders' => Order::latest()->take(5)->get(),

            'message' => 'Admin dashboard data loaded successfully'
        ]);
    }
}
