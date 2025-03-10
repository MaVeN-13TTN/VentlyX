# VentlyX Deployment Guide

This document outlines the process of deploying the VentlyX backend to various environments. It provides step-by-step instructions for deployment configurations and best practices.

## Environments

The VentlyX application supports the following environments:

1. **Development** - Local development environment
2. **Testing** - For automated tests and CI/CD pipeline
3. **Staging** - Pre-production environment for testing and QA
4. **Production** - Live environment for end users

## Prerequisites

Before deployment, ensure you have:

-   Access credentials to the target server
-   Required environment variables configured
-   Database migration and seed data ready
-   Proper SSL certificates for production

## Deployment Process

### Local Development Deployment

1. Clone the repository:

    ```bash
    git clone https://github.com/your-organization/ventlyx.git
    cd ventlyx/backend
    ```

2. Install dependencies:

    ```bash
    composer install
    ```

3. Set up environment variables:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Configure database in `.env` file:

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=ventlyx
    DB_USERNAME=root
    DB_PASSWORD=your_password
    ```

5. Run migrations and seeders:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

6. Start the development server:
    ```bash
    php artisan serve
    ```

### Staging/Production Deployment

#### Server Requirements

-   PHP 8.1 or higher
-   Composer
-   MySQL 8.0 or higher
-   Nginx or Apache
-   SSL certificate (Let's Encrypt recommended)
-   Redis (for caching and queues)

#### Manual Deployment Steps

1. **Server Preparation**

    - Install required software:

        ```bash
        sudo apt update
        sudo apt install php8.1-fpm php8.1-mbstring php8.1-xml php8.1-mysql \
        php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath nginx mysql-server redis-server
        ```

    - Configure Nginx:

        ```nginx
        server {
            listen 80;
            server_name api.ventlyx.com;
            root /var/www/ventlyx/backend/public;

            add_header X-Frame-Options "SAMEORIGIN";
            add_header X-Content-Type-Options "nosniff";

            index index.php;

            charset utf-8;

            location / {
                try_files $uri $uri/ /index.php?$query_string;
            }

            location = /favicon.ico { access_log off; log_not_found off; }
            location = /robots.txt  { access_log off; log_not_found off; }

            error_page 404 /index.php;

            location ~ \.php$ {
                fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
                fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
                include fastcgi_params;
            }

            location ~ /\.(?!well-known).* {
                deny all;
            }
        }
        ```

    - Enable HTTPS with Let's Encrypt:
        ```bash
        sudo apt install certbot python3-certbot-nginx
        sudo certbot --nginx -d api.ventlyx.com
        ```

2. **Application Deployment**

    - Clone repository:

        ```bash
        cd /var/www
        git clone https://github.com/your-organization/ventlyx.git
        cd ventlyx/backend
        ```

    - Install dependencies:

        ```bash
        composer install --no-dev --optimize-autoloader
        ```

    - Configure environment:

        ```bash
        cp .env.example .env
        php artisan key:generate
        ```

    - Edit `.env` file with production settings:

        ```
        APP_ENV=production
        APP_DEBUG=false
        APP_URL=https://api.ventlyx.com

        DB_CONNECTION=mysql
        DB_HOST=production-db-host
        DB_PORT=3306
        DB_DATABASE=ventlyx_prod
        DB_USERNAME=db_user
        DB_PASSWORD=secure_password

        CACHE_DRIVER=redis
        SESSION_DRIVER=redis
        QUEUE_CONNECTION=redis

        REDIS_HOST=127.0.0.1
        REDIS_PASSWORD=null
        REDIS_PORT=6379
        ```

    - Run migrations:

        ```bash
        php artisan migrate --force
        ```

    - Optimize application:

        ```bash
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        ```

    - Set permissions:
        ```bash
        sudo chown -R www-data:www-data /var/www/ventlyx
        sudo chmod -R 755 /var/www/ventlyx/backend/storage
        ```

3. **Supervisor Configuration for Queue Workers**

    - Install supervisor:

        ```bash
        sudo apt install supervisor
        ```

    - Create config file:

        ```bash
        sudo nano /etc/supervisor/conf.d/ventlyx-worker.conf
        ```

    - Add configuration:

        ```
        [program:ventlyx-worker]
        process_name=%(program_name)s_%(process_num)02d
        command=php /var/www/ventlyx/backend/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
        autostart=true
        autorestart=true
        stopasgroup=true
        killasgroup=true
        user=www-data
        numprocs=2
        redirect_stderr=true
        stdout_logfile=/var/www/ventlyx/backend/storage/logs/worker.log
        stopwaitsecs=3600
        ```

    - Update supervisor:
        ```bash
        sudo supervisorctl reread
        sudo supervisorctl update
        sudo supervisorctl start all
        ```

4. **Scheduled Tasks**

    - Add Laravel Scheduler to crontab:

        ```bash
        sudo crontab -e
        ```

    - Add the following line:
        ```
        * * * * * cd /var/www/ventlyx/backend && php artisan schedule:run >> /dev/null 2>&1
        ```

### Automated Deployment Using CI/CD

This project is set up to use GitHub Actions for CI/CD. The workflow is configured to:

1. Run tests
2. Build assets
3. Deploy to the appropriate environment based on the branch

#### GitHub Actions Workflow

Create or update `.github/workflows/deploy.yml`:

```yaml
name: Deploy VentlyX Backend

