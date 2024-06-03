<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');

Route::post('/login', [UserController::class, 'login']);


Route::middleware(['auth:api'])->group(function () {
    
    //about us
    Route::get('/about-us', [AboutUsController::class, 'index']);
    Route::post('/about-us', [AboutUsController::class, 'store']);
    Route::get('/about-us/{id}', [AboutUsController::class, 'show']);

    
    Route::post('/about-us/{id}', [AboutUsController::class, 'update']);
    Route::patch('/about-us/{id}', [AboutUsController::class, 'update']);
    Route::delete('/about-us/{id}', [AboutUsController::class, 'destroy']);


});

