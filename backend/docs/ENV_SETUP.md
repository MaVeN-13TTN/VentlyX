# VentlyX Environment Setup Guide

This document provides comprehensive instructions for setting up the VentlyX development environment.

## Prerequisites

-   PHP >= 8.2
-   Composer
-   Node.js >= 18
-   MySQL >= 8.0 or PostgreSQL >= 14
-   Redis (optional, for caching and queues)
-   Git

## Installation Steps

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/ventlyx.git
cd ventlyx
```

### 2. Backend Setup

1. Navigate to backend directory:

```bash
cd backend
```

2. Install PHP dependencies:

```bash
composer install
```

3. Copy environment file:

```bash
cp .env.example .env
```

4. Configure environment variables in `.env`:

```env
# Application
APP_NAME=VentlyX
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ventlyx
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=null
MAIL_FROM_NAME="${APP_NAME}"

# Payment Integration
STRIPE_KEY=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key

# M-Pesa Integration
MPESA_CONSUMER_KEY=your_mpesa_consumer_key
MPESA_CONSUMER_SECRET=your_mpesa_consumer_secret
MPESA_PASSKEY=your_mpesa_passkey
MPESA_SHORTCODE=your_mpesa_shortcode
MPESA_ENV=sandbox

# File Storage
FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=

# Queue Configuration
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Broadcasting Configuration
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
```

5. Generate application key:

```bash
php artisan key:generate
```

6. Create database:

```bash
mysql -u root -p
CREATE DATABASE ventlyx;
```

7. Run migrations:

```bash
php artisan migrate
```

8. Seed the database (optional):

```bash
php artisan db:seed
```

9. Link storage:

```bash
php artisan storage:link
```

10. Start the development server:

```bash
php artisan serve
```

### 3. Queue Setup (Optional)

If using Redis for queues:

1. Install Redis server (Ubuntu example):

```bash
sudo apt-get install redis-server
```

2. Start Redis:

```bash
sudo systemctl start redis-server
```

3. Start queue worker:

```bash
php artisan queue:work
```

## Development Tools

### Code Style & Analysis

1. PHP CS Fixer for code style:

```bash
./vendor/bin/php-cs-fixer fix
```

2. Static analysis with PHPStan:

```bash
./vendor/bin/phpstan analyse
```

### Testing

1. Create testing database:

```bash
mysql -u root -p
CREATE DATABASE ventlyx_testing;
```

2. Configure testing environment:

```bash
cp .env .env.testing
```

3. Update `.env.testing`:

```env
APP_ENV=testing
DB_DATABASE=ventlyx_testing
```

4. Run tests:

```bash
php artisan test
```

## Troubleshooting

### Common Issues

1. Storage Permissions:

```bash
chmod -R 775 storage bootstrap/cache
sudo chown -R $USER:www-data storage bootstrap/cache
```

2. Redis Connection Issues:

-   Check Redis service: `sudo systemctl status redis`
-   Verify Redis connection: `redis-cli ping`

3. Database Connection Issues:

-   Check credentials in `.env`
-   Verify database exists
-   Check MySQL service: `sudo systemctl status mysql`

### Environment Validation

Run this command to verify your setup:

```bash
php artisan ventlyx:check-environment
```

## Production Deployment

1. Set production environment variables:

```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize application:

```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Security checklist:

-   [ ] Generate strong application key
-   [ ] Set secure database credentials
-   [ ] Configure HTTPS
-   [ ] Set proper file permissions
-   [ ] Configure CORS settings
-   [ ] Set up rate limiting
-   [ ] Enable security headers
-   [ ] Configure session security
-   [ ] Set up backup system
-   [ ] Configure error logging

## Support

For additional help:

-   Check our [Troubleshooting Guide](TROUBLESHOOTING.md)
-   Review [API Documentation](api.md)
-   Raise an issue in the repository
-   Contact the development team

## Contributing

Please see [Contributing Guide](../../CONTRIBUTING.md) for details on contributing to the project.