on:
    push:
        branches:
            - main # Production deployment
            - staging # Staging deployment

jobs:
    tests:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v3
            - name: Set up PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: "8.1"
            - name: Install Dependencies
              working-directory: ./backend
              run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress
            - name: Run Tests
              working-directory: ./backend
              run: php artisan test

    deploy:
        needs: tests
        runs-on: ubuntu-latest
        if: github.ref == 'refs/heads/main' || github.ref == 'refs/heads/staging'
        steps:
            - uses: actions/checkout@v3

            - name: Set environment name
              id: env-name
              run: |
                  if [ "${{ github.ref }}" = "refs/heads/main" ]; then
                    echo "ENV_NAME=production" >> $GITHUB_OUTPUT
                  else
                    echo "ENV_NAME=staging" >> $GITHUB_OUTPUT
                  fi

            - name: Deploy to server
              uses: appleboy/ssh-action@master
              with:
                  host: ${{ secrets.HOST_${{ steps.env-name.outputs.ENV_NAME }} }}
                  username: ${{ secrets.USERNAME }}
                  key: ${{ secrets.SSH_KEY }}
                  script: |
                      cd /var/www/ventlyx-${{ steps.env-name.outputs.ENV_NAME }}/backend
                      git pull origin ${{ github.ref_name }}
                      composer install --no-dev --optimize-autoloader
                      php artisan migrate --force
                      php artisan config:cache
                      php artisan route:cache
                      php artisan view:cache
                      sudo supervisorctl restart all
```

## Environment-Specific Configurations

### Development vs Production Settings

| Configuration    | Development | Production |
| ---------------- | ----------- | ---------- |
| APP_DEBUG        | true        | false      |
| LOG_LEVEL        | debug       | error      |
| CACHE_DRIVER     | file        | redis      |
| SESSION_DRIVER   | file        | redis      |
| QUEUE_CONNECTION | sync        | redis      |

### Environment Variables

Create secure environment-specific `.env` files. Critical variables include:

-   `APP_KEY` - Application encryption key
-   `DB_PASSWORD` - Database password
-   `MAIL_PASSWORD` - Mail service password
-   `REDIS_PASSWORD` - Redis password
-   `MPESA_*` - MPesa API credentials
-   `STRIPE_*` - Stripe payment credentials

## Database Management

### Migration Strategy

For zero-downtime migrations:

1. Make all migrations backward compatible when possible
2. For major structural changes:
    - Create new tables/columns first
    - Deploy code that uses both old and new structures
    - Migrate data to the new structure
    - Deploy code that uses only new structures
    - Remove old structures

### Backup Strategy

Automated daily backups:

```bash
# Add to server crontab
0 2 * * * mysqldump -u root -p"password" ventlyx_prod | gzip > /backup/ventlyx-prod-$(date +\%Y\%m\%d).sql.gz
```

## Monitoring and Maintenance

### Health Checks

Implement a health check endpoint at `/api/health` that monitors:

-   Database connectivity
-   Redis connectivity
-   Disk space
-   Memory usage

### Log Management

Configure log rotation:

```bash
sudo nano /etc/logrotate.d/ventlyx
```

Add configuration:

```
/var/www/ventlyx/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
}
```

### Performance Monitoring

-   Use Laravel Telescope in staging environment
-   Consider using New Relic or Datadog for production monitoring
-   Set up alerts for errors and performance degradation

## Rollback Procedures

In case of deployment issues:

1. **Quick Rollback**:

    ```bash
    cd /var/www/ventlyx/backend
    git reset --hard HEAD~1
    composer install --no-dev --optimize-autoloader
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

2. **Database Rollback**:

    ```bash
    php artisan migrate:rollback
    ```

3. **Full Restore from Backup**:
    ```bash
    mysql -u root -p ventlyx_prod < backup_file.sql
    ```

## Security Considerations

1. **SSL Certificate Renewal**:
   Certbot automatically renews certificates. Check renewal status:

    ```bash
    sudo certbot renew --dry-run
    ```

2. **File Permissions**:

    ```bash
    find /var/www/ventlyx/backend -type f -exec chmod 644 {} \;
    find /var/www/ventlyx/backend -type d -exec chmod 755 {} \;
    sudo chown -R www-data:www-data /var/www/ventlyx/backend/storage
    sudo chmod -R 775 /var/www/ventlyx/backend/storage
    ```

3. **Firewall Configuration**:

    ```bash
    sudo ufw allow 22
    sudo ufw allow 80
    sudo ufw allow 443
    sudo ufw enable
    ```

4. **Regular Security Updates**:
    ```bash
    sudo apt update
    sudo apt upgrade
    ```

## Conclusion

Follow this guide to deploy the VentlyX backend across different environments. Always test thoroughly in staging before deploying to production and maintain proper backup procedures.
