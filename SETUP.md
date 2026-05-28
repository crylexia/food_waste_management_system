# EcoManager Food and Waste Management System — Setup Guide

## Overview

EcoManager is a Laravel-based multi-user web application for tracking food inventory usage and waste. It features user authentication, item management, daily entry tracking with waste reason logging, historical records, comprehensive analytics (descriptive, diagnostic, prescriptive, and predictive), and storage/expiration monitoring.

---

## Features

- **User Authentication** — Secure login/logout with session management and post-login redirect
- **Item Management** — Create, edit, delete, and categorize food items with unit and price
- **Daily Entry System** — Record daily usage, waste quantities, and waste reasons per item
- **Historical Records** — View and sort past entries by date or item name
- **Analytics Engine** — Descriptive stats, BI layer (KPIs, root cause, recommendations), and predictive forecasts
- **Storage Monitoring** — Track inventory batches with expiration dates; dashboard expiry alerts
- **Multi-User Support** — Complete data isolation between users via global Eloquent scopes
- **Clean UI** — Minimalist green-and-white theme, responsive layout

---

## Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Composer
- Node.js and NPM (optional — only needed to recompile frontend assets)

---

## Installation Steps

### 1. Install PHP Dependencies

```bash
composer install
```

> The `vendor/` folder is not included in source control. This step is required before running any `php artisan` commands.

### 2. Configure Environment

Copy the example environment file:

```bash
cp .env.example .env
```

Update the database section in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecomanager
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Set the session driver to database:

```env
SESSION_DRIVER=database
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Create the Database

In MySQL, create the database you specified in `.env`:

```sql
CREATE DATABASE ecomanager;
```

### 5. Run Migrations

```bash
php artisan migrate
```

This creates all required tables:

| Table | Purpose |
|---|---|
| `users` | User accounts |
| `items` | Food items |
| `daily_entries` | Daily tracking entries |
| `entry_items` | Items within entries (usage, waste, reason) |
| `storage_items` | Inventory batches with expiration dates |
| `sessions` | Database-backed session storage |

### 6. Seed Test Users

```bash
php artisan db:seed
```

Creates two test accounts:

| Email | Password |
|---|---|
| `test@example.com` | `password` |
| `demo@example.com` | `password` |

### 7. Start the Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`.

---

## Usage Guide

### Login
1. Navigate to `http://localhost:8000` — you'll be redirected to the login page
2. Use one of the test accounts above

### Dashboard
The dashboard is designed for a 30-second daily review:
- Check today's waste value and quantity
- Check the weekly trend arrow — is waste going up or down?
- Review any red/orange expiry or waste alerts
- Use Quick Actions to log or review

### Item Management
1. Click **Items** in the sidebar
2. Click **+ New Item** and enter name, category, unit, and optional price
3. Edit or delete items from the list (items in use cannot be deleted)

### Daily Entries
1. Click **Daily Entry** in the sidebar
2. Click **+ New Entry** and select a date
3. Add items with used and wasted quantities
4. Select a waste reason for each item (overproduced, leftover, expired, spoiled, other)
5. Add optional notes
6. The waste rating is computed automatically

### Records Viewer
1. Click **Records** in the sidebar
2. Sort by date or item name
3. View the detailed breakdown of each entry

### Analytics
1. Click **Analytics** in the sidebar
2. Review the Efficiency Score and Decision Cards for a quick orientation
3. Drill into Root Cause Analysis to understand why waste is occurring
4. Follow the Recommendation Engine — High priority items first
5. Check Predictive Analytics for forecasted waste and procurement suggestions
6. Use date range and period filters to compare time windows

### Storage & Expiration Monitoring
1. Click **Storage** in the sidebar
2. Select an item, enter quantity, and set an expiration date
3. Optionally add a batch/lot number, received date, and notes
4. Click **Add to Storage**
5. Mark batches as depleted when consumed, or discarded when thrown away
6. Expired and near-expiry batches are surfaced as alerts on the Dashboard

---

## Database Schema Reference

### `items`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Owner — scoped by UserScope |
| `name` | string | Unique per user |
| `category` | string | |
| `unit` | string | e.g. kg, pcs, litre |
| `price` | decimal | Price per unit |

### `daily_entries`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Owner — scoped by UserScope |
| `date` | date | Unique per user |

### `entry_items`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `daily_entry_id` | bigint FK | CASCADE delete |
| `item_id` | bigint FK | RESTRICT delete |
| `used_quantity` | decimal | |
| `wasted_quantity` | decimal | |
| `waste_reason` | string nullable | overproduced, leftover, expired, spoiled, other — drives root cause analysis |
| `notes` | string nullable | |

### `storage_items`
| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Owner — scoped by UserScope |
| `item_id` | bigint FK | |
| `quantity` | decimal | |
| `expiration_date` | date nullable | |
| `received_date` | date nullable | |
| `batch_number` | string nullable | |
| `notes` | string nullable | |
| `status` | enum | active, depleted, discarded |

---

## Multi-User Data Isolation

The `UserScope` global scope is applied to the `Item`, `DailyEntry`, and `StorageItem` models. Every Eloquent query on these models is automatically filtered to the authenticated user's ID. Route-model binding returns a 404 if a URL references a record owned by a different user, preventing cross-user data access (IDOR).

---

## Security Features

- CSRF protection on all forms
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade escaping
- Password hashing with bcrypt
- Session security (HTTP-only, encrypted, database-backed)
- All routes — including storage — require authentication via `middleware('auth')`
- `UserScope` enforces data isolation on `Item`, `DailyEntry`, and `StorageItem`
- `EntryItem` deletion verified against parent entry ownership before proceeding
- Input validation on all forms

---

## Running Tests

```bash
php artisan test
```

| Suite | Tests |
|---|---|
| `AuthenticationTest` | 10 |
| `ItemManagementTest` | 5 |
| `StorageControllerTest` | 13 |
| `UserScopeTest` | 6 |
| `DailyEntryTest` | 7 |
| `EntryItemTest` | 11 |
| **Total** | **52** |

---

## Troubleshooting

### `vendor/autoload.php` not found
Run `composer install` first. The vendor folder is excluded from source control.

### Database connection error
Verify your MySQL server is running and the credentials in `.env` match your setup.

### Session not working
Ensure migrations have been run to create the `sessions` table:
```bash
php artisan migrate
```

### Permission errors (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
```

### Clearing cache after config changes
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Production Deployment

1. Set `APP_ENV=production` and `APP_DEBUG=false` in `.env`
2. Configure proper database credentials
3. Set up SSL
4. Run optimisation commands:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Support

Refer to the design and requirements documents in `.kiro/specs/eco-manager-food-waste-system/` for full specification details.