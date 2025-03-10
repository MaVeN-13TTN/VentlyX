# VentlyX Testing Guide

This document outlines the testing strategy and practices for the VentlyX backend application. It provides guidance on writing effective tests and understanding the existing test structure.

## Testing Philosophy

The VentlyX project follows a comprehensive testing approach with these goals:

-   Ensure functionality works as expected
-   Prevent regressions when making changes
-   Document expected behavior through tests
-   Improve code design through testability requirements

## Test Types

### Unit Tests

Located in the `tests/Unit` directory, these tests focus on isolated components:

-   Individual class methods
-   Functions with minimal dependencies
-   Service and repository logic

Unit tests should:

-   Be fast and have no external dependencies
-   Mock or stub external dependencies
-   Test only one unit of code at a time
-   Cover edge cases and failure scenarios

### Feature Tests

Located in the `tests/Feature` directory, these tests focus on complete features:

-   API endpoints
-   User flows
-   Integration between components

Feature tests should:

-   Test complete user stories or API endpoints
-   Assert on HTTP status codes, response formats, and database changes
-   Cover authentication and authorization scenarios

### Integration Tests

These tests (part of Feature tests) focus on component interaction:

-   Database interactions
-   Third-party service integration (payment gateways, etc.)

## Test Structure

### Naming Conventions

-   Test classes should be named after the class they test with a `Test` suffix
-   Test methods should use descriptive names explaining what they test
-   Follow the pattern: `test_[method]_[scenario]_[expected_result]`

Example:

```php
// Testing the BookingController::store method
public function test_store_with_valid_input_creates_booking()
public function test_store_with_invalid_input_returns_validation_error()
```

### Common Testing Patterns

1. **Arrange-Act-Assert (AAA)**:

    ```php
    // Arrange
    $user = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $user->id]);

    // Act
    $response = $this->actingAs($user)->post('/api/events', $eventData);

    // Assert
    $response->assertStatus(201);
    $this->assertDatabaseHas('events', ['title' => $eventData['title']]);
    ```

2. **Given-When-Then**:

    ```php
    // Given a user and an event
    $user = User::factory()->create();
    $event = Event::factory()->create();

    // When the user tries to book a ticket
    $response = $this->actingAs($user)->post('/api/bookings', [
        'event_id' => $event->id,
        'ticket_type_id' => $ticketType->id
    ]);

    // Then a booking should be created
    $response->assertStatus(201);
    $this->assertDatabaseHas('bookings', [
        'user_id' => $user->id,
        'event_id' => $event->id
    ]);
    ```

## Testing Tools and Libraries

-   **PHPUnit**: Primary testing framework
-   **Laravel Testing Helpers**: Built-in tools for HTTP tests, database assertions, etc.
-   **Factories**: Data generators for tests (located in `database/factories`)
-   **Mockery**: For creating test doubles (mocks, stubs)
-   **Faker**: For generating test data

## Writing Effective Tests

### Best Practices

1. **Test One Thing**: Each test should verify one specific behavior
2. **Independent Tests**: Tests should not depend on each other
3. **Repeatable Results**: Tests should produce the same results each time
4. **Self-Validating**: Tests should automatically determine if they pass or fail
5. **Timely**: Write tests close to when you write the code

### Testing Controllers

```php
public function test_index_returns_paginated_events()
{
    // Create test data
    Event::factory()->count(15)->create();

    // Make request
    $response = $this->getJson('/api/events');

    // Assert response
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => ['id', 'title', 'description', 'start_date']
        ],
        'meta' => ['current_page', 'last_page', 'per_page', 'total']
    ]);
    $response->assertJsonCount(10, 'data'); // Default pagination
}
```

### Testing Models

```php
public function test_event_belongs_to_organizer()
{
    $user = User::factory()->create();
    $event = Event::factory()->create(['organizer_id' => $user->id]);

    $this->assertInstanceOf(User::class, $event->organizer);
    $this->assertEquals($user->id, $event->organizer->id);
}
```

### Testing Services

```php
public function test_payment_service_processes_payment()
{
    // Mock the payment gateway
    $mockGateway = $this->mock(PaymentGatewayInterface::class);
    $mockGateway->shouldReceive('process')
        ->once()
        ->andReturn(['success' => true, 'transaction_id' => '123456']);

    // Create the service with the mock
    $paymentService = new PaymentService($mockGateway);

    // Test the service
    $result = $paymentService->processPayment(1000, 'USD', $paymentDetails);

    // Assert result
    $this->assertTrue($result['success']);
    $this->assertEquals('123456', $result['transaction_id']);
}
```

## Setting Up Test Data

### Using Factories

```php
// Create a basic model
$user = User::factory()->create();

// Create with specific attributes
$event = Event::factory()->create([
    'title' => 'Test Event',
    'start_date' => now()->addDays(5)
]);

// Create related models
$event = Event::factory()
    ->has(TicketType::factory()->count(3))
    ->create();
```

### Database Transactions

All tests use database transactions to roll back changes after each test:

```php
use RefreshDatabase;
```

## Testing Authentication and Authorization

```php
public function test_unauthenticated_user_cannot_create_event()
{
    $response = $this->postJson('/api/events', $eventData);

    $response->assertStatus(401);
}

public function test_non_organizer_cannot_create_event()
{
    $user = User::factory()->create(['role_id' => $regularUserRole->id]);

    $response = $this->actingAs($user)->postJson('/api/events', $eventData);

    $response->assertStatus(403);
}
```

## Running Tests

Run all tests:

```bash
php artisan test
```

Run specific test class:

```bash
php artisan test --filter=EventControllerTest
```

Run specific test method:

```bash
php artisan test --filter=EventControllerTest::test_store_with_valid_input_creates_event
```

## Continuous Integration

Tests are automatically run in the CI/CD pipeline on:

-   Pull request creation or update
-   Merge to main branch

## Test Coverage

Generate test coverage report:

```bash
php artisan test --coverage
```

View detailed HTML coverage report:

```bash
php artisan test --coverage-html coverage-report
```

## Troubleshooting Tests

Common issues and solutions:

1. **Random failures**: Check for time-dependent tests or tests that depend on external services
2. **Slow tests**: Identify bottlenecks using `--profile` option
3. **Database inconsistencies**: Ensure `RefreshDatabase` or `DatabaseTransactions` traits are used

## Additional Resources

-   [Laravel Testing Documentation](https://laravel.com/docs/testing)
-   [PHPUnit Documentation](https://phpunit.de/documentation.html)
-   [Mockery Documentation](http://docs.mockery.io/)
