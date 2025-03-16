# VentlyX Integration Tests

This directory contains integration tests for the VentlyX platform, focusing on testing complete user flows and interactions between multiple components of the system.

## Test Structure

The integration tests are organized into three main categories:

1. **BookingFlow** - Tests related to the complete booking process, from event selection to payment processing.
2. **EventManagement** - Tests for event creation, updating, and management by organizers.
3. **CheckInFlow** - Tests for the check-in process at events, including QR code scanning and manual check-ins.

## Running the Tests

To run all integration tests:

```bash
php artisan test --testsuite=Integration
```

To run a specific test category:

```bash
php artisan test --filter=BookingPaymentFlowTest
php artisan test --filter=EventCreationFlowTest
php artisan test --filter=CheckInProcessTest
```

## Test Environment Setup

These tests use Laravel's `RefreshDatabase` trait, which means they will reset the database between test runs. The tests are designed to be self-contained and will set up all necessary data for each test case.

### Payment Testing

For payment-related tests, we use mock implementations of payment gateways:

-   **Stripe** - Tests use a mocked Stripe API client
-   **M-Pesa** - Tests use a mocked M-Pesa API client

To configure the payment testing environment:

1. Copy the `.env.payment-testing` file to your `.env.testing` file
2. Run the `setup-payment-testing.sh` script to set up the necessary test keys

```bash
cp .env.payment-testing .env.testing
bash setup-payment-testing.sh
```

## Writing New Integration Tests

When writing new integration tests, follow these guidelines:

1. **Use the appropriate namespace** - Place your test in the correct category folder and use the corresponding namespace.
2. **Extend TestCase** - All tests should extend the base `Tests\TestCase` class.
3. **Use RefreshDatabase** - Include the `RefreshDatabase` trait to ensure a clean database state.
4. **Set up test data in setUp()** - Create all necessary models and relationships in the `setUp()` method.
5. **Test complete flows** - Integration tests should test entire user flows, not just individual components.
6. **Use descriptive test names** - Test method names should clearly describe what is being tested.
7. **Include assertions** - Make assertions about both the API responses and the database state.

Example structure for a new integration test:

```php
<?php

namespace Tests\Integration\YourCategory;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YourFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up your test data here
    }

    public function test_your_complete_flow()
    {
        // Step 1: First action in the flow

        // Step 2: Second action in the flow

        // Step 3: Final action and assertions
    }
}
```

## Best Practices

1. **Test realistic scenarios** - Focus on testing real-world user flows.
2. **Test edge cases** - Include tests for error conditions and edge cases.
3. **Keep tests independent** - Each test should be able to run independently of others.
4. **Use factories** - Use model factories to create test data.
5. **Mock external services** - Use mocks for external services like payment gateways.
6. **Test both happy and unhappy paths** - Test both successful flows and error scenarios.
7. **Document test assumptions** - Add comments to explain any assumptions or complex setup.

## Troubleshooting

If you encounter issues with the integration tests:

1. **Check database configuration** - Ensure your testing database is properly configured.
2. **Verify environment variables** - Make sure all required environment variables are set in `.env.testing`.
3. **Check for missing dependencies** - Ensure all required packages are installed.
4. **Look for timing issues** - Some tests may fail due to timing issues with async operations.
5. **Inspect test database** - You can inspect the test database during debugging by adding `$this->artisan('db:seed')` before a failing assertion.

## Contributing

When contributing new integration tests:

1. Follow the existing test structure and naming conventions.
2. Ensure your tests are properly documented with comments.
3. Make sure all tests pass before submitting a pull request.
4. Include tests for both success and failure scenarios.
