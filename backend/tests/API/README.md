# API Tests for VentlyX

This directory contains API tests for the VentlyX application. These tests verify the functionality, security, and consistency of the API endpoints.

## Test Structure

The API tests are organized into the following categories:

1. **Authentication Tests** (`AuthenticationTest.php`)

    - Tests for user registration, login, and token validation
    - Tests for protected endpoint access and logout functionality
    - Verifies proper error responses for invalid credentials

2. **Rate Limiting Tests** (`RateLimitingTest.php`)

    - Tests that API requests can be made without hitting rate limits
    - Verifies that auth routes have specific rate limiting
    - Tests that booking routes can be accessed
    - Checks for caching headers on public endpoints

3. **Response Format Tests** (`ResponseFormatTest.php`)
    - Tests for JSON structure consistency in responses
    - Verifies error response formats (404, 422)
    - Tests event details retrieval and pagination
    - Checks for CORS headers, caching, and content negotiation
    - Verifies API root endpoint information

## Base Test Case

All API tests extend the `ApiTestCase` class, which provides helper methods for:

-   Creating users with specific roles (User, Organizer, Admin)
-   Getting authentication tokens
-   Making authenticated and unauthenticated API requests
-   Role-specific request methods (makeOrganizerRequest, makeAdminRequest)

## API Endpoints Tested

The tests cover the following key endpoints:

-   **Authentication**: `/api/v1/auth/register`, `/api/v1/auth/login`, `/api/v1/auth/logout`
-   **User Profile**: `/api/v1/profile`
-   **Events**: `/api/v1/events`, `/api/v1/events/{id}`
-   **Bookings**: `/api/v1/bookings`
-   **API Information**: `/api`

## Running the Tests

To run all API tests:

```bash
php artisan test --testsuite=API
```

To run a specific test class:

```bash
php artisan test --filter=AuthenticationTest
```

To run a specific test method:

```bash
php artisan test --filter=AuthenticationTest::test_user_can_register
```

## Adding New Tests

When adding new API tests:

1. Create a new test class that extends `ApiTestCase`
2. Organize tests by API feature or endpoint
3. Use the helper methods from `ApiTestCase` for making API requests
4. Follow the existing patterns for asserting responses

Example:

```php
class BookingApiTest extends ApiTestCase
{
    public function test_user_can_view_bookings()
    {
        $user = $this->createUserWithRole('User');
        $response = $this->makeAuthenticatedRequest('GET', '/api/v1/bookings', [], $user);
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }
}
```

## Test Design Principles

1. **Independence**: Each test is independent and doesn't rely on other tests
2. **Clarity**: Test names clearly describe what is being tested
3. **Focused**: Each test verifies a single aspect of functionality
4. **Robust**: Tests are designed to be resilient to minor API changes
5. **Comprehensive**: Tests cover happy paths and error cases

## Notes on Test Data

-   Tests use factories to create test data
-   The database is refreshed between tests using the `RefreshDatabase` trait
-   Test data is created specifically for each test to avoid dependencies
-   Assertions are flexible enough to accommodate minor response format changes

## Troubleshooting

If tests are failing, check:

1. API endpoint paths match your actual implementation
2. Expected response structures match your API responses
3. Status code expectations align with your API behavior
4. Rate limiting configuration matches your application settings
5. Authentication flow works as expected

For more information on Laravel testing, see the [Laravel documentation](https://laravel.com/docs/10.x/testing).
