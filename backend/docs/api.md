# VentlyX API Documentation

## Table of Contents

1. [Base URL](#base-url)
2. [Authentication](#authentication)
3. [Events](#events)
4. [Tickets](#tickets)
5. [Bookings](#bookings)
6. [Payments](#payments)
7. [User Management](#user-management)
8. [Analytics](#analytics)
9. [Check-in System](#check-in-system)
10. [Error Handling](#error-handling)
11. [Rate Limiting](#rate-limiting)

## Base URL

All API URLs are relative to: `https://api.ventlyx.com/api/v1/` (replace with your actual domain)

## Authentication

### Register User

-   **POST** `/auth/register`
-   **Required Fields**: name, email, password, password_confirmation
-   **Optional Fields**: phone_number
-   **Returns**: User object with token

### Login

-   **POST** `/auth/login`
-   **Required Fields**: email, password
-   **Returns**: User object with token

### Logout

-   **POST** `/auth/logout`
-   **Headers Required**: Authorization Bearer token
-   **Returns**: Success message

### Password Reset

-   **POST** `/auth/forgot-password`
-   **Required Fields**: email
-   **Returns**: Success message with reset instructions

## Events

### List Events

-   **GET** `/events`
-   **Query Parameters**:
    -   page (default: 1)
    -   per_page (default: 10)
    -   search
    -   category
    -   date_from
    -   date_to
-   **Returns**: Paginated list of events

### Get Event

-   **GET** `/events/{id}`
-   **Returns**: Event details with ticket types

### Create Event

-   **POST** `/events`
-   **Headers Required**: Authorization Bearer token (Admin only)
-   **Required Fields**: title, description, start_date, end_date, venue
-   **Optional Fields**: category, image, ticket_types
-   **Returns**: Created event object

### Update Event

-   **PUT** `/events/{id}`
-   **Headers Required**: Authorization Bearer token (Admin only)
-   **Fields**: Same as create
-   **Returns**: Updated event object

### Delete Event

-   **DELETE** `/events/{id}`
-   **Headers Required**: Authorization Bearer token (Admin only)
-   **Returns**: Success message

## Tickets

### List Ticket Types

-   **GET** `/events/{id}/ticket-types`
-   **Returns**: Array of ticket types for event

### Create Ticket Type

-   **POST** `/events/{id}/ticket-types`
-   **Headers Required**: Authorization Bearer token (Admin only)
-   **Required Fields**: name, price, quantity
-   **Optional Fields**: description, sale_start, sale_end
-   **Returns**: Created ticket type

## Bookings

### Create Booking

-   **POST** `/bookings`
-   **Headers Required**: Authorization Bearer token
-   **Required Fields**: event_id, ticket_type_id, quantity
-   **Returns**: Booking with payment link

### List User Bookings

-   **GET** `/bookings`
-   **Headers Required**: Authorization Bearer token
-   **Query Parameters**: status, page
-   **Returns**: Paginated list of user's bookings

### Get Booking

-   **GET** `/bookings/{id}`
-   **Headers Required**: Authorization Bearer token
-   **Returns**: Booking details with tickets

### Get Ticket QR

-   **GET** `/bookings/{id}/ticket`
-   **Headers Required**: Authorization Bearer token
-   **Returns**: QR code for ticket validation

## Payments

### Create Payment

-   **POST** `/payments`
-   **Headers Required**: Authorization Bearer token
-   **Required Fields**: booking_id, payment_method
-   **Returns**: Payment gateway URL or payment instructions

### Verify Payment

-   **GET** `/payments/{id}/verify`
-   **Headers Required**: Authorization Bearer token
-   **Returns**: Payment status

### Payment Webhook

-   **POST** `/payments/webhook`
-   **Headers Required**: Webhook signature
-   **Returns**: Acknowledgment

## User Management

### Get Profile

-   **GET** `/user`
-   **Headers Required**: Authorization Bearer token
-   **Returns**: User profile

### Update Profile

-   **PUT** `/user`
-   **Headers Required**: Authorization Bearer token
-   **Fields**: name, email, phone_number
-   **Returns**: Updated user profile

### Admin Only Endpoints

-   **GET** `/admin/users` - List all users
-   **GET** `/admin/users/{id}` - Get user details
-   **PUT** `/admin/users/{id}` - Update user
-   **DELETE** `/admin/users/{id}` - Delete user

## Analytics

### Event Analytics

-   **GET** `/analytics/events`
-   **Headers Required**: Authorization Bearer token (Admin only)
-   **Query Parameters**: date_from, date_to
-   **Returns**: Event statistics

### Sales Analytics

-   **GET** `/analytics/sales`
-   **Headers Required**: Authorization Bearer token (Admin only)
-   **Query Parameters**: date_from, date_to
-   **Returns**: Sales statistics

## Check-in System

### Validate Ticket

-   **POST** `/check-in/validate`
-   **Headers Required**: Authorization Bearer token (Staff only)
-   **Required Fields**: qr_code
-   **Returns**: Ticket validation status

### Check-in History

-   **GET** `/check-in/history`
-   **Headers Required**: Authorization Bearer token (Staff only)
-   **Query Parameters**: event_id, date
-   **Returns**: Check-in history

## Error Handling

All endpoints follow a consistent error response format:

```json
{
    "error": {
        "code": "ERROR_CODE",
        "message": "Human readable message",
        "details": {} // Optional additional details
    }
}
```

Common HTTP status codes:

-   200: Success
-   201: Created
-   400: Bad Request
-   401: Unauthorized
-   403: Forbidden
-   404: Not Found
-   422: Validation Error
-   429: Too Many Requests
-   500: Server Error

## Rate Limiting

API requests are limited to:

-   60 requests per minute for authenticated users
-   30 requests per minute for unauthenticated users
-   Specific endpoints may have custom limits

Rate limit headers are included in responses:

-   X-RateLimit-Limit
-   X-RateLimit-Remaining
-   X-RateLimit-Reset
