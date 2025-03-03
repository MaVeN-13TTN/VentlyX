# VentlyX

## Project Overview

VentlyX is a comprehensive web-based application designed to facilitate seamless booking and management of events, concerts, and shows. The platform provides an intuitive interface for users to explore and book events while enabling administrators to efficiently manage event listings, user registrations, and ticketing operations.

## Target Audience

- **Event Attendees** – Users looking to browse and book tickets for events
- **Event Organizers** – Hosts who want to list events and manage bookings
- **Administrators** – System managers responsible for maintaining the platform

## Key Features

### User Side (Attendees)

- **Homepage with Event Listings** – Display a carousel of upcoming events
- **Event Details Page** – Users can view event location, time, price tiers, and descriptions
- **User Registration & Authentication** – Users sign up using email/password or social login (Google, Facebook)
- **Ticket Booking System** – Select ticket type (Individual, Group, VIP, VVIP) and make a payment
- **QR Code Ticket Generation** – Upon booking confirmation, users receive a QR code for event check-in
- **Payment Integration** – Secure payments via Stripe and PayPal
- **Booking History & Notifications** – Users can view past bookings and receive notifications

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
| Frontend            | Vue.js + Tailwind CSS                      |
| Backend             | Laravel (PHP) + Sanctum for authentication |
| Database            | MySQL or PostgreSQL                        |
| Authentication      | Laravel Sanctum, OAuth (Google, Facebook)  |
| Payments            | Stripe, PayPal                             |
| QR Code & Ticketing | Laravel QR Code Package                    |
| Real-Time Updates   | Laravel Echo + Pusher                      |

## System Architecture

The application follows an MVC (Model-View-Controller) architecture:

- **Frontend Layer** – Vue.js with TypeScript
- **Backend Layer** – Laravel handles business logic, database queries, and authentication
- **Database Layer** – MySQL/PostgreSQL for structured data storage
- **API Layer** – RESTful APIs for communication between frontend and backend

## Project Structure

- `/frontend` – Contains the Vue.js application
- `/backend` – Contains the Laravel application

## Getting Started

Please refer to the [RUNNING.md](RUNNING.md) file for detailed instructions on how to set up and run the application.

## Security Considerations

- User Authentication & Authorization via Laravel Sanctum and OAuth
- Data Encryption for sensitive information
- Role-Based Access Control (RBAC)
- CSRF & XSS Protection
