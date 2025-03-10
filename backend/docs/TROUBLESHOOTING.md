# VentlyX Troubleshooting Guide

This guide covers common issues you might encounter while developing or deploying VentlyX.

## Table of Contents

1. [Installation Issues](#installation-issues)
2. [Database Issues](#database-issues)
3. [Authentication Issues](#authentication-issues)
4. [Payment Integration Issues](#payment-integration-issues)
5. [Performance Issues](#performance-issues)
6. [Deployment Issues](#deployment-issues)

## Installation Issues

### Composer Install Fails

```
Problem: composer install fails with memory limit error
Solution:
1. Increase PHP memory limit in php.ini
2. Run composer with increased memory: php -d memory_limit=-1 composer install
```

### Node Dependencies

```
Problem: npm install fails with node-gyp errors
Solution:
1. Install build essentials: sudo apt-get install build-essential
2. Clear npm cache: npm cache clean --force
3. Try again: npm install
```

## Database Issues

### Migration Failures

```
Problem: Migrations fail to run
Solutions:
1. Check database credentials in .env
2. Ensure database exists: mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ventlyx"
3. Reset migrations: php artisan migrate:fresh
```

### Query Performance

```
Problem: Slow database queries
Solutions:
1. Check indexes: php artisan db:show
2. Run query analyzer: EXPLAIN [your-query]
3. Enable query log: DB::enableQueryLog()
```

## Authentication Issues

### Token Issues

```
Problem: Invalid/expired tokens
Solutions:
1. Clear token cache: php artisan cache:clear
2. Reset password grant: php artisan passport:client --password
```

### Session Problems

```
Problem: Session not persisting
Solutions:
1. Check session driver in .env
2. Verify session directory permissions
3. Clear session files: php artisan session:clear
```

## Payment Integration Issues

### Stripe Integration

```
Problem: Stripe payments failing
Solutions:
1. Verify Stripe keys in .env
2. Enable Stripe logging
3. Check webhook configuration
```

### M-Pesa Integration

```
Problem: M-Pesa callbacks not received
Solutions:
1. Verify M-Pesa credentials
2. Check ngrok tunnel (development)
3. Validate callback URL
```

## Performance Issues

### Slow API Responses

```
Problem: API endpoints responding slowly
Solutions:
1. Enable route caching: php artisan route:cache
2. Enable config caching: php artisan config:cache
3. Check N+1 queries using Laravel Debugbar
```

### Memory Issues

```
Problem: High memory usage
Solutions:
1. Enable OPcache
2. Implement pagination
3. Use lazy loading where appropriate
```

## Deployment Issues

### Server Configuration

```
Problem: 500 errors after deployment
Solutions:
1. Check storage permissions
2. Verify .env exists
3. Generate application key
```

### SSL/HTTPS Issues

```
Problem: Mixed content warnings
Solutions:
1. Update APP_URL in .env
2. Force HTTPS in production
3. Check asset URLs
```

## Queue Worker Issues

### Failed Jobs

```
Problem: Queue jobs failing
Solutions:
1. Check failed_jobs table
2. Monitor queue worker: supervisorctl status
3. View logs: tail -f storage/logs/laravel.log
```

### Redis Connection

```
Problem: Redis connection refused
Solutions:
1. Check Redis service: systemctl status redis
2. Verify Redis credentials
3. Test connection: redis-cli ping
```

## Common Error Codes

-   `E001`: Database connection failed
-   `E002`: Payment gateway error
-   `E003`: Authentication failed
-   `E004`: Rate limit exceeded
-   `E005`: Invalid input data
-   `E006`: External service unavailable

## Debugging Tools

1. Laravel Telescope (Development)

    ```bash
    composer require laravel/telescope --dev
    php artisan telescope:install
    ```

2. Laravel Debugbar

    ```bash
    composer require barryvdh/laravel-debugbar --dev
    ```

3. Query Logging
    ```php
    \DB::enableQueryLog();
    // Your code here
    dd(\DB::getQueryLog());
    ```

## Support Channels

1. GitHub Issues: Report bugs and feature requests
2. Documentation: Check API and setup guides
3. Stack Overflow: Tag questions with 'ventlyx'
4. Discord Community: Join our developer channel

## Maintenance Mode

To put application in maintenance mode:

```bash
php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"
```

To exit maintenance mode:

```bash
php artisan up
```
