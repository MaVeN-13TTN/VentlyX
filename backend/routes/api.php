<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\TicketTypeController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\StripeWebhookController;
use App\Http\Controllers\API\EventAnalyticsController;
use App\Http\Controllers\API\BookingTransferController;
use App\Http\Controllers\API\PaymentAnalyticsController;
use App\Http\Controllers\API\TwoFactorController;
use App\Http\Controllers\API\MPesaPaymentController;
use App\Http\Controllers\API\UserManagementController;
use App\Http\Controllers\API\CheckInController;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

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

// API root - provides basic API information
Route::get('/', function () {
    return response()->json([
        'name' => 'VentlyX API',
        'version' => 'v1',
        'status' => 'active',
        'documentation' => '/docs/api',
        'timestamp' => now()->toIso8601String()
    ]);
});

// Stripe webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// M-Pesa callback webhook (public)
Route::post('/mpesa/callback', [MPesaPaymentController::class, 'handleCallback']);

// All API v1 routes
Route::prefix('v1')->group(function () {
    // Auth routes with specific rate limiting
    Route::middleware('throttle:auth')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            // Password Reset Routes
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        });
    });

    // Public event and ticket routes with caching
    Route::middleware('api-cache')->group(function () {
        Route::get('/events', [EventController::class, 'index']);
        Route::get('/events/{id}', [EventController::class, 'show']);
        Route::get('/events/{eventId}/ticket-types', [TicketTypeController::class, 'index']);
        Route::get('/events/{eventId}/ticket-types/availability', [TicketTypeController::class, 'checkAvailability']);
        Route::get('/events/{eventId}/ticket-types/{id}', [TicketTypeController::class, 'show']);
    });

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });
        Route::get('/profile', [AuthController::class, 'profile']);

        // 2FA routes
        Route::prefix('2fa')->group(function () {
            Route::post('/enable', [TwoFactorController::class, 'enable']);
            Route::post('/confirm', [TwoFactorController::class, 'confirm']);
            Route::post('/disable', [TwoFactorController::class, 'disable']);
            Route::post('/verify', [TwoFactorController::class, 'verify']);
        });

        // Notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread', [NotificationController::class, 'unread']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            Route::delete('/all', [NotificationController::class, 'destroyAll']);
            Route::get('/preferences', [NotificationController::class, 'getPreferences']);
            Route::put('/preferences', [NotificationController::class, 'updatePreferences']);
        });

        // Booking routes
        Route::middleware('throttle:bookings')->group(function () {
            Route::get('/bookings', [BookingController::class, 'index']);
            Route::post('/bookings', [BookingController::class, 'store']);
            Route::get('/bookings/{id}', [BookingController::class, 'show']);
            Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
            Route::get('/bookings/{id}/qr-code', [BookingController::class, 'getQrCode']);
            Route::post('/bookings/validate-qr', [BookingController::class, 'validateQrCode']);

            // Add route for check-in to match test expectations
            Route::post('/bookings/{id}/check-in', [CheckInController::class, 'checkIn']);

            // Transfer routes
            Route::post('/bookings/{id}/transfer/initiate', [BookingTransferController::class, 'initiateTransfer']);
            Route::post('/bookings/transfer/accept', [BookingTransferController::class, 'acceptTransfer']);
            Route::delete('/bookings/{id}/transfer', [BookingTransferController::class, 'cancelTransfer']);
        });

        // Payment routes with rate limiting
        Route::middleware('throttle:payments')->group(function () {
            Route::post('/payments/process', [PaymentController::class, 'processPayment']);
            // M-Pesa payment routes
            Route::post('/payments/mpesa/initiate', [MPesaPaymentController::class, 'initiatePayment']);
            Route::post('/payments/mpesa/check-status', [MPesaPaymentController::class, 'checkStatus']);
        });

        // Organizer routes
        Route::middleware(['auth:sanctum', 'organizer'])->group(function () {
            // Event management with rate limiting
            Route::middleware('throttle:event-creation')->group(function () {
                Route::post('/events', [EventController::class, 'store']);
                Route::put('/events/{id}', [EventController::class, 'update']);
                Route::delete('/events/{id}', [EventController::class, 'destroy']);
                Route::post('/events/{id}/image', [EventController::class, 'uploadImage']);
            });
            Route::get('/my-events', [EventController::class, 'myEvents']);

            // Enhanced check-in management system
            Route::prefix('check-in')->group(function () {
                Route::post('/bookings/{id}', [CheckInController::class, 'checkIn']);
                Route::get('/events/{eventId}/attendees', [CheckInController::class, 'getAttendees']);
                Route::post('/verify', [CheckInController::class, 'verifyQrCode']);
                Route::post('/bookings/{id}/undo', [CheckInController::class, 'undoCheckIn']);
                Route::get('/events/{eventId}/stats', [CheckInController::class, 'getCheckInStats']);
                Route::post('/bulk', [CheckInController::class, 'bulkCheckIn']);
            });

            // Ticket type management including bulk operations
            Route::post('/events/{eventId}/ticket-types', [TicketTypeController::class, 'store']);
            Route::post('/events/{eventId}/ticket-types/bulk', [TicketTypeController::class, 'bulkStore']);
            Route::put('/events/{eventId}/ticket-types/bulk', [TicketTypeController::class, 'bulkUpdate']);
            Route::delete('/events/{eventId}/ticket-types/bulk', [TicketTypeController::class, 'bulkDelete']);
            Route::put('/events/{eventId}/ticket-types/{id}', [TicketTypeController::class, 'update']);
            Route::delete('/events/{eventId}/ticket-types/{id}', [TicketTypeController::class, 'destroy']);

            // Organizer routes with analytics access
            Route::get('/analytics/my-events/{id}', [EventAnalyticsController::class, 'getEventStats']);
            Route::get('/analytics/organizer-dashboard', [EventAnalyticsController::class, 'getOrganizerStats']);
        });

        // Admin routes
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            Route::patch('/events/{id}/toggle-featured', [EventController::class, 'toggleFeatured']);
            Route::post('/payments/{payment_id}/refund', [PaymentController::class, 'processRefund']);

            // Analytics routes
            Route::get('/analytics/events/{id}', [EventAnalyticsController::class, 'getEventStats']);
            Route::get('/analytics/overall', [EventAnalyticsController::class, 'getOverallStats']);

            // Payment analytics routes
            Route::get('/analytics/payments', [PaymentAnalyticsController::class, 'getPaymentStats']);
            Route::get('/analytics/payments/trends', [PaymentAnalyticsController::class, 'getPaymentMethodTrends']);
            Route::get('/analytics/payments/failures', [PaymentAnalyticsController::class, 'getFailureAnalysis']);

            // User management routes
            Route::get('/users', [UserManagementController::class, 'index']);
            Route::post('/users', [UserManagementController::class, 'store']);
            Route::get('/users/{id}', [UserManagementController::class, 'show']);
            Route::put('/users/{id}', [UserManagementController::class, 'update']);
            Route::delete('/users/{id}', [UserManagementController::class, 'destroy']);
            Route::get('/users-statistics', [UserManagementController::class, 'statistics']);
            Route::get('/roles', [UserManagementController::class, 'roles']);
            Route::put('/users/{id}/roles', [UserManagementController::class, 'updateRoles']);
            Route::patch('/users/{id}/toggle-active', [UserManagementController::class, 'toggleActive']);
        });
    });
});
