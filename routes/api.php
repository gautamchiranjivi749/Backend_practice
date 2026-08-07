<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\testController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\admin\DashboardController;
use App\Http\Controllers\API\admin\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\PaymentController;
use App\Models\Category;
use App\Models\User;
use App\Models\Cart;




// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




Route::Post('/register',[AuthController::class,'register']);
Route::Post('/login',[AuthController::class,'login']);

//AUTH ROUTE 

Route::middleware('auth:sanctum')->group(function (){
    Route::Post('/logout',[AuthController::class,'logout']);
    Route::get('/user',[AuthController::class,'user']);
});





Route::get('/categories',function(){
     return Category::where('status', true)->get();

});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

//ADMIN DASHBOARD ROUTE

Route::middleware(['auth:sanctum','admin'])->prefix('admin')->group(function(){

 Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('/products', ProductController::class);
    Route::apiResource('/categories', CategoryController::class);


    Route::get('/cart', [CartController::class, 'index']);

    Route::post('/cart', [CartController::class, 'store']);

    Route::put('/cart/{id}', [CartController::class, 'update']);

    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    //route for checkout
    Route::post('/checkout', [OrderController::class, 'checkout']);

    Route::get('/orders', [OrderController::class, 'myOrders']);


    //wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    //payment routes
    Route::post('/payment/esewa/{order}',[PaymentController::class,'pay']
    );

    Route::get('/payment/success',[PaymentController::class,'success']
    );

    Route::get('/payment/failure',[PaymentController::class,'failure']
    );

    Route::post('/payment/esewa/{order}', [PaymentController::class, 'pay']);

    Route::post('/payment/esewa/{order}', [PaymentController::class,'pay']);
});
