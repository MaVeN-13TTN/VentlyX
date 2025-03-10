# VentlyX Database Schema Documentation

This document provides a detailed overview of the VentlyX database schema, including tables, relationships, indexes, and constraints.

## Entity Relationship Diagram

Below is a simplified representation of the main entities and their relationships:

```
User (1) ──────┐
               │
               ▼
Event (1) ────► Booking (N) ◄──── Payment (1)
   │                │
   │                ▼
   └───────► TicketType (N) ────► Ticket (N)
                     │
                     ▼
               EventCategory (N)
```

## Tables

### Users

Stores user information including authentication details.

| Column                    | Type          | Constraints               | Description                           |
| ------------------------- | ------------- | ------------------------- | ------------------------------------- |
| id                        | bigint        | PK, AUTO_INCREMENT        | Unique identifier for the user        |
| name                      | varchar(255)  | NOT NULL                  | Full name of the user                 |
| email                     | varchar(255)  | UNIQUE, NOT NULL          | Email address (used for login)        |
| email_verified_at         | timestamp     | NULLABLE                  | When email was verified               |
| password                  | varchar(255)  | NOT NULL                  | Encrypted password                    |
| phone_number              | varchar(20)   | NULLABLE                  | Contact phone number                  |
| role_id                   | bigint        | FK, NOT NULL              | References roles.id                   |
| two_factor_secret         | text          | NULLABLE                  | 2FA secret key (if enabled)           |
| two_factor_recovery_codes | text          | NULLABLE                  | Recovery codes for 2FA                |
| profile_photo_path        | varchar(2048) | NULLABLE                  | Path to profile photo                 |
| remember_token            | varchar(100)  | NULLABLE                  | Token for "remember me" functionality |
| created_at                | timestamp     | DEFAULT CURRENT_TIMESTAMP | Creation timestamp                    |
| updated_at                | timestamp     | DEFAULT CURRENT_TIMESTAMP | Last update timestamp                 |

Indexes:

-   PRIMARY KEY (id)
-   UNIQUE (email)
-   INDEX (role_id)

### Roles

Defines user roles and permissions within the system.

| Column      | Type        | Constraints               | Description                                    |
| ----------- | ----------- | ------------------------- | ---------------------------------------------- |
| id          | bigint      | PK, AUTO_INCREMENT        | Unique identifier for the role                 |
| name        | varchar(50) | UNIQUE, NOT NULL          | Role name (e.g., "admin", "organizer", "user") |
| permissions | json        | NOT NULL                  | JSON encoded permissions array                 |
| created_at  | timestamp   | DEFAULT CURRENT_TIMESTAMP | Creation timestamp                             |
| updated_at  | timestamp   | DEFAULT CURRENT_TIMESTAMP | Last update timestamp                          |

Indexes:

-   PRIMARY KEY (id)
-   UNIQUE (name)

### Events

Stores information about events.

| Column               | Type          | Constraints               | Description                           |
| -------------------- | ------------- | ------------------------- | ------------------------------------- |
| id                   | bigint        | PK, AUTO_INCREMENT        | Unique identifier for the event       |
| title                | varchar(255)  | NOT NULL                  | Event title                           |
| description          | text          | NOT NULL                  | Detailed description                  |
| start_date           | timestamp     | NOT NULL                  | Event start date and time             |
| end_date             | timestamp     | NOT NULL                  | Event end date and time               |
| location             | varchar(255)  | NOT NULL                  | Physical location or virtual platform |
| is_virtual           | boolean       | DEFAULT false             | Whether event is virtual              |
| virtual_meeting_link | varchar(2048) | NULLABLE                  | Link for virtual events               |
| organizer_id         | bigint        | FK, NOT NULL              | References users.id                   |
| banner_image         | varchar(2048) | NULLABLE                  | URL to banner image                   |
| status               | varchar(20)   | DEFAULT 'draft'           | Status (draft, published, cancelled)  |
| max_attendees        | int           | NULLABLE                  | Maximum capacity (NULL = unlimited)   |
| category_id          | bigint        | FK, NULLABLE              | References event_categories.id        |
| created_at           | timestamp     | DEFAULT CURRENT_TIMESTAMP | Creation timestamp                    |
| updated_at           | timestamp     | DEFAULT CURRENT_TIMESTAMP | Last update timestamp                 |

