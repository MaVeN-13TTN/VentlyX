#!/bin/bash

# VentlyX Payment Testing Setup Script

echo "=== VentlyX Payment Testing Setup ==="
echo ""

# Copy the payment testing environment file
if [ -f .env.payment-testing ]; then
    echo "Copying payment testing environment file..."
    cp .env.payment-testing .env
    echo "✅ Environment file copied"
else
    echo "❌ Payment testing environment file not found"
    exit 1
fi

# Install dependencies
echo "Installing dependencies..."
composer install
echo "✅ Dependencies installed"

# Clear caches
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
echo "✅ Caches cleared"

# Run migrations
echo "Running database migrations..."
php artisan migrate
echo "✅ Migrations completed"

# Start the server
echo "Starting the Laravel server..."
php artisan serve &
SERVER_PID=$!
echo "✅ Server started (PID: $SERVER_PID)"

# Check if ngrok is installed
if command -v ngrok &> /dev/null; then
    echo "Starting ngrok for webhook testing..."
    ngrok http 8000 &
    NGROK_PID=$!
    echo "✅ Ngrok started (PID: $NGROK_PID)"
    
    # Wait for ngrok to start
    sleep 5
    
    # Get the ngrok URL
    NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | grep -o "https://[a-zA-Z0-9.-]*\.ngrok\.io")
    
    if [ -n "$NGROK_URL" ]; then
        echo ""
        echo "=== Webhook URLs ==="
        echo "Stripe Webhook URL: $NGROK_URL/api/stripe/webhook"
        echo "M-Pesa Callback URL: $NGROK_URL/api/mpesa/callback"
        echo "PayPal Webhook URL: $NGROK_URL/api/paypal/webhook"
        echo ""
        echo "Update your .env file with these URLs:"
        echo "MPESA_CALLBACK_URL=$NGROK_URL/api/mpesa/callback"
        echo ""
    else
        echo "❌ Failed to get ngrok URL"
    fi
else
    echo "⚠️ Ngrok not found. Install ngrok for webhook testing."
fi

echo ""
echo "=== Running Payment Test Script ==="
echo ""
php artisan tinker --execute="require 'tests/manual-payment-test.php';"

echo ""
echo "=== Payment Testing Setup Complete ==="
echo ""
echo "To run automated tests:"
echo "php artisan test --filter=PaymentGatewayTest"
echo ""
echo "For more information, see docs/payment-testing-guide.md"
echo ""
echo "Press Ctrl+C to stop the server and ngrok"

# Wait for user to press Ctrl+C
wait $SERVER_PID 