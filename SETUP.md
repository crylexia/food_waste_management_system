# EcoManager Food and Waste Management System - Setup Guide

## Overview

EcoManager is a Laravel-based multi-user web application for tracking food inventory usage and waste. It features user authentication, item management, daily entry tracking, historical records viewing, and comprehensive analytics.

## Features

- **User Authentication**: Secure login/logout with session management
- **Item Management**: Create, edit, delete, and categorize food items
- **Daily Entry System**: Record daily usage and waste quantities for items
- **Historical Records**: View and sort past entries by date or item name
- **Analytics Engine**: Calculate waste ratings, rankings, and time-based statistics
- **Multi-User Support**: Complete data isolation between users
- **Clean UI**: Minimalist green-and-white theme

## Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js and NPM (optional, for asset compilation)

## Installation Steps

### 1. Install Dependencies

```bash
composer install
```

### 2. Configure Environment

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Update the database configuration in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecomanager
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Update the session driver to use database:

```
SESSION_DRIVER=database
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Create Database

Create a MySQL database named `ecomanager` (or whatever you specified in `.env`):

```sql
CREATE DATABASE ecomanager;
```

### 5. Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- users
- items
- daily_entries
- entry_items
- sessions

### 6. Seed Test Users

```bash
php artisan db:seed
```

This creates two test users:
- Email: `test@example.com`, Password: `password`
- Email: `demo@example.com`, Password: `password`

### 7. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Usage

### Login

1. Navigate to `http://localhost:8000`
2. You'll be redirected to the login page
3. Use one of the test accounts:
   - Email: `test@example.com`
   - Password: `password`

### Dashboard

After logging in, you'll see the dashboard with:
- Quick statistics (total items, total entries, average waste rate)
- Recent entries list

### Item Management

1. Click "Items" in the sidebar
2. Create new items with name, category, and optional price
3. Edit or delete existing items
4. Filter items by category or search by name

### Daily Entries

1. Click "Daily Entry" in the sidebar
2. Create a new entry for a specific date
3. Add items to the entry with used and wasted quantities
4. Add optional notes for each item
5. View the waste rating for the entry

### Records Viewer

1. Click "Records" in the sidebar
2. View all historical entries
3. Sort by date or item name
4. See detailed breakdown of each entry

### Analytics

1. Click "Analytics" in the sidebar
2. View most wasted items
3. View most used items
4. Compare usage vs waste
5. Filter by time period (daily, weekly, monthly)
6. Filter by date range

## Database Schema

### Users Table
- id, name, email, password, remember_token, timestamps

### Items Table
- id, user_id (FK), name, category, price, timestamps
- Unique constraint on (user_id, name)

### Daily Entries Table
- id, user_id (FK), date, timestamps
- Unique constraint on (user_id, date)

### Entry Items Table
- id, daily_entry_id (FK), item_id (FK), used_quantity, wasted_quantity, notes, timestamps
- CASCADE delete on daily_entry_id
- RESTRICT delete on item_id

### Sessions Table
- id, user_id, ip_address, user_agent, payload, last_activity

## Multi-User Data Isolation

The application uses a global scope (`UserScope`) to automatically filter all queries by the authenticated user's ID. This ensures:
- Users can only see their own data
- No data leakage between users
- Simple and secure implementation

## Security Features

- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade escaping
- Password hashing with bcrypt
- Session security (HTTP-only, encrypted)
- User data isolation via global scopes

## Troubleshooting

### Database Connection Error

Make sure your MySQL server is running and the credentials in `.env` are correct.

### Session Not Working

Make sure you've run the migrations to create the sessions table:

```bash
php artisan migrate
```

### Permission Errors

Make sure the `storage` and `bootstrap/cache` directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
```

## Development

### Running Tests

```bash
php artisan test
```

### Clearing Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Creating New Users

You can create new users via the database seeder or manually in the database. Passwords must be hashed using bcrypt.

## Production Deployment

For production deployment:

1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure proper database credentials
4. Set up SSL certificate
5. Run optimization commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Support

For issues or questions, please refer to the design document or requirements document in the `.kiro/specs/eco-manager-food-waste-system/` directory.