Indexes:

-   PRIMARY KEY (id)
-   INDEX (organizer_id)
-   INDEX (category_id)
-   INDEX (status)
-   FULLTEXT (title, description) - For search functionality

### EventCategories

Categorizes events by type.

| Column      | Type         | Constraints               | Description                        |
| ----------- | ------------ | ------------------------- | ---------------------------------- |
| id          | bigint       | PK, AUTO_INCREMENT        | Unique identifier for the category |
| name        | varchar(100) | UNIQUE, NOT NULL          | Category name                      |
| description | text         | NULLABLE                  | Category description               |
| created_at  | timestamp    | DEFAULT CURRENT_TIMESTAMP | Creation timestamp                 |
| updated_at  | timestamp    | DEFAULT CURRENT_TIMESTAMP | Last update timestamp              |

Indexes:

-   PRIMARY KEY (id)
-   UNIQUE (name)

### TicketTypes

Defines different types of tickets available for an event.

| Column             | Type          | Constraints               | Description                               |
| ------------------ | ------------- | ------------------------- | ----------------------------------------- |
| id                 | bigint        | PK, AUTO_INCREMENT        | Unique identifier for the ticket type     |
| event_id           | bigint        | FK, NOT NULL              | References events.id                      |
| name               | varchar(100)  | NOT NULL                  | Ticket type name (e.g., "VIP", "Regular") |
| description        | text          | NULLABLE                  | Description of ticket benefits            |
| price              | decimal(10,2) | NOT NULL                  | Price in base currency                    |
| quantity_available | int           | NOT NULL                  | Number of tickets available of this type  |
| quantity_sold      | int           | DEFAULT 0                 | Number of tickets sold                    |
| sales_start_date   | timestamp     | NOT NULL                  | When tickets go on sale                   |
| sales_end_date     | timestamp     | NOT NULL                  | When ticket sales end                     |
| created_at         | timestamp     | DEFAULT CURRENT_TIMESTAMP | Creation timestamp                        |
| updated_at         | timestamp     | DEFAULT CURRENT_TIMESTAMP | Last update timestamp                     |

Indexes:

-   PRIMARY KEY (id)
-   INDEX (event_id)
-   INDEX (sales_end_date) - For queries on available tickets

### Bookings

Records ticket purchases/reservations.

| Column            | Type          | Constraints               | Description                            |
| ----------------- | ------------- | ------------------------- | -------------------------------------- |
| id                | bigint        | PK, AUTO_INCREMENT        | Unique identifier for the booking      |
| user_id           | bigint        | FK, NOT NULL              | References users.id                    |
| event_id          | bigint        | FK, NOT NULL              | References events.id                   |
| ticket_type_id    | bigint        | FK, NOT NULL              | References ticket_types.id             |
| quantity          | int           | NOT NULL                  | Number of tickets purchased            |
| total_amount      | decimal(10,2) | NOT NULL                  | Total amount paid                      |
| status            | varchar(20)   | DEFAULT 'pending'         | Status (pending, confirmed, cancelled) |
| booking_reference | varchar(20)   | UNIQUE, NOT NULL          | Unique reference code                  |
| check_in_status   | boolean       | DEFAULT false             | Whether user has checked in            |
| check_in_time     | timestamp     | NULLABLE                  | When user checked in                   |
| created_at        | timestamp     | DEFAULT CURRENT_TIMESTAMP | Creation timestamp                     |
| updated_at        | timestamp     | DEFAULT CURRENT_TIMESTAMP | Last update timestamp                  |

Indexes:

-   PRIMARY KEY (id)
-   INDEX (user_id)
-   INDEX (event_id)
-   INDEX (ticket_type_id)
-   UNIQUE (booking_reference)
-   INDEX (status)

### Tickets

Individual tickets generated from bookings.

