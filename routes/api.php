<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceReviewController;
use App\Http\Controllers\UserController;
use App\Models\ProductReview;
use Illuminate\Support\Facades\Route;

//Authentication Route
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

//registration Confirmation
Route::post('/auth/registration-confirm', [AuthController::class, 'verifyCode']);
Route::post('/auth/resend-registration-confirm', [AuthController::class, 'resendCode']);

//forget & reset password
//forget password & resend forget Code
Route::post('/auth/forget-password', [AuthController::class, 'sendResetPassword']);

//check resent password status
Route::post('/auth/verify-reset-password', [AuthController::class, 'verifyResetPassword']);

//reset password
Route::post('/auth/reset-password', [AuthController::class, 'updatePassword']);


Route::middleware('auth:sanctum')->group(function () {

    // Profile
    // Profile creation after registration
    Route::post('/user/{id}/profile', [UserController::class, 'store']);
    //profile update without password
    Route::put('/user/{id}/profile', [UserController::class, 'update']);
    // profile update only password
    Route::put('/user/{id}/password', [UserController::class, 'updatePassword']);
    //logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    //home page api
    //both top stylist and top product fetching from this api
    Route::get('/', [HomeController::class, 'index']);


    Route::get('/providers', [HomeController::class, 'providers']);
    Route::get('/trending-stylist', [HomeController::class, 'trendingStylist']);
    Route::get('/recent-products', [HomeController::class, 'recentProducts']);
    // Route::get('/popular-stylist', [HomeController::class, 'popularStylist']); --pending
    // Route::get('/near-by', [HomeController::class, 'nearBy']); --pending

    // All Services
    Route::get('/vendor/{vendor_id}/service', [ServiceController::class, 'index']);

    // Single Service with their reviews and vendor
    Route::get('/vendor/{vendor_id}/service/{id}', [ServiceController::class, 'show']);


    // store service
    Route::post('/vendor/{vendor_id}/service', [ServiceController::class, 'store']);
    // update service
    Route::put('/vendor/{vendor_id}/service', [ServiceController::class, 'update']);
    //delete service
    Route::delete('/vendor/{vendor_id}/service/{service_id}', [ServiceController::class, 'destroy']);

    //book appointment
    Route::post('/book-appointment/{user_id}', [AppointmentController::class, 'bookAppointment']);

    //get my appointment user route
    //status confirmed , canceled, pending
    Route::get('/appointments/{user_id}/{status?}', [AppointmentController::class, 'getAppointmentsByStatus']);

    //appointment route for vendor
    Route::get('/vendor/{vendor_id}/appointments/{status?}', [AppointmentController::class, 'vendorAppointment']);

    // Update appointment status from vendor side
    Route::patch('/appointments/{appointment_id}/status', [AppointmentController::class, 'updateAppointmentStatus']);

    // chat route
    Route::get('/conversations/{userId}', [ChatController::class, 'getConversations']);
    Route::get('/messages/{conversationId}', [ChatController::class, 'getMessages']);
    Route::post('/send-message', [ChatController::class, 'sendMessage']);

    // show review , store review , delete review
    // Route::apiResource('/service/{id}/reviews', ServiceReviewController::class);
    // get all review of individual Service
    Route::get('/service/{id}/reviews', [ServiceReviewController::class, 'index']);
    //store a review
    Route::post('/service/{id}/reviews', [ServiceReviewController::class, 'store']);
    //delete a review
    Route::delete('/service/{id}/reviews/{review_id}', [ServiceReviewController::class, 'destroy']);

    //products
    Route::get('/products/{vendor_id?}', [ProductController::class, 'index']);
    Route::post('/vendor/{vendor_id}/product', [ProductController::class, 'store']);
    Route::post('product/{product_id}', [ProductController::class, 'show']);
    Route::put('/vendor/{vendor_id}/product/{product_id}', [ProductController::class, 'update']);
    Route::delete('vendor/{vendor_id}/product/{product_id}', [ProductController::class, 'destroy']);

    //product Review
    //get All Reviews
    Route::get('/product/{product_id}/reviews', [ProductReviewController::class, 'index']);
    //submit product review
    Route::post('/product/{product_id}/reviews', [ProductReviewController::class, 'store']);
    //delete product review
    Route::delete('/product/{product_id}/reviews/{review_id}', [ProductReviewController::class, 'destroy']);
});
