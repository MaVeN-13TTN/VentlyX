# VentlyX Coding Standards and Best Practices

This document outlines the coding standards and best practices for the VentlyX backend codebase. Following these guidelines will ensure code consistency, readability, and maintainability.

## PHP Coding Standards

### General Guidelines

-   Follow PSR-12 coding style for PHP code
-   Use PHP 8.2+ features where applicable
-   Avoid "magic" where possible; be explicit
-   Keep functions and methods small and focused
-   Use type declarations for method parameters and return types

### Naming Conventions

-   **Classes**: PascalCase (e.g., `EventController`, `UserRepository`)
-   **Methods/Functions**: camelCase (e.g., `getEventDetails()`, `createBooking()`)
-   **Variables**: camelCase (e.g., `$eventData`, `$userProfile`)
-   **Constants**: UPPER_SNAKE_CASE (e.g., `API_VERSION`, `MAX_LOGIN_ATTEMPTS`)
-   **Database Tables**: snake_case, plural (e.g., `events`, `ticket_types`)
-   **Database Columns**: snake_case (e.g., `first_name`, `created_at`)

### File Organization

-   One class per file
-   Filename should match the class name
-   Group related functionality in namespaces
-   Follow PSR-4 autoloading standard

## Laravel Best Practices

### Controllers

-   Follow single responsibility principle
-   Keep controllers thin, move business logic to services/repositories
-   Return consistent JSON responses using Response helpers
-   Use form requests for complex validation
-   Leverage resource collections for API responses

### Models

-   Define relationships clearly
-   Use model observers for related operations
-   Keep query scopes focused and reusable
-   Use custom casts for complex attributes
-   Define accessors and mutators as needed

### Database

-   Always use migrations for database changes
-   Create meaningful seeders for development/testing
-   Use database transactions for multi-step operations
-   Index columns used frequently in WHERE, ORDER BY, or JOIN clauses
-   Avoid raw queries; use the query builder or Eloquent

### Services & Repositories

-   Use services for complex business logic
-   Use repositories for database interaction abstraction
-   Make services focused on specific domains (e.g., `PaymentService`, `EventService`)
-   Inject dependencies via constructor

### API Design

-   Follow RESTful conventions
-   Use proper HTTP status codes
-   Version the API for backward compatibility
-   Use consistent response structure
-   Document all endpoints with OpenAPI/Swagger

### Authentication & Authorization

-   Use Laravel Sanctum for API authentication
-   Implement proper authorization via policies
-   Never trust client-side validation alone
-   Use middleware for route protection
-   Keep authentication logic separate from business logic

### Error Handling

-   Use custom exceptions for business logic errors
-   Return consistent error responses
-   Log significant errors
-   Don't expose sensitive information in error messages
-   Handle validation errors consistently

### Testing

-   Write unit tests for services and repositories
-   Write feature tests for API endpoints
-   Use factories for test data
-   Mock external services in tests
-   Aim for high test coverage on critical paths

## Security Practices

-   Validate all input
-   Escape output to prevent XSS
-   Use prepared statements (built into Eloquent/Query Builder)
-   Implement CSRF protection for web routes
-   Set proper HTTP headers (Content Security Policy, etc.)
-   Store sensitive data encrypted
-   Keep dependencies updated
-   Follow the principle of least privilege

## Performance Considerations

-   Cache frequently accessed data
-   Optimize database queries (use eager loading, avoid N+1 problems)
-   Use queued jobs for long-running tasks
-   Implement appropriate indexes
-   Use pagination for large result sets
-   Consider rate limiting for public APIs

## Code Review Checklist

Before submitting code for review, ensure:

-   Code follows the standards in this document
-   All tests pass
-   New features have corresponding tests
-   No security vulnerabilities introduced
-   Documentation is updated
-   No debug code or commented-out code remains

## Commit Message Guidelines

-   Use present tense ("Add feature" not "Added feature")
-   First line is a summary (max 50 characters)
-   Optionally followed by blank line and detailed description
-   Reference issue numbers if applicable

Example:

```
Add event registration confirmation emails

- Send confirmation email when user registers for event
- Include QR code in email for check-in
- Add email template with event details

Fixes #123
```

## Tools and Resources

-   Use Laravel Pint for code style enforcement
-   Use PHPStan for static analysis
-   Use Laravel Debugbar for development debugging