| Column      | Type        | Constraints               | Description                      |
| ----------- | ----------- | ------------------------- | -------------------------------- |
| id          | bigint      | PK, AUTO_INCREMENT        | Unique identifier for the ticket |
| booking_id  | bigint      | FK, NOT NULL              | References bookings.id           |
| ticket_code | varchar(50) | UNIQUE, NOT NULL          | Unique ticket code (for QR)      |
| is_used     | boolean     | DEFAULT false             | Whether ticket has been used     |
| created_at  | timestamp   | DEFAULT CURRENT_TIMESTAMP | Creation timestamp               |
| updated_at  | timestamp   | DEFAULT CURRENT_TIMESTAMP | Last update timestamp            |

Indexes:

-   PRIMARY KEY (id)
-   INDEX (booking_id)
-   UNIQUE (ticket_code)

### Payments

Records payment transactions.

| Column          | Type          | Constraints               | Description                                   |
| --------------- | ------------- | ------------------------- | --------------------------------------------- |
| id              | bigint        | PK, AUTO_INCREMENT        | Unique identifier for the payment             |
| booking_id      | bigint        | FK, NOT NULL              | References bookings.id                        |
| amount          | decimal(10,2) | NOT NULL                  | Amount paid                                   |
| currency        | varchar(3)    | DEFAULT 'KES'             | Currency code                                 |
| payment_method  | varchar(50)   | NOT NULL                  | Payment method (stripe, mpesa, etc.)          |
| transaction_id  | varchar(100)  | UNIQUE, NULLABLE          | External transaction reference                |
| status          | varchar(20)   | NOT NULL                  | Status (pending, completed, failed, refunded) |
| payment_details | json          | NULLABLE                  | Additional payment details as JSON            |
| created_at      | timestamp     | DEFAULT CURRENT_TIMESTAMP | Creation timestamp                            |
| updated_at      | timestamp     | DEFAULT CURRENT_TIMESTAMP | Last update timestamp                         |

Indexes:

-   PRIMARY KEY (id)
-   INDEX (booking_id)
-   INDEX (status)
-   UNIQUE (transaction_id)

### MpesaTransactions

Stores M-Pesa specific transaction details.

| Column               | Type         | Constraints               | Description                    |
| -------------------- | ------------ | ------------------------- | ------------------------------ |
| id                   | bigint       | PK, AUTO_INCREMENT        | Unique identifier              |
| payment_id           | bigint       | FK, NULLABLE              | References payments.id         |
| phone_number         | varchar(20)  | NOT NULL                  | Customer phone number          |
| merchant_request_id  | varchar(100) | UNIQUE, NOT NULL          | M-Pesa merchant request ID     |
| checkout_request_id  | varchar(100) | UNIQUE, NOT NULL          | M-Pesa checkout request ID     |
| result_code          | varchar(20)  | NULLABLE                  | Result code from M-Pesa        |
| result_description   | varchar(255) | NULLABLE                  | Result description             |
| mpesa_receipt_number | varchar(50)  | UNIQUE, NULLABLE          | M-Pesa receipt number          |
| transaction_date     | timestamp    | NULLABLE                  | When transaction was completed |
| created_at           | timestamp    | DEFAULT CURRENT_TIMESTAMP | Creation timestamp             |
| updated_at           | timestamp    | DEFAULT CURRENT_TIMESTAMP | Last update timestamp          |

Indexes:

-   PRIMARY KEY (id)
-   INDEX (payment_id)
-   UNIQUE (merchant_request_id)
-   UNIQUE (checkout_request_id)
-   UNIQUE (mpesa_receipt_number)

### PersonalAccessTokens

Stores API tokens for authentication (from Laravel Sanctum).

| Column         | Type         | Constraints               | Description              |
| -------------- | ------------ | ------------------------- | ------------------------ |
| id             | bigint       | PK, AUTO_INCREMENT        | Unique identifier        |
| tokenable_type | varchar(255) | NOT NULL                  | Model class name         |
| tokenable_id   | bigint       | NOT NULL                  | ID of the model          |
| name           | varchar(255) | NOT NULL                  | Token name/purpose       |
| token          | varchar(64)  | UNIQUE, NOT NULL          | Hashed token value       |
| abilities      | text         | NULLABLE                  | JSON encoded abilities   |
| last_used_at   | timestamp    | NULLABLE                  | When token was last used |
| expires_at     | timestamp    | NULLABLE                  | When token expires       |
| created_at     | timestamp    | DEFAULT CURRENT_TIMESTAMP | Creation timestamp       |
| updated_at     | timestamp    | DEFAULT CURRENT_TIMESTAMP | Last update timestamp    |

