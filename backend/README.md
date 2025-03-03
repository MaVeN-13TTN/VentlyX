<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# VentlyX Backend

## Overview

This is the backend component of the VentlyX application, built with Laravel. It provides RESTful API endpoints for the frontend to interact with, manages the database, and handles all business logic.

## Technology Stack

-   **PHP Framework**: Laravel 12.x
-   **Authentication**: Laravel Sanctum
-   **QR Code Generation**: Simple QR Code Package
-   **Payment Processing**: Stripe PHP SDK
-   **Database**: MySQL/PostgreSQL

## Key Features Implemented

-   User authentication and authorization
-   Event management system
-   Ticket booking and payment processing
-   QR code ticket generation
-   Admin dashboard and analytics
-   Role-based access control

## Directory Structure

-   `app/Models`: Contains database models (User, Event, Ticket, etc.)
-   `app/Http/Controllers`: Contains API controllers
-   `app/Http/Middleware`: Contains middleware for authentication and authorization
-   `database/migrations`: Contains database schema migrations
-   `routes`: Contains API route definitions
-   `config`: Contains configuration files

## API Endpoints

The backend provides the following API endpoints:

### Authentication

-   `POST /api/register`: Register a new user
-   `POST /api/login`: Authenticate a user
-   `POST /api/logout`: Log out a user

### Events

-   `GET /api/events`: Get all events
-   `GET /api/events/{id}`: Get a specific event
-   `POST /api/events`: Create a new event (Admin only)
-   `PUT /api/events/{id}`: Update an event (Admin only)
-   `DELETE /api/events/{id}`: Delete an event (Admin only)

### Bookings

-   `GET /api/bookings`: Get user's bookings
-   `POST /api/bookings`: Create a new booking
-   `GET /api/bookings/{id}`: Get a specific booking
-   `GET /api/bookings/{id}/ticket`: Generate ticket QR code

### Admin

-   `GET /api/admin/users`: Get all users (Admin only)
-   `GET /api/admin/bookings`: Get all bookings (Admin only)
-   `GET /api/admin/analytics`: Get analytics data (Admin only)

## Environment Variables

See `.env.example` for required environment variables.

## See Also

-   [RUNNING.md](../RUNNING.md) for setup and running instructions
-   [Frontend README](../frontend/README.md) for frontend documentation

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
