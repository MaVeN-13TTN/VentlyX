<?php

/**
 * VentlyX Payment Gateway Manual Testing Script
 * 
 * This script helps test payment gateways manually.
 * Run this script with: php artisan tinker --execute="require 'tests/manual-payment-test.php';"
 */

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\TicketType;
use App\Models\User;
use App\Services\MPesaService;
use Illuminate\Support\Facades\Log;

// Helper function to print test results
function printResult($test, $result, $message = null)
{
    echo str_pad($test, 40, ' ') . ": ";
    if ($result) {
        echo "\033[32mPASSED\033[0m";
    } else {
        echo "\033[31mFAILED\033[0m";
    }
    if ($message) {
        echo " - $message";
    }
    echo "\n";
}

// Helper function to print section headers
function printSection($title)
{
    echo "\n\033[1;33m=== $title ===\033[0m\n";
}

// Check environment configuration
printSection("ENVIRONMENT CONFIGURATION CHECK");

// Check Stripe configuration
$stripeConfigured = !empty(config('services.stripe.key')) &&
    !empty(config('services.stripe.secret')) &&
    !empty(config('services.stripe.webhook.secret'));
printResult("Stripe Configuration", $stripeConfigured, $stripeConfigured ? "Keys found" : "Missing keys");

// Check M-Pesa configuration
$mpesaConfigured = !empty(config('services.mpesa.consumer_key')) &&
    !empty(config('services.mpesa.consumer_secret')) &&
    !empty(config('services.mpesa.shortcode')) &&
    !empty(config('services.mpesa.passkey')) &&
    !empty(config('services.mpesa.callback_url'));
printResult("M-Pesa Configuration", $mpesaConfigured, $mpesaConfigured ? "Keys found" : "Missing keys");

// Check PayPal configuration
$paypalConfigured = !empty(env('PAYPAL_CLIENT_ID')) &&
    !empty(env('PAYPAL_SECRET')) &&
    !empty(env('PAYPAL_MODE'));
printResult("PayPal Configuration", $paypalConfigured, $paypalConfigured ? "Keys found" : "Missing keys");

// Create test data
printSection("CREATING TEST DATA");

try {
    // Create test user if not exists
    $user = User::firstOrCreate(
        ['email' => 'test@ventlyx.com'],
        [
            'name' => 'Test User',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]
    );
    printResult("Create Test User", true, "ID: {$user->id}");

    // Create test event if not exists
    $event = Event::firstOrCreate(
        ['title' => 'Payment Test Event'],
        [
            'description' => 'This is a test event for payment testing',
            'start_time' => now()->addDays(7),
            'end_time' => now()->addDays(7)->addHours(3),
            'location' => 'Test Location',
            'organizer_id' => $user->id,
            'status' => 'published',
        ]
    );
    printResult("Create Test Event", true, "ID: {$event->id}");

    // Create test ticket type if not exists
    $ticketType = TicketType::firstOrCreate(
        [
            'event_id' => $event->id,
            'name' => 'Test Ticket'
        ],
        [
            'description' => 'Test ticket for payment testing',
            'price' => 1000, // $10.00
            'quantity' => 100,
            'tickets_remaining' => 100,
        ]
    );
    printResult("Create Test Ticket Type", true, "ID: {$ticketType->id}");

    // Create test booking
    $booking = Booking::create([
        'user_id' => $user->id,
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id,
        'quantity' => 1,
        'total_price' => $ticketType->price,
        'status' => 'pending',
        'payment_status' => 'pending',
    ]);
    printResult("Create Test Booking", true, "ID: {$booking->id}");
} catch (\Exception $e) {
    printResult("Create Test Data", false, $e->getMessage());
}

// Test Stripe integration
printSection("STRIPE INTEGRATION TEST");

if ($stripeConfigured) {
    try {
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Create a test payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => 1000, // $10.00 in cents
            'currency' => 'usd',
            'payment_method_types' => ['card'],
            'metadata' => [
                'booking_id' => $booking->id,
                'test' => true,
            ],
        ]);

        printResult("Create Stripe Payment Intent", true, "ID: {$paymentIntent->id}");

        echo "\nStripe Client Secret (for frontend): {$paymentIntent->client_secret}\n";
        echo "Use test card: 4242 4242 4242 4242, any future date, any CVC, any postal code\n";
    } catch (\Exception $e) {
        printResult("Stripe Integration", false, $e->getMessage());
    }
} else {
    printResult("Stripe Integration", false, "Stripe not configured");
}

// Test M-Pesa integration
printSection("M-PESA INTEGRATION TEST");

if ($mpesaConfigured) {
    try {
        $mpesaService = app(MPesaService::class);

        // Test access token generation
        $accessToken = $mpesaService->generateAccessToken();
        printResult("Generate M-Pesa Access Token", !empty($accessToken), !empty($accessToken) ? "Token received" : "Failed to get token");

        echo "\nTo test M-Pesa payment:\n";
        echo "1. Use the API endpoint: POST /api/v1/payments/mpesa/initiate\n";
        echo "2. With payload: { \"booking_id\": {$booking->id}, \"phone_number\": \"254708374149\" }\n";
        echo "3. For local testing, ensure ngrok is running and callback URL is set\n";
    } catch (\Exception $e) {
        printResult("M-Pesa Integration", false, $e->getMessage());
    }
} else {
    printResult("M-Pesa Integration", false, "M-Pesa not configured");
}

// Test PayPal integration
printSection("PAYPAL INTEGRATION TEST");

if ($paypalConfigured) {
    try {
        echo "\nTo test PayPal payment:\n";
        echo "1. Use the API endpoint: POST /api/v1/payments/process\n";
        echo "2. With payload: { \"booking_id\": {$booking->id}, \"payment_method\": \"paypal\", \"payment_token\": \"PAYPAL_ORDER_ID\", \"currency\": \"usd\" }\n";
        echo "3. The PayPal order ID should be created on the frontend using PayPal JavaScript SDK\n";
        echo "4. For sandbox testing, use PayPal sandbox accounts\n";
    } catch (\Exception $e) {
        printResult("PayPal Integration", false, $e->getMessage());
    }
} else {
    printResult("PayPal Integration", false, "PayPal not configured");
}

// Summary
printSection("TESTING SUMMARY");
echo "Booking ID for testing: {$booking->id}\n";
echo "User ID for testing: {$user->id}\n";
echo "Event ID for testing: {$event->id}\n";
echo "Ticket Type ID for testing: {$ticketType->id}\n\n";

echo "API Endpoints for Testing:\n";
echo "- Stripe Payment: POST /api/v1/payments/process\n";
echo "- M-Pesa Payment: POST /api/v1/payments/mpesa/initiate\n";
echo "- PayPal Payment: POST /api/v1/payments/process\n";
echo "- Check M-Pesa Status: POST /api/v1/payments/mpesa/check-status\n";
echo "- Process Refund: POST /api/v1/payments/{payment_id}/refund (Admin only)\n\n";

echo "For webhook testing with ngrok:\n";
echo "1. Run: ngrok http 8000\n";
echo "2. Update webhook URLs in respective dashboards with ngrok URL\n";
echo "3. For Stripe: {ngrok_url}/api/stripe/webhook\n";
echo "4. For M-Pesa: {ngrok_url}/api/mpesa/callback\n";
echo "5. For PayPal: {ngrok_url}/api/paypal/webhook (if implemented)\n";
