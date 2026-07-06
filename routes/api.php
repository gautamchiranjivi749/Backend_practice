<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\testController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\admin\DashboardController;
use App\Http\Controllers\API\admin\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
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


//ADMIN DASHBOARD ROUTE

Route::middleware(['auth:sanctum','admin'])->prefix('admin')->group(function(){


    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('/products', ProductController::class);
    Route::apiResource('/categories', CategoryController::class);



});

Route::get('/categories',function(){
     return Category::where('status', true)->get();

});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);


Route::middleware(['auth:sanctum','admin'])->prefix('admin')->group(function(){
    Route::get('/cart', [CartController::class, 'index']);

    Route::post('/cart', [CartController::class, 'store']);

    Route::put('/cart/{id}', [CartController::class, 'update']);

    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

});

