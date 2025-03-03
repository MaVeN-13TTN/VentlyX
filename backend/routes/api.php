<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\TicketTypeController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\StripeWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Stripe webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// Auth routes with specific rate limiting
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public event routes with caching
Route::middleware('cache.response')->group(function () {
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::get('/events/{eventId}/ticket-types', [TicketTypeController::class, 'index']);
    Route::get('/events/{eventId}/ticket-types/{id}', [TicketTypeController::class, 'show']);
    Route::get('/events/{eventId}/ticket-types/availability', [TicketTypeController::class, 'checkAvailability']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    
    // User booking routes with rate limiting
    Route::middleware('throttle:bookings')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{id}', [BookingController::class, 'show']);
        Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
    });

    // Payment routes with rate limiting
    Route::middleware('throttle:payments')->group(function () {
        Route::post('/payments/process', [PaymentController::class, 'processPayment']);
    });

    // Organizer routes
    Route::middleware(['organizer'])->group(function () {
        // Event management with rate limiting
        Route::middleware('throttle:event-creation')->group(function () {
            Route::post('/events', [EventController::class, 'store']);
            Route::put('/events/{id}', [EventController::class, 'update']);
            Route::delete('/events/{id}', [EventController::class, 'destroy']);
            Route::post('/events/{id}/image', [EventController::class, 'uploadImage']);
        });

        Route::get('/my-events', [EventController::class, 'myEvents']);
        
        // Ticket type management
        Route::post('/events/{eventId}/ticket-types', [TicketTypeController::class, 'store']);
        Route::put('/events/{eventId}/ticket-types/{id}', [TicketTypeController::class, 'update']);
        Route::delete('/events/{eventId}/ticket-types/{id}', [TicketTypeController::class, 'destroy']);
        
        // Check-in management
        Route::post('/bookings/{id}/check-in', [BookingController::class, 'checkIn']);
    });
    
    // Admin routes
    Route::middleware('admin')->group(function () {
        Route::patch('/events/{id}/toggle-featured', [EventController::class, 'toggleFeatured']);
        Route::post('/payments/{payment_id}/refund', [PaymentController::class, 'processRefund']);
    });
});