Indexes:

-   PRIMARY KEY (id)
-   INDEX (tokenable_type, tokenable_id)
-   UNIQUE (token)

## Relationships

1. **User to Events** (One-to-Many):

    - One user can organize many events
    - Foreign key: `events.organizer_id` references `users.id`

2. **User to Bookings** (One-to-Many):

    - One user can have many bookings
    - Foreign key: `bookings.user_id` references `users.id`

3. **User to Role** (Many-to-One):

    - Many users can have the same role
    - Foreign key: `users.role_id` references `roles.id`

4. **Event to TicketTypes** (One-to-Many):

    - One event can have many ticket types
    - Foreign key: `ticket_types.event_id` references `events.id`

5. **Event to EventCategory** (Many-to-One):

    - Many events can belong to one category
    - Foreign key: `events.category_id` references `event_categories.id`

6. **Event to Bookings** (One-to-Many):

    - One event can have many bookings
    - Foreign key: `bookings.event_id` references `events.id`

7. **TicketType to Bookings** (One-to-Many):

    - One ticket type can have many bookings
    - Foreign key: `bookings.ticket_type_id` references `ticket_types.id`

8. **Booking to Tickets** (One-to-Many):

    - One booking can have many individual tickets
    - Foreign key: `tickets.booking_id` references `bookings.id`

9. **Booking to Payment** (One-to-One):

    - One booking has one payment
    - Foreign key: `payments.booking_id` references `bookings.id`

10. **Payment to MpesaTransaction** (One-to-One):
    - One payment may have one M-Pesa transaction
    - Foreign key: `mpesa_transactions.payment_id` references `payments.id`

## Constraints and Business Rules

1. **Ticket Availability**:

    - The system prevents booking more tickets than available
    - Trigger on `bookings` before insert checks and updates `ticket_types.quantity_sold`

2. **Event Status**:

    - Events marked as "cancelled" cannot accept new bookings
    - Check constraint or application logic

3. **Booking Validation**:

    - Bookings can only be made for future events
    - Ticket sales must be within the sales start/end dates
    - Application logic ensures these constraints

4. **Referential Integrity**:

    - All foreign keys have ON DELETE CASCADE constraints
    - Ensures related data is removed when parent records are deleted

5. **Payment Status**:
    - Booking status transitions to "confirmed" only after payment status is "completed"
    - Trigger on `payments` after update updates `bookings.status`

## Indexes and Performance Optimization

1. **Search Optimization**:

    - Fulltext index on `events.title` and `events.description` for search functionality
    - Index on `events.start_date` for date-based queries

2. **Lookup Performance**:

    - Indexes on all foreign key columns for join performance
    - Index on `bookings.status` for filtering bookings by status
    - Index on `bookings.booking_reference` for quick lookup

3. **Unique Constraints**:
    - Unique constraints on all reference codes, transaction IDs, etc.

## Data Integrity and Validation

1. **NOT NULL Constraints**:

    - Applied to all required fields
    - Prevents incomplete data records

2. **Check Constraints**:

    - `events.end_date` must be after `events.start_date`
    - `ticket_types.price` must be greater than or equal to 0
    - `bookings.quantity` must be greater than 0

3. **Default Values**:
    - Sensible defaults provided for status fields
    - Timestamps automatically set

## Soft Deletion

Some entities implement soft deletion where records are marked as deleted rather than being physically removed:

-   `users` table has `deleted_at` column
-   `events` table has `deleted_at` column
-   `bookings` table has `deleted_at` column

This preserves historical data while hiding it from regular queries.

## Audit Trail

Changes to critical data are logged in audit tables:

-   `user_activity_logs` tracks user actions
-   `payment_logs` tracks detailed payment processing steps
-   `booking_status_changes` tracks booking status transitions
