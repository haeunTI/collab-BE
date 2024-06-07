<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\OurServicesController;
use App\Http\Controllers\OurTeamsController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\TestimonyController;
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

    //contact-us
    Route::get('/contact-us', [ContactUsController::class, 'index']);
    Route::post('/contact-us', [ContactUsController::class, 'store']);
    Route::get('/contact-us/{id}', [ContactUsController::class, 'show']);

    
    Route::post('/contact-us/{id}', [ContactUsController::class, 'update']);
    Route::patch('/contact-us/{id}', [ContactUsController::class, 'update']);
    Route::delete('/contact-us/{id}', [ContactUsController::class, 'destroy']);


    //social-media
    Route::get('/social-media', [SocialMediaController::class, 'index']);
    Route::post('/social-media', [SocialMediaController::class, 'store']);


    //testimony
    Route::get('/testimony', [TestimonyController::class, 'index']);
    Route::post('/testimony', [TestimonyController::class, 'store']);
    Route::get('/testimony/{id}', [TestimonyController::class, 'show']);

    
    Route::post('/testimony/{id}', [TestimonyController::class, 'update']);
    Route::patch('/testimony/{id}', [TestimonyController::class, 'update']);
    Route::delete('/testimony/{id}', [TestimonyController::class, 'destroy']);

    //banner
    Route::get('/banner', [BannerController::class, 'index']);
    Route::post('/banner', [BannerController::class, 'store']);
    Route::get('/banner/{id}', [BannerController::class, 'show']);

    
    Route::post('/banner/{id}', [BannerController::class, 'update']);
    Route::patch('/banner/{id}', [BannerController::class, 'update']);
    Route::delete('/banner/{id}', [BannerController::class, 'destroy']);


    //our-teams
    Route::get('/our-teams', [OurTeamsController::class, 'index']);
    Route::post('/our-teams', [OurTeamsController::class, 'store']);
    Route::get('/our-teams/{id}', [OurTeamsController::class, 'show']);

    
    Route::post('/our-teams/{id}', [OurTeamsController::class, 'update']);
    Route::patch('/our-teams/{id}', [OurTeamsController::class, 'update']);
    Route::delete('/our-teams/{id}', [OurTeamsController::class, 'destroy']);



    
    //our-services
    Route::get('/our-services', [OurServicesController::class, 'index']);
    Route::post('/our-services', [OurServicesController::class, 'store']);
    Route::get('/our-services/{id}', [OurServicesController::class, 'show']);

    
    Route::post('/our-services/{id}', [OurServicesController::class, 'update']);
    Route::patch('/our-services/{id}', [OurServicesController::class, 'update']);
    Route::delete('/our-services/{id}', [OurServicesController::class, 'destroy']);


    //logout
    Route::get('/logout', [UserController::class, 'logout']);    

});

