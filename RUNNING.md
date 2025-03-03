# Setting Up and Running VentlyX

This guide provides step-by-step instructions for setting up and running both the backend (Laravel) and frontend (Vue.js) components of the VentlyX application.

## Prerequisites

### System Requirements

- PHP 8.2 or higher
- Composer 2.0 or higher
- Node.js 18.0 or higher
- NPM 7.0 or higher
- PostgreSQL 12+ (recommended)

### Required Software

- Git
- A web server (Apache, Nginx, or PHP's built-in server)
- PostgreSQL database server
- A text editor or IDE (Visual Studio Code, PhpStorm, etc.)

## Backend Setup (Laravel)

### 1. Clone the Repository

```bash
git clone <repository-url>
cd VentlyX
```

### 2. Install PHP Dependencies

```bash
cd backend
composer install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Now, edit the `.env` file to configure your PostgreSQL database connection:

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=ventlyx_db
DB_USERNAME=ventlyx_user
DB_PASSWORD=your_secure_password
```

For Stripe and PayPal integration, add your API keys:

```
STRIPE_KEY=your_stripe_public_key
STRIPE_SECRET=your_stripe_secret_key
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_SECRET=your_paypal_secret
```

### 4. Create the PostgreSQL Database and User

```bash
# Login to PostgreSQL as postgres user
sudo -u postgres psql

# Create the database
CREATE DATABASE ventlyx_db;

# Create a user with a secure password
CREATE USER ventlyx_user WITH PASSWORD 'your_secure_password';

# Grant privileges to the user on the database
GRANT ALL PRIVILEGES ON DATABASE ventlyx_db TO ventlyx_user;

# Grant schema privileges
\c ventlyx_db
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO ventlyx_user;
GRANT ALL ON SCHEMA public TO ventlyx_user;

# Exit PostgreSQL
\q
```

### 5. PostgreSQL Authentication Configuration (Optional)

If you encounter authentication issues, you may need to modify the PostgreSQL authentication method in the pg_hba.conf file:

```bash
# Find the PostgreSQL configuration file
sudo find /etc/postgresql -name "pg_hba.conf"

# Edit the file (replace with your actual path)
sudo nano /etc/postgresql/[version]/main/pg_hba.conf

# Change the authentication method for local connections from 'scram-sha-256' to 'md5'
# Then restart PostgreSQL
sudo systemctl restart postgresql
```

### 6. Run Migrations and Seed the Database

```bash
php artisan migrate
php artisan db:seed
```

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Start the Laravel Development Server

```bash
php artisan serve
```

The Laravel backend will be available at http://localhost:8000.

## Frontend Setup (Vue.js)

### 1. Navigate to the Frontend Directory

```bash
cd ../frontend/VentlyX
```

### 2. Install Node Dependencies

```bash
npm install
```

### 3. Configure API Base URL

Create a `.env` file in the frontend directory:

```bash
echo 'VITE_API_BASE_URL=http://localhost:8000/api' > .env
```

### 4. Start the Development Server

```bash
npm run dev
```

The Vue.js frontend will be available at http://localhost:5173.

## Running Both Services Simultaneously

For convenience, you can use a tool like Concurrently to run both servers at once:

```bash
# Install concurrently globally if you haven't already
npm install -g concurrently

# From the project root directory
concurrently "cd backend && php artisan serve" "cd frontend/VentlyX && npm run dev"
```

## Testing

### Backend Tests

```bash
cd backend
php artisan test
```

### Frontend Tests

```bash
cd frontend/VentlyX
# Run unit tests
npm run test:unit
# Run end-to-end tests
npm run test:e2e
```

## Building for Production

### Backend

No specific build process is needed for the Laravel backend in most cases. Just ensure:

1. Set `APP_ENV=production` and `APP_DEBUG=false` in your `.env` file
2. Optimize the application: `php artisan optimize`

### Frontend

```bash
cd frontend/VentlyX
npm run build
```

This will generate a `dist` directory with static assets that can be served by any web server.

## Deployment Considerations

### Database Migrations

Always run migrations when deploying to a new environment:

```bash
php artisan migrate --force
```

### Storage Permissions

Ensure proper permissions for the storage directory:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Web Server Configuration

For Apache, ensure the `.htaccess` file is properly configured.
For Nginx, use the configuration example in the Laravel documentation.

### Environment Variables

Make sure all required environment variables are set in your production environment.

## Troubleshooting

### Common Backend Issues

- "No application encryption key has been specified." - Run `php artisan key:generate`
- Database connection issues - Check your database credentials and connection in `.env`
- PostgreSQL authentication errors - Verify authentication method in pg_hba.conf (may need to change from scram-sha-256 to md5)
- Permission issues - Check file permissions on storage and bootstrap/cache

### Common Frontend Issues

- API connection errors - Ensure the backend URL in `.env` is correct
- CORS issues - Check Laravel CORS configuration
- Node module issues - Try deleting node_modules and reinstalling with `npm install`

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue.js Documentation](https://vuejs.org/guide/introduction.html)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Pinia Documentation](https://pinia.vuejs.org/)
