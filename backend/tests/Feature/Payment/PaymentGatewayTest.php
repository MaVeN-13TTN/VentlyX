<?php

namespace Tests\Feature\Payment;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\TicketType;
use App\Models\User;
use App\Services\MPesaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\CardException;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $event;
    protected $ticketType;
    protected $booking;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Create test event
        $this->event = Event::factory()->create([
            'title' => 'Test Event for Payment',
            'status' => 'published',
        ]);

        // Create ticket type
        $this->ticketType = TicketType::factory()->create([
            'event_id' => $this->event->id,
            'price' => 1000, // $10.00
            'quantity' => 100,
            'tickets_remaining' => 100,
        ]);

        // Create booking
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'event_id' => $this->event->id,
            'ticket_type_id' => $this->ticketType->id,
            'quantity' => 1,
            'total_price' => 1000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Test Stripe payment processing
     */
    public function test_stripe_payment_processing()
    {
        $this->markTestSkipped('Requires Stripe API keys to be configured');

        // Set Stripe API key
        Stripe::setApiKey(config('services.stripe.secret'));

        // Create a test payment method
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => 1000, // $10.00 in cents
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'metadata' => [
                    'booking_id' => $this->booking->id,
                    'test' => true,
                ],
            ]);

            // Confirm the payment with a test card
            $paymentIntent->confirm([
                'payment_method' => 'pm_card_visa', // Stripe test card
            ]);

            // Process the payment in our system
            $response = $this->actingAs($this->user)
                ->postJson('/api/v1/payments/process', [
                    'booking_id' => $this->booking->id,
                    'payment_method' => 'stripe',
                    'payment_token' => $paymentIntent->payment_method,
                    'currency' => 'usd',
                ]);

            $response->assertStatus(200)
                ->assertJsonPath('message', 'Payment processed successfully');

            // Check that the booking status was updated
            $this->assertEquals('confirmed', $this->booking->fresh()->status);
            $this->assertEquals('paid', $this->booking->fresh()->payment_status);

            // Check that a payment record was created
            $this->assertDatabaseHas('payments', [
                'booking_id' => $this->booking->id,
                'payment_method' => 'stripe',
                'status' => 'completed',
            ]);
        } catch (CardException $e) {
            $this->fail('Stripe payment failed: ' . $e->getMessage());
        }
    }

    /**
     * Test M-Pesa payment processing
     */
    public function test_mpesa_payment_processing()
    {
        $this->markTestSkipped('Requires M-Pesa API keys to be configured');

        // Mock the M-Pesa service response
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/v1/generate*' => Http::response([
                'access_token' => 'test_access_token',
                'expires_in' => '3599',
            ], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest' => Http::response([
                'MerchantRequestID' => '29115-34620561-1',
                'CheckoutRequestID' => 'ws_CO_191220191020363925',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'CustomerMessage' => 'Success. Request accepted for processing'
            ], 200),
        ]);

        // Test initiating M-Pesa payment
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/payments/mpesa/initiate', [
                'booking_id' => $this->booking->id,
                'phone_number' => '254708374149', // Test phone number
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'M-Pesa payment initiated successfully. Please check your phone.');

        // Check that a payment record was created with pending status
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'mpesa',
            'status' => 'pending',
        ]);

        // Simulate callback from M-Pesa
        $payment = Payment::where('booking_id', $this->booking->id)->first();

        $callbackData = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => '29115-34620561-1',
                    'CheckoutRequestID' => $payment->transaction_id,
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            [
                                'Name' => 'Amount',
                                'Value' => 1000
                            ],
                            [
                                'Name' => 'MpesaReceiptNumber',
                                'Value' => 'LHG31AA5TX'
                            ],
                            [
                                'Name' => 'TransactionDate',
                                'Value' => 20191219102115
                            ],
                            [
                                'Name' => 'PhoneNumber',
                                'Value' => 254708374149
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/mpesa/callback', $callbackData);
        $response->assertStatus(200);

        // Check that the payment status was updated
        $this->assertEquals('completed', $payment->fresh()->status);

        // Check that the booking status was updated
        $this->assertEquals('confirmed', $this->booking->fresh()->status);
        $this->assertEquals('paid', $this->booking->fresh()->payment_status);
    }

    /**
     * Test PayPal payment processing
     */
    public function test_paypal_payment_processing()
    {
        $this->markTestSkipped('Requires PayPal API keys to be configured');

        // Mock the PayPal API responses
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'test_access_token',
                'token_type' => 'Bearer',
                'app_id' => 'APP-80W284485P519543T',
                'expires_in' => 32400,
                'nonce' => 'test-nonce'
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/*/capture' => Http::response([
                'id' => 'test_order_id',
                'status' => 'COMPLETED',
                'purchase_units' => [
                    [
                        'reference_id' => 'test_reference',
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => '10.00'
                        ],
                        'payments' => [
                            'captures' => [
                                [
                                    'id' => 'test_capture_id',
                                    'status' => 'COMPLETED',
                                    'amount' => [
                                        'currency_code' => 'USD',
                                        'value' => '10.00'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'payer' => [
                    'email_address' => 'test@example.com',
                    'payer_id' => 'test_payer_id'
                ]
            ], 200)
        ]);

        // Process the payment
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/payments/process', [
                'booking_id' => $this->booking->id,
                'payment_method' => 'paypal',
                'payment_token' => 'test_order_id', // PayPal order ID
                'currency' => 'usd',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Payment processed successfully');

        // Check that the booking status was updated
        $this->assertEquals('confirmed', $this->booking->fresh()->status);
        $this->assertEquals('paid', $this->booking->fresh()->payment_status);

        // Check that a payment record was created
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'paypal',
            'status' => 'completed',
        ]);
    }

    /**
     * Test payment refund processing
     */
    public function test_payment_refund_processing()
    {
        $this->markTestSkipped('Requires payment gateway API keys to be configured');

        // Create a completed payment
        $payment = Payment::factory()->create([
            'booking_id' => $this->booking->id,
            'payment_method' => 'stripe',
            'amount' => 1000,
            'status' => 'completed',
            'payment_id' => 'pi_test_payment_intent',
            'currency' => 'usd',
        ]);

        // Update booking status
        $this->booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
        ]);

        // Mock Stripe refund response
        Http::fake([
            'api.stripe.com/*' => Http::response([
                'id' => 're_test_refund',
                'object' => 'refund',
                'amount' => 1000,
                'currency' => 'usd',
                'payment_intent' => 'pi_test_payment_intent',
                'status' => 'succeeded',
            ], 200)
        ]);

        // Create admin user
        $admin = User::factory()->create();
        $adminRole = \App\Models\Role::firstOrCreate(['name' => 'Admin']);
        $admin->roles()->attach($adminRole);

        // Process refund
        $response = $this->actingAs($admin)
            ->postJson('/api/v1/payments/' . $payment->id . '/refund', [
                'payment_id' => $payment->id,
                'reason' => 'Customer requested refund',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Refund processed successfully');

        // Check that the payment status was updated
        $this->assertEquals('refunded', $payment->fresh()->status);

        // Check that the booking status was updated
        $this->assertEquals('refunded', $this->booking->fresh()->status);
        $this->assertEquals('refunded', $this->booking->fresh()->payment_status);
    }
}
