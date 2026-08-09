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
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\ProductVariantController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\UserAddressController;
use App\Models\Product;
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

    Route::apiResource('/brands', BrandController::class);
    
    Route::apiResource('/categories', CategoryController::class);

    Route::apiResource('/product-variants', ProductVariantController::class);

     Route::get(
        '/inventory/low-stock',
        [InventoryController::class, 'lowStock']
    );

    Route::get(
        '/inventory/out-of-stock',
        [InventoryController::class, 'outOfStock']
    );

  Route::get(
        '/inventory',
        [InventoryController::class, 'index']
    );

    Route::post(
        '/inventory/products/{productId}/add',
        [InventoryController::class, 'addStock']
    );

    Route::post(
        '/inventory/products/{productId}/remove',
        [InventoryController::class, 'removeStock']
    );

    Route::put(
        '/inventory/products/{productId}/stock',
        [InventoryController::class, 'setStock']
    );

    Route::post(
        '/inventory/variants/{variantId}/add',
        [InventoryController::class, 'addVariantStock']
    );

    Route::post(
        '/inventory/variants/{variantId}/remove',
        [InventoryController::class, 'removeVariantStock']
    );

    Route::put(
        '/inventory/variants/{variantId}/stock',
        [InventoryController::class, 'setVariantStock']
    );

    Route::get('/customers', [CustomerController::class, 'index']);

    Route::get('/customers/{id}', [CustomerController::class, 'show']);

    Route::put('/customers/{id}', [CustomerController::class, 'update']);

    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

    Route::Patch('/customers/{id}/status', [CustomerController::class, 'status']);
   

    Route::get('/cart', [CartController::class, 'index']);

    Route::post('/cart', [CartController::class, 'store']);

    Route::put('/cart/{id}', [CartController::class, 'update']);

    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    //route for checkout
    Route::post('/checkout', [OrderController::class, 'checkout']);

    Route::get('/orders', [OrderController::class, 'myOrders']);

     Route::get(
        '/addresses',
        [UserAddressController::class, 'index']
    );

    Route::post(
        '/addresses',
        [UserAddressController::class, 'store']
    );

    Route::get(
        '/addresses/{id}',
        [UserAddressController::class, 'show']
    );

    Route::put(
        '/addresses/{id}',
        [UserAddressController::class, 'update']
    );

    Route::delete(
        '/addresses/{id}',
        [UserAddressController::class, 'destroy']
    );

    Route::patch(
        '/addresses/{id}/default',
        [UserAddressController::class, 'setDefault']
    );


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
