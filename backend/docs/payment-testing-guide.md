# VentlyX Payment Testing Guide

This guide provides detailed instructions for testing the payment gateways integrated with VentlyX.

## Prerequisites

Before testing payments, ensure you have:

1. A local development environment with PHP 8.1+ and Composer
2. MySQL/PostgreSQL database set up
3. Redis server (for queue processing)
4. Ngrok or similar tool for webhook testing
5. Test accounts for Stripe, PayPal, and M-Pesa (Safaricom Daraja)

## Environment Setup

1. Copy the payment testing environment file:

    ```bash
    cp .env.payment-testing .env
    ```

2. Update the environment variables with your test credentials:

    - Database credentials
    - Stripe API keys
    - M-Pesa API keys
    - PayPal API keys

3. Start ngrok to receive webhooks:

    ```bash
    ngrok http 8000
    ```

4. Update the callback URLs in your `.env` file with the ngrok URL:

    ```
    MPESA_CALLBACK_URL=https://your-ngrok-url.ngrok.io/api/mpesa/callback
    ```

5. Update webhook URLs in the respective dashboards:
    - Stripe Dashboard: `https://your-ngrok-url.ngrok.io/api/stripe/webhook`
    - M-Pesa Developer Portal: `https://your-ngrok-url.ngrok.io/api/mpesa/callback`
    - PayPal Developer Dashboard: `https://your-ngrok-url.ngrok.io/api/paypal/webhook` (if implemented)

## Running the Test Script

We've created a manual testing script to help you test the payment gateways:

```bash
php artisan tinker --execute="require 'tests/manual-payment-test.php';"
```

This script will:

1. Check your environment configuration
2. Create test data (user, event, ticket type, booking)
3. Test Stripe integration
4. Test M-Pesa integration
5. Test PayPal integration
6. Provide a summary of test endpoints

## Testing Each Payment Gateway

### 1. Stripe Testing

#### Test Cards

-   Successful payment: `4242 4242 4242 4242`
-   Requires authentication: `4000 0025 0000 3155`
-   Declined payment: `4000 0000 0000 0002`

#### Testing Steps

1. Create a payment intent using the test script or API
2. Use the client secret with Stripe Elements or Checkout in your frontend
3. Complete the payment with a test card
4. Verify the payment status in your database and Stripe Dashboard

#### Webhook Testing

1. Install the Stripe CLI for local webhook testing:
    ```bash
    stripe listen --forward-to http://localhost:8000/api/stripe/webhook
    ```
2. Trigger events from the Stripe CLI:
    ```bash
    stripe trigger payment_intent.succeeded
    ```

### 2. M-Pesa Testing

#### Test Phone Numbers

-   Use Safaricom test numbers: `254708374149`

#### Testing Steps

1. Initiate payment using the API:
    ```
    POST /api/v1/payments/mpesa/initiate
    {
      "booking_id": 1,
      "phone_number": "254708374149"
    }
    ```
2. For sandbox testing, the payment will be automatically accepted
3. Check the payment status using:
    ```
    POST /api/v1/payments/mpesa/check-status
    {
      "checkout_request_id": "ws_CO_191220191020363925"
    }
    ```

#### Callback Testing

1. Ensure ngrok is running and the callback URL is set in your `.env` file
2. The sandbox environment will send a callback to your webhook URL
3. Monitor the logs to see the callback data

### 3. PayPal Testing

#### Test Accounts

-   Create sandbox accounts in the PayPal Developer Dashboard
-   Use these accounts for testing payments

#### Testing Steps

1. Implement PayPal Checkout in your frontend using the JavaScript SDK
2. Create an order using the sandbox account
3. Process the payment using the API:
    ```
    POST /api/v1/payments/process
    {
      "booking_id": 1,
      "payment_method": "paypal",
      "payment_token": "PAYPAL_ORDER_ID",
      "currency": "usd"
    }
    ```
4. Verify the payment status in your database and PayPal Dashboard

## Testing Refunds

Refunds can only be processed by admin users:

1. Create an admin user if you don't have one:

    ```php
    $admin = \App\Models\User::find(1);
    $adminRole = \App\Models\Role::firstOrCreate(['name' => 'Admin']);
    $admin->roles()->attach($adminRole);
    ```

2. Process a refund using the API:

    ```
    POST /api/v1/payments/{payment_id}/refund
    {
      "payment_id": 1,
      "reason": "Customer requested refund"
    }
    ```

3. Verify the refund status in your database and payment gateway dashboard

## Automated Testing

We've also created automated tests for the payment gateways:

```bash
php artisan test --filter=PaymentGatewayTest
```

Note: By default, these tests are skipped as they require API keys to be configured. Remove the `markTestSkipped` line to run them.

## Troubleshooting

### Common Issues

1. **Webhook Not Received**

    - Check that ngrok is running
    - Verify the webhook URL in the payment gateway dashboard
    - Check the Laravel logs for errors

2. **Payment Failed**

    - Check the payment gateway logs
    - Verify the test card/account details
    - Check for validation errors in your request

3. **Database Connection Issues**
    - Verify your database credentials
    - Check that the database server is running

### Logging

Payment-related logs can be found in:

-   Laravel logs: `storage/logs/laravel.log`
-   Payment gateway dashboards

## Production Considerations

When moving to production:

1. Replace all test API keys with production keys
2. Update webhook URLs to production URLs
3. Remove test code and accounts
4. Implement additional security measures
5. Set up monitoring and alerting for payment failures

## Additional Resources

-   [Stripe API Documentation](https://stripe.com/docs/api)
-   [PayPal API Documentation](https://developer.paypal.com/docs/api/overview/)
-   [M-Pesa API Documentation](https://developer.safaricom.co.ke/docs)
