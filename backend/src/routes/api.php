<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController; 

/*
|--------------------------------------------------------------------------
| Public Routes (No Login Required)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// --- FORGOT PASSWORD ROUTES ---
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Product Public Routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Product Management (Admin Panel Routes)
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{id}', [ProductController::class, 'update']);
Route::delete('/products/{id}', [ProductController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Login Required)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- NEW: CHANGE PASSWORD & PROFILE (LOGGED IN) ---
    Route::post('/user/request-password-change', [AuthController::class, 'requestPasswordChange']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']); // <--- ADDED THIS

    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);

    // Checkout & Orders
    Route::post('/checkout', [CheckoutController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']); // Client Order History
    
    // --- CANCEL ORDER ---
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']); 

    // --- ADMIN MANAGEMENT & ANALYTICS ---
    Route::get('/admin/orders', [OrderController::class, 'getAllOrders']); // View all orders
    Route::put('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']); // Update status
    Route::get('/admin/analytics', [OrderController::class, 'getSalesAnalytics']); // Sales Report
    
    // --- ADMIN CUSTOMER MANAGEMENT ---
    Route::get('/admin/users', [UserController::class, 'index']); // List all customers
    Route::put('/admin/users/{id}/block', [UserController::class, 'toggleBlock']); // Block/Unblock User
});