# VentlyX

## Project Overview

VentlyX is a comprehensive web-based application designed to facilitate seamless booking and management of events, concerts, and shows. The platform provides an intuitive interface for users to explore and book events while enabling administrators to efficiently manage event listings, user registrations, and ticketing operations.

## Target Audience

- **Event Attendees** – Users looking to browse and book tickets for events
- **Event Organizers** – Hosts who want to list events and manage bookings
- **Administrators** – System managers responsible for maintaining the platform

## Key Features

### User Side (Attendees)

- **Homepage with Event Listings** – Display a carousel of upcoming events with advanced filtering options
- **Event Details Page** – Users can view event location, time, price tiers, and descriptions
- **User Registration & Authentication** – Users sign up using email/password with optional two-factor authentication
- **Ticket Booking System** – Select ticket type (Individual, Group, VIP, VVIP) and make a payment
- **QR Code Ticket Generation** – Upon booking confirmation, users receive a QR code for event check-in
- **Payment Integration** – Secure payments via Stripe and M-Pesa
- **Booking History & Notifications** – Users can view past bookings and receive notifications
- **Ticket Transfer System** – Users can transfer tickets to friends and family

### Organizer Side

- **Event Creation & Management** – Create and manage event listings with detailed information
- **Ticket Type Configuration** – Set up different ticket tiers with pricing and availability
- **Check-in Management** – Scan QR codes and track attendee check-ins
- **Event Analytics** – Access real-time data on ticket sales and attendance
- **Attendee Management** – View and manage the list of attendees for each event

### Admin Side

- **Admin Dashboard** – Overview of bookings, payments, and event analytics
- **Event Management** – Create, edit, and delete event listings
- **User Management** – View, activate, or deactivate user accounts
- **Booking & Payment Management** – View transaction history and manage refunds if necessary
- **Analytics & Reports** – Generate reports on event attendance and revenue
- **Role-Based Access Control** – Define permissions for different admin roles

## Technology Stack

| Component           | Technology Used                            |
| ------------------- | ------------------------------------------ |
| Frontend            | Vue.js 3 + TypeScript + Tailwind CSS       |
| Backend             | Laravel 12 + Sanctum for authentication    |
| Database            | MySQL or PostgreSQL                        |
| Authentication      | Laravel Sanctum, Two-Factor Authentication |
| Payments            | Stripe, M-Pesa Integration                 |
| QR Code & Ticketing | Simple QR Code Package                     |
| Real-Time Updates   | Laravel Echo + Pusher                      |

## System Architecture

The application follows an MVC (Model-View-Controller) architecture:

- **Frontend Layer** – Vue.js with TypeScript using Composition API
- **Backend Layer** – Laravel handles business logic, database queries, and authentication
- **Database Layer** – MySQL/PostgreSQL for structured data storage
- **API Layer** – RESTful APIs for communication between frontend and backend

## Project Structure

- `/frontend` – Contains the Vue.js application
- `/backend` – Contains the Laravel application
  - `/app` – Core application code
    - `/Http/Controllers/API` – API controllers for various features
    - `/Models` – Database models and relationships
    - `/Services` – Business logic and third-party integrations
  - `/routes` – API and web routes
  - `/database` – Migrations and seeders
  - `/tests` – Feature and unit tests

## API Documentation

Comprehensive API documentation is available in the `/backend/docs/api.md` file. This includes:

- Authentication endpoints
- Event management endpoints
- Booking and ticket operations
- Payment processing
- User management
- Analytics endpoints

## Getting Started

Please refer to the [RUNNING.md](RUNNING.md) file for detailed instructions on how to set up and run the application.

## Environment Setup

The application requires specific environment variables to be set:

- Database configuration
- Stripe API credentials
- M-Pesa integration keys
- Mail server configuration
- Frontend and backend URLs

See the [ENV_SETUP.md](ENV_SETUP.md) file for detailed configuration instructions.

## Security Considerations

- User Authentication & Authorization via Laravel Sanctum
- Two-Factor Authentication for enhanced security
- Data Encryption for sensitive information
- Role-Based Access Control (RBAC)
- CSRF & XSS Protection
- Rate limiting for sensitive operations
- Input validation and sanitization

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.
