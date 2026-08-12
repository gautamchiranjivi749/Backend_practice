<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Controllers
|--------------------------------------------------------------------------
*/

// Authentication
use App\Http\Controllers\API\AuthController;

// Public
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\BrandController;
use App\Http\Controllers\API\ProductVariantController;

// Customer
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\UserAddressController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\InventoryController;

// Admin
use App\Http\Controllers\API\AdminCustomerController;
use App\Http\Controllers\API\AdminOrderController;
use App\Http\Controllers\API\AdminPaymentController;
use App\Http\Controllers\API\AdminReviewController;
use App\Http\Controllers\API\AdminDashboardController;
use App\Http\Controllers\API\admin\DashboardController;





// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {

    Route::post('/register', [
        AuthController::class,
        'register'
    ]);

    Route::post('/login', [
        AuthController::class,
        'login'
    ]);

});

// Route::Post('/register',[AuthController::class,'register']);
// Route::Post('/login',[AuthController::class,'login']);

//AUTH ROUTE 

Route::middleware('auth:sanctum')->group(function (){
    Route::Post('/logout',[AuthController::class,'logout']);
    Route::get('/user',[AuthController::class,'user']);
});


Route::prefix('public')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    Route::get('/categories', [
        CategoryController::class,
        'index'
    ]);

    Route::get('/categories/{category}', [
        CategoryController::class,
        'show'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Brands
    |--------------------------------------------------------------------------
    */

    Route::get('/brands', [
        BrandController::class,
        'index'
    ]);

    Route::get('/brands/{brand}', [
        BrandController::class,
        'show'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    Route::get('/products', [
        ProductController::class,
        'index'
    ]);

    Route::get('/products/{product}', [
        ProductController::class,
        'show'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Product Variants
    |--------------------------------------------------------------------------
    */

    Route::get('/products/{product}/variants', [
        ProductVariantController::class,
        'productVariants'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Product Reviews
    |--------------------------------------------------------------------------
    */

    Route::get('/products/{product}/reviews', [
        ReviewController::class,
        'productReviews'
    ]);

});



// Route::get('/categories',function(){
//      return Category::where('status', true)->get();

// });

// Route::get('/products', [ProductController::class, 'index']);
// Route::get('/products/{product}', [ProductController::class, 'show']);
// Route::get('/products/{product}', [ProductController::class, 'show']);


Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post('/auth/logout', [
        AuthController::class,
        'logout'
    ]);

    Route::get('/auth/me', [
        AuthController::class,
        'me'
    ]);


    /*
    |--------------------------------------------------------------------------
    | CART
    |--------------------------------------------------------------------------
    */

    Route::get('/cart', [
        CartController::class,
        'index'
    ]);

    Route::post('/cart', [
        CartController::class,
        'store'
    ]);

    Route::put('/cart/{id}', [
        CartController::class,
        'update'
    ]);

    Route::delete('/cart/{id}', [
        CartController::class,
        'destroy'
    ]);


    /*
    |--------------------------------------------------------------------------
    | ORDERS
    |--------------------------------------------------------------------------
    */

    Route::post('/checkout', [
        OrderController::class,
        'checkout'
    ]);

    Route::get('/my-orders', [
        OrderController::class,
        'myOrders'
    ]);

    Route::get('/orders/{id}', [
        OrderController::class,
        'show'
    ]);


    /*
    |--------------------------------------------------------------------------
    | PAYMENTS
    |--------------------------------------------------------------------------
    */

    Route::post('/payment/esewa/{order}', [
        PaymentController::class,
        'pay'
    ]);

    Route::get('/payment/{order}', [
        PaymentController::class,
        'show'
    ]);


    /*
    |--------------------------------------------------------------------------
    | REVIEWS
    |--------------------------------------------------------------------------
    */

    Route::post('/products/{product}/reviews', [
        ReviewController::class,
        'store'
    ]);

    Route::get('/my-reviews', [
        ReviewController::class,
        'myReviews'
    ]);

    Route::put('/reviews/{id}', [
        ReviewController::class,
        'update'
    ]);

    Route::delete('/reviews/{id}', [
        ReviewController::class,
        'destroy'
    ]);

});

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
    // final api resource
});
