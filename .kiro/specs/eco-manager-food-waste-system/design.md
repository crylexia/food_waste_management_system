# Design Document: EcoManager Food and Waste Management System

## Overview

The EcoManager Food and Waste Management System is a multi-user Laravel web application that enables food shop owners (cafeterias, bakeries, etc.) to track food inventory usage and waste. The system provides separate data isolation for each user, allowing multiple businesses to use the same application instance while maintaining complete data privacy.

### Core Functionality

- **User Authentication**: Secure login/logout with session management
- **Item Management**: Create, edit, delete, and categorize food items
- **Daily Entry System**: Record daily usage and waste quantities for items
- **Historical Records**: View and sort past entries by date or item name
- **Analytics Engine**: Calculate waste ratings, rankings, and time-based statistics

### Technology Stack

- **Backend**: Laravel (PHP framework) with MVC architecture
- **Database**: MySQL with Eloquent ORM
- **Frontend**: HTML/CSS with minimalist green-and-white theme
- **Authentication**: Laravel's built-in authentication system

### Design Principles

- Clean, minimalist interface focused on functionality
- Efficient layouts for quick data entry by busy shop owners
- Multi-user data isolation at the database level
- RESTful API design following Laravel conventions
- Responsive design for various screen sizes


## Architecture

### System Architecture Overview

The EcoManager follows Laravel's MVC (Model-View-Controller) architecture with clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                         Browser (Client)                     │
│                    HTML/CSS/JavaScript                       │
└──────────────────────────┬──────────────────────────────────┘
                           │ HTTP Requests
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      Laravel Application                     │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                    Routes (web.php)                    │  │
│  │              Route definitions & middleware            │  │
│  └─────────────────────────┬─────────────────────────────┘  │
│                            │                                 │
│  ┌─────────────────────────▼─────────────────────────────┐  │
│  │                     Controllers                        │  │
│  │  - AuthController                                      │  │
│  │  - DashboardController                                 │  │
│  │  - ItemController                                      │  │
│  │  - DailyEntryController                                │  │
│  │  - RecordController                                    │  │
│  │  - AnalyticsController                                 │  │
│  └─────────────────────────┬─────────────────────────────┘  │
│                            │                                 │
│  ┌─────────────────────────▼─────────────────────────────┐  │
│  │                   Business Logic                       │  │
│  │  - Services (Analytics calculations, etc.)             │  │
│  │  - Validation (Form Requests)                          │  │
│  └─────────────────────────┬─────────────────────────────┘  │
│                            │                                 │
│  ┌─────────────────────────▼─────────────────────────────┐  │
│  │                  Models (Eloquent ORM)                 │  │
│  │  - User                                                │  │
│  │  - Item                                                │  │
│  │  - DailyEntry                                          │  │
│  │  - EntryItem                                           │  │
│  └─────────────────────────┬─────────────────────────────┘  │
│                            │                                 │
│  ┌─────────────────────────▼─────────────────────────────┐  │
│  │                    Views (Blade)                       │  │
│  │  - layouts/app.blade.php                               │  │
│  │  - auth/login.blade.php                                │  │
│  │  - dashboard.blade.php                                 │  │
│  │  - items/index.blade.php, create.blade.php, etc.       │  │
│  │  - entries/index.blade.php, create.blade.php, etc.     │  │
│  │  - records/index.blade.php                             │  │
│  │  - analytics/index.blade.php                           │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────┬──────────────────────────────────┘
                           │ SQL Queries
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                       MySQL Database                         │
│  - users                                                     │
│  - items                                                     │
│  - daily_entries                                             │
│  - entry_items                                               │
└─────────────────────────────────────────────────────────────┘
```

### Multi-User Data Isolation Strategy

Each user's data is isolated using a **user_id foreign key** approach:

1. **User Model**: Central authentication entity
2. **Ownership Pattern**: All primary entities (items, daily_entries) include a `user_id` column
3. **Global Scopes**: Laravel global scopes automatically filter queries by authenticated user
4. **Middleware**: Authentication middleware ensures only logged-in users access protected routes
5. **Query Filtering**: All database queries automatically include `WHERE user_id = ?` clause

This approach provides:
- Complete data isolation between users
- Single database instance (cost-effective)
- Simple backup and maintenance
- Easy to implement and maintain


### Directory Structure

```
eco-manager/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ItemController.php
│   │   │   ├── DailyEntryController.php
│   │   │   ├── RecordController.php
│   │   │   └── AnalyticsController.php
│   │   ├── Middleware/
│   │   │   └── Authenticate.php
│   │   └── Requests/
│   │       ├── StoreItemRequest.php
│   │       ├── UpdateItemRequest.php
│   │       ├── StoreDailyEntryRequest.php
│   │       └── StoreEntryItemRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Item.php
│   │   ├── DailyEntry.php
│   │   └── EntryItem.php
│   ├── Services/
│   │   └── AnalyticsService.php
│   └── Scopes/
│       └── UserScope.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000000_create_users_table.php
│       ├── 2024_01_01_000001_create_items_table.php
│       ├── 2024_01_01_000002_create_daily_entries_table.php
│       └── 2024_01_01_000003_create_entry_items_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       ├── auth/
│       │   └── login.blade.php
│       ├── dashboard.blade.php
│       ├── items/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       ├── entries/
│       │   ├── index.blade.php
│       │   └── create.blade.php
│       ├── records/
│       │   └── index.blade.php
│       └── analytics/
│           └── index.blade.php
├── routes/
│   └── web.php
└── public/
    └── css/
        └── app.css
```


## Components and Interfaces

### 1. Authentication System

**Purpose**: Manage user login, logout, and session state

**Components**:
- `AuthController`: Handles login/logout requests
- `User` Model: Represents authenticated users
- `Authenticate` Middleware: Protects routes requiring authentication

**Key Methods**:
```php
// AuthController
public function showLoginForm(): View
public function login(Request $request): RedirectResponse
public function logout(Request $request): RedirectResponse
```

**Flow**:
1. User visits `/login` → `showLoginForm()` displays login page
2. User submits credentials → `login()` validates and creates session
3. On success → redirect to `/dashboard`
4. On failure → return to login with error message
5. User clicks logout → `logout()` destroys session and redirects to login

**Session Management**:
- Laravel's built-in session handling
- Session stored in database or file system
- CSRF protection on all forms

### 2. Dashboard Module

**Purpose**: Display summary information and provide navigation

**Components**:
- `DashboardController`: Fetches and prepares dashboard data
- `dashboard.blade.php`: Renders dashboard view

**Key Methods**:
```php
// DashboardController
public function index(): View
```

**Data Displayed**:
- Recent daily entries (last 5-10 entries)
- Summary statistics (total items, total entries)
- Quick waste rating overview
- Navigation links to all modules

**UI Layout**:
```
┌─────────────────────────────────────────────────────────┐
│ EcoManager                              [Logout]        │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Dashboard                                   ┌────────┐ │
│                                              │ Dash   │ │
│  Recent Entries:                             │ Items  │ │
│  ┌──────────────────────────────────┐        │ Entry  │ │
│  │ 2024-01-15 | 5 items | 12% waste│        │ Record │ │
│  │ 2024-01-14 | 8 items | 8% waste │        │ Analyt │ │
│  │ 2024-01-13 | 6 items | 15% waste│        └────────┘ │
│  └──────────────────────────────────┘                   │
│                                                         │
│  Quick Stats:                                           │
│  Total Items: 25                                        │
│  Total Entries: 45                                      │
│  Avg Waste Rate: 10.5%                                  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```


### 3. Item Management Module

**Purpose**: CRUD operations for food items

**Components**:
- `ItemController`: Handles item operations
- `Item` Model: Represents food items
- `StoreItemRequest`, `UpdateItemRequest`: Validation rules
- Views: `items/index.blade.php`, `items/create.blade.php`, `items/edit.blade.php`

**Key Methods**:
```php
// ItemController
public function index(): View                          // List all items
public function create(): View                         // Show create form
public function store(StoreItemRequest $request): RedirectResponse
public function edit(Item $item): View                 // Show edit form
public function update(UpdateItemRequest $request, Item $item): RedirectResponse
public function destroy(Item $item): RedirectResponse
```

**Validation Rules**:
```php
// StoreItemRequest
'name' => 'required|string|max:255|unique:items,name,NULL,id,user_id,' . auth()->id()
'category' => 'required|string|max:100'
'price' => 'nullable|numeric|min:0'

// UpdateItemRequest
'name' => 'required|string|max:255|unique:items,name,' . $item->id . ',id,user_id,' . auth()->id()
'category' => 'required|string|max:100'
'price' => 'nullable|numeric|min:0'
```

**UI Layout - Item List**:
```
┌─────────────────────────────────────────────────────────┐
│ Item Management                          [+ New Item]   │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Filter by Category: [All ▼]                            │
│                                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Name          │ Category    │ Price  │ Actions   │  │
│  ├───────────────────────────────────────────────────┤  │
│  │ Bread         │ Product     │ $2.50  │ Edit Del  │  │
│  │ Flour         │ Ingredient  │ $1.20  │ Edit Del  │  │
│  │ Croissant     │ Product     │ $3.00  │ Edit Del  │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

**Deletion Logic**:
- Check if item is referenced in `entry_items` table
- If referenced: prevent deletion, show error message
- If not referenced: delete item, show success message

### 4. Daily Entry System Module

**Purpose**: Create daily entries and add items with quantities

**Components**:
- `DailyEntryController`: Handles entry operations
- `DailyEntry` Model: Represents daily entries
- `EntryItem` Model: Represents items within entries
- `StoreDailyEntryRequest`, `StoreEntryItemRequest`: Validation rules
- Views: `entries/index.blade.php`, `entries/create.blade.php`

**Key Methods**:
```php
// DailyEntryController
public function index(): View                          // List user's entries
public function create(): View                         // Show create form
public function store(StoreDailyEntryRequest $request): RedirectResponse
public function show(DailyEntry $entry): View          // Show entry details
public function addItem(StoreEntryItemRequest $request, DailyEntry $entry): RedirectResponse
public function removeItem(EntryItem $entryItem): RedirectResponse
```

**Validation Rules**:
```php
// StoreDailyEntryRequest
'date' => 'required|date|unique:daily_entries,date,NULL,id,user_id,' . auth()->id()

// StoreEntryItemRequest
'item_id' => 'required|exists:items,id'
'used_quantity' => 'required|numeric|min:0'
'wasted_quantity' => 'required|numeric|min:0'
'notes' => 'nullable|string|max:500'
// Custom validation: at least one quantity must be > 0
```

**UI Layout - Create Entry**:
```
┌─────────────────────────────────────────────────────────┐
│ Create Daily Entry                                      │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Date: [2024-01-15]                    [Create Entry]   │
│                                                         │
│  Add Items to Entry:                                    │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Select Item: [Bread ▼]                            │  │
│  │ Used Quantity: [____]                             │  │
│  │ Wasted Quantity: [____]                           │  │
│  │ Notes: [_________________________________]        │  │
│  │                                    [Add Item]     │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  Current Items:                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Item    │ Used │ Wasted │ Notes      │ Remove    │  │
│  ├───────────────────────────────────────────────────┤  │
│  │ Bread   │ 10   │ 2      │ Stale      │ [X]       │  │
│  │ Flour   │ 5    │ 0.5    │            │ [X]       │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```


### 5. Records Viewer Module

**Purpose**: View and sort historical daily entries

**Components**:
- `RecordController`: Handles record viewing and sorting
- Views: `records/index.blade.php`

**Key Methods**:
```php
// RecordController
public function index(Request $request): View
// Parameters: sort_by (date|name), sort_order (asc|desc), page
```

**Sorting Logic**:
- **Date Sort**: Order `daily_entries` by date field
- **Name Sort**: Join with `entry_items` and `items`, order by item name
- Store sort preferences in session
- Default: date descending (newest first)

**Pagination**:
- 50 entries per page
- Laravel's built-in pagination
- Show page numbers and next/previous links

**UI Layout**:
```
┌─────────────────────────────────────────────────────────┐
│ Historical Records                                      │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Sort by: [Date ▼]  Order: [Newest First ▼]            │
│                                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Date: 2024-01-15                                  │  │
│  │ ┌─────────────────────────────────────────────┐   │  │
│  │ │ Item      │ Used │ Wasted │ Notes          │   │  │
│  │ ├─────────────────────────────────────────────┤   │  │
│  │ │ Bread     │ 10   │ 2      │ Stale          │   │  │
│  │ │ Flour     │ 5    │ 0.5    │                │   │  │
│  │ └─────────────────────────────────────────────┘   │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Date: 2024-01-14                                  │  │
│  │ ┌─────────────────────────────────────────────┐   │  │
│  │ │ Item      │ Used │ Wasted │ Notes          │   │  │
│  │ ├─────────────────────────────────────────────┤   │  │
│  │ │ Croissant │ 15   │ 3      │                │   │  │
│  │ └─────────────────────────────────────────────┘   │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│  [< Previous]  Page 1 of 5  [Next >]                    │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### 6. Analytics Engine Module

**Purpose**: Calculate and display waste statistics and rankings

**Components**:
- `AnalyticsController`: Handles analytics requests
- `AnalyticsService`: Business logic for calculations
- Views: `analytics/index.blade.php`

**Key Methods**:
```php
// AnalyticsController
public function index(Request $request): View
// Parameters: time_period (daily|weekly|monthly), date_range

// AnalyticsService
public function calculateWasteRating(Item $item): float
public function getMostWastedItems(int $limit = 10): Collection
public function getMostUsedItems(int $limit = 10): Collection
public function getUsageComparison(Item $item, string $dateRange = null): array
public function getTimePeriodStatistics(string $period): Collection
```

**Calculation Formulas**:
```php
// Waste Rating
waste_rating = (total_wasted / (total_used + total_wasted)) × 100

// Handle edge case
if (total_used + total_wasted == 0) {
    waste_rating = 0
}

// Time Period Aggregation
daily: GROUP BY date
weekly: GROUP BY YEARWEEK(date)
monthly: GROUP BY YEAR(date), MONTH(date)
```

**UI Layout**:
```
┌─────────────────────────────────────────────────────────┐
│ Analytics                                               │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Time Period: [Daily ▼]  Date Range: [Last 30 Days ▼]  │
│                                                         │
│  Most Wasted Items:                                     │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Rank │ Item      │ Wasted │ Waste Rating        │  │
│  ├───────────────────────────────────────────────────┤  │
│  │ 1    │ Bread     │ 25.5   │ 15.30% ████░░░░░░  │  │
│  │ 2    │ Croissant │ 18.0   │ 12.00% ███░░░░░░░  │  │
│  │ 3    │ Flour     │ 10.5   │ 8.50%  ██░░░░░░░░  │  │
│  └───────────────────────────────────────────────────┘  │
│  [Show All Items]                                       │
│                                                         │
│  Most Used Items:                                       │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Rank │ Item      │ Used   │ Waste Rating        │  │
│  ├───────────────────────────────────────────────────┤  │
│  │ 1    │ Flour     │ 150.0  │ 8.50%  ██░░░░░░░░  │  │
│  │ 2    │ Bread     │ 140.5  │ 15.30% ████░░░░░░  │  │
│  │ 3    │ Croissant │ 132.0  │ 12.00% ███░░░░░░░  │  │
│  └───────────────────────────────────────────────────┘  │
│  [Show All Items]                                       │
│                                                         │
│  Usage vs Waste Comparison:                             │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Item      │ Used   │ Wasted │ Comparison         │  │
│  ├───────────────────────────────────────────────────┤  │
│  │ Bread     │ 140.5  │ 25.5   │ ████████████░░     │  │
│  │ Flour     │ 150.0  │ 10.5   │ ██████████████░    │  │
│  │ Croissant │ 132.0  │ 18.0   │ █████████████░░    │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```


## Data Models

### Entity Relationship Diagram

```
┌─────────────────┐
│     users       │
├─────────────────┤
│ id (PK)         │
│ name            │
│ email (unique)  │
│ password        │
│ created_at      │
│ updated_at      │
└────────┬────────┘
         │
         │ 1:N
         │
    ┌────┴────────────────────────────┐
    │                                 │
    ▼                                 ▼
┌─────────────────┐         ┌─────────────────┐
│     items       │         │ daily_entries   │
├─────────────────┤         ├─────────────────┤
│ id (PK)         │         │ id (PK)         │
│ user_id (FK)    │         │ user_id (FK)    │
│ name (unique*)  │         │ date (unique*)  │
│ category        │         │ created_at      │
│ price           │         │ updated_at      │
│ created_at      │         └────────┬────────┘
│ updated_at      │                  │
└────────┬────────┘                  │ 1:N
         │                           │
         │                           ▼
         │                  ┌─────────────────┐
         │                  │  entry_items    │
         │                  ├─────────────────┤
         │                  │ id (PK)         │
         │                  │ daily_entry_id  │
         │                  │ item_id (FK)    │
         │                  │ used_quantity   │
         │                  │ wasted_quantity │
         │                  │ notes           │
         │                  │ created_at      │
         │                  │ updated_at      │
         │                  └────────┬────────┘
         │                           │
         └───────────────────────────┘
                    N:1

* unique within user's scope
```

### Model Definitions

#### User Model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password'];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    
    // Relationships
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
    
    public function dailyEntries(): HasMany
    {
        return $this->hasMany(DailyEntry::class);
    }
}
```

#### Item Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Scopes\UserScope;

class Item extends Model
{
    protected $fillable = ['user_id', 'name', 'category', 'price'];
    
    protected $casts = [
        'price' => 'decimal:2',
    ];
    
    // Automatically filter by authenticated user
    protected static function booted()
    {
        static::addGlobalScope(new UserScope);
    }
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function entryItems(): HasMany
    {
        return $this->hasMany(EntryItem::class);
    }
    
    // Check if item can be deleted
    public function canBeDeleted(): bool
    {
        return $this->entryItems()->count() === 0;
    }
}
```

#### DailyEntry Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Scopes\UserScope;

class DailyEntry extends Model
{
    protected $fillable = ['user_id', 'date'];
    
    protected $casts = [
        'date' => 'date',
    ];
    
    // Automatically filter by authenticated user
    protected static function booted()
    {
        static::addGlobalScope(new UserScope);
    }
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function entryItems(): HasMany
    {
        return $this->hasMany(EntryItem::class);
    }
    
    // Calculate total waste rating for this entry
    public function getWasteRatingAttribute(): float
    {
        $totalUsed = $this->entryItems->sum('used_quantity');
        $totalWasted = $this->entryItems->sum('wasted_quantity');
        $total = $totalUsed + $totalWasted;
        
        return $total > 0 ? ($totalWasted / $total) * 100 : 0;
    }
}
```

#### EntryItem Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryItem extends Model
{
    protected $fillable = [
        'daily_entry_id',
        'item_id',
        'used_quantity',
        'wasted_quantity',
        'notes'
    ];
    
    protected $casts = [
        'used_quantity' => 'decimal:2',
        'wasted_quantity' => 'decimal:2',
    ];
    
    // Relationships
    public function dailyEntry(): BelongsTo
    {
        return $this->belongsTo(DailyEntry::class);
    }
    
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
    
    // Calculate waste rating for this specific entry item
    public function getWasteRatingAttribute(): float
    {
        $total = $this->used_quantity + $this->wasted_quantity;
        return $total > 0 ? ($this->wasted_quantity / $total) * 100 : 0;
    }
}
```

### Global Scope for User Isolation

```php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class UserScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (auth()->check()) {
            $builder->where('user_id', auth()->id());
        }
    }
}
```

This global scope automatically adds `WHERE user_id = ?` to all queries for models that use it, ensuring complete data isolation between users.


### Database Schema

#### users table

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_email (email)
);
```

#### items table

```sql
CREATE TABLE items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NULL DEFAULT 0.00,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_item (user_id, name),
    INDEX idx_user_id (user_id),
    INDEX idx_category (category)
);
```

**Notes**:
- `unique_user_item` ensures item names are unique per user
- `user_id` foreign key with CASCADE delete removes all items when user is deleted
- Indexes on `user_id` and `category` for efficient filtering

#### daily_entries table

```sql
CREATE TABLE daily_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date),
    INDEX idx_user_id (user_id),
    INDEX idx_date (date)
);
```

**Notes**:
- `unique_user_date` ensures one entry per date per user
- `user_id` foreign key with CASCADE delete removes all entries when user is deleted
- Index on `date` for efficient date-based sorting

#### entry_items table

```sql
CREATE TABLE entry_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    daily_entry_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    used_quantity DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    wasted_quantity DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (daily_entry_id) REFERENCES daily_entries(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT,
    INDEX idx_daily_entry_id (daily_entry_id),
    INDEX idx_item_id (item_id),
    CONSTRAINT chk_quantities CHECK (used_quantity >= 0 AND wasted_quantity >= 0),
    CONSTRAINT chk_at_least_one CHECK (used_quantity > 0 OR wasted_quantity > 0)
);
```

**Notes**:
- `daily_entry_id` foreign key with CASCADE delete removes entry items when daily entry is deleted
- `item_id` foreign key with RESTRICT prevents item deletion if referenced
- Check constraints ensure valid quantities
- `notes` field limited to 500 characters at application level

### Migration Files

#### 2024_01_01_000000_create_users_table.php

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

#### 2024_01_01_000001_create_items_table.php

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('category', 100);
            $table->decimal('price', 10, 2)->nullable()->default(0.00);
            $table->timestamps();
            
            $table->unique(['user_id', 'name'], 'unique_user_item');
            $table->index('user_id');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
```

#### 2024_01_01_000002_create_daily_entries_table.php

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->timestamps();
            
            $table->unique(['user_id', 'date'], 'unique_user_date');
            $table->index('user_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_entries');
    }
};
```

#### 2024_01_01_000003_create_entry_items_table.php

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('restrict');
            $table->decimal('used_quantity', 10, 2)->default(0.00);
            $table->decimal('wasted_quantity', 10, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('daily_entry_id');
            $table->index('item_id');
        });
        
        // Add check constraints via raw SQL (Laravel doesn't support CHECK in Blueprint)
        DB::statement('ALTER TABLE entry_items ADD CONSTRAINT chk_quantities CHECK (used_quantity >= 0 AND wasted_quantity >= 0)');
        DB::statement('ALTER TABLE entry_items ADD CONSTRAINT chk_at_least_one CHECK (used_quantity > 0 OR wasted_quantity > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_items');
    }
};
```


## Correctness Properties

A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.

### Property 1: Authentication Round-Trip

For any valid user credentials, authenticating with those credentials should result in an authenticated session, and logging out should return to an unauthenticated state.

**Validates: Requirements 1.2, 1.5**

### Property 2: Authentication Rejection

For any invalid credentials (wrong password, non-existent user, malformed input), authentication attempts should be rejected with an error message and no session should be created.

**Validates: Requirements 1.3**

### Property 3: Session Persistence

For any authenticated user, navigating between any pages in the application should maintain the authenticated session state until logout is explicitly called.

**Validates: Requirements 1.4**

### Property 4: User Data Isolation

For any two different authenticated users, querying for items, daily entries, or entry items should return only data belonging to the authenticated user, with no data leakage between users.

**Validates: Requirements (implicit multi-user requirement)**

### Property 5: Item CRUD Round-Trip

For any valid item data (name, category, optional price), creating an item should result in that item being retrievable with the same data, updating an item should result in the updated data being retrievable, and deleting an unreferenced item should result in that item no longer being retrievable.

**Validates: Requirements 3.2, 4.3, 5.3**

### Property 6: Item Name Uniqueness

For any user, attempting to create or update an item with a name that already exists for that user should be rejected with an error message.

**Validates: Requirements 3.3, 4.5, 22.3**

### Property 7: Required Field Validation

For any item creation or update attempt, if the name or category fields are missing or empty, the operation should be rejected with a validation error.

**Validates: Requirements 3.4**

### Property 8: Item Deletion Protection

For any item that is referenced in one or more entry items, attempting to delete that item should be prevented with an informative error message, and the item should remain in the database.

**Validates: Requirements 5.5, 25.2**

### Property 9: Deletion Cancellation

For any item, canceling a deletion operation should leave the item completely unchanged in the database.

**Validates: Requirements 5.4**

### Property 10: Category Filtering

For any category value and any set of items, filtering items by that category should return only items with that exact category value, and all items with that category should be included.

**Validates: Requirements 6.5**

### Property 11: Item Display Completeness

For any item, displaying that item should include its name, category, and price (if set).

**Validates: Requirements 6.4**

### Property 12: Daily Entry CRUD Round-Trip

For any valid date, creating a daily entry should result in that entry being retrievable with the same date, and deleting a daily entry should result in that entry no longer being retrievable.

**Validates: Requirements 7.2**

### Property 13: Daily Entry Date Uniqueness

For any user, attempting to create a daily entry with a date that already has an entry for that user should be rejected with an error message.

**Validates: Requirements 7.5, 23.3**

### Property 14: Unique ID Assignment

For any created item, daily entry, or entry item, the system should assign a unique auto-incrementing ID that differs from all other IDs in that table.

**Validates: Requirements 7.3, 22.2, 23.2, 24.2**

### Property 15: Entry Item CRUD Round-Trip

For any valid entry item data (item reference, used quantity, wasted quantity, optional notes), adding an entry item to a daily entry should result in that entry item being retrievable with the same data and correct references to both the daily entry and the item.

**Validates: Requirements 8.4, 9.3, 10.3**

### Property 16: Multiple Items Per Entry

For any daily entry, adding multiple entry items should result in all entry items being associated with that daily entry and all being retrievable.

**Validates: Requirements 8.5**

### Property 17: Quantity Validation

For any entry item, both used_quantity and wasted_quantity must be non-negative numeric values, at least one must be greater than zero, and non-numeric values should be rejected with a validation error.

**Validates: Requirements 9.2, 9.4, 9.5**

### Property 18: Entry Item Display Completeness

For any entry item, displaying that entry item should include the item name, used quantity, wasted quantity, and notes (if present).

**Validates: Requirements 11.4**

### Property 19: Records Pagination

For any user with more than 50 daily entries, the records viewer should paginate the results, displaying at most 50 entries per page.

**Validates: Requirements 11.5**

### Property 20: Date Sorting Invariant

For any set of daily entries, sorting by date should order them chronologically, with the sort order (ascending or descending) determining whether oldest or newest appears first, and toggling the sort order should reverse the sequence.

**Validates: Requirements 12.2, 12.3**

### Property 21: Name Sorting Invariant

For any set of entry items, sorting by item name should order them alphabetically, with the sort order (ascending or descending) determining the direction, and toggling the sort order should reverse the sequence.

**Validates: Requirements 13.2, 13.3**

### Property 22: Name Grouping

For any set of entry items with the same item name, when sorted by name, all entry items with the same name should appear consecutively in the list.

**Validates: Requirements 13.4**

### Property 23: Sort Persistence

For any sort selection (date or name, ascending or descending), that selection should persist across page navigations within the same user session.

**Validates: Requirements 12.4, 13.5**

### Property 24: Waste Rating Calculation

For any entry item or item aggregate with used_quantity and wasted_quantity, the waste rating should equal (wasted_quantity / (used_quantity + wasted_quantity)) × 100, formatted to two decimal places, and should equal zero when both quantities are zero.

**Validates: Requirements 14.1, 14.3, 14.4**

### Property 25: Aggregate Waste Rating

For any item with multiple entry items across different daily entries, the aggregate waste rating should be calculated using the sum of all used quantities and the sum of all wasted quantities for that item.

**Validates: Requirements 14.2**

### Property 26: Waste Rating Updates

For any item, adding a new entry item for that item should result in the item's aggregate waste rating being recalculated to include the new entry item's quantities.

**Validates: Requirements 14.5, 16.5, 17.5**

### Property 27: Quantity Aggregation

For any item, the total used quantity should equal the sum of used_quantity across all entry items for that item, and the total wasted quantity should equal the sum of wasted_quantity across all entry items for that item.

**Validates: Requirements 15.1, 15.2**

### Property 28: Date Range Filtering

For any date range filter applied to analytics, only entry items from daily entries within that date range (inclusive) should be included in the calculations.

**Validates: Requirements 15.5**

### Property 29: Waste Ranking Invariant

For any set of items, ranking by total wasted quantity should order them with the highest wasted quantity first, and each item should display both its absolute wasted quantity and its waste rating percentage.

**Validates: Requirements 16.1, 16.4**

### Property 30: Usage Ranking Invariant

For any set of items, ranking by total used quantity should order them with the highest used quantity first, and each item should display both its absolute used quantity and its waste rating percentage.

**Validates: Requirements 17.1, 17.4**

### Property 31: Time Period Aggregation

For any item and time period selection (daily, weekly, monthly), the displayed statistics should aggregate entry items according to the selected period: daily groups by exact date, weekly groups by 7-day periods, and monthly groups by calendar month.

**Validates: Requirements 18.1, 18.2, 18.3, 18.5**

### Property 32: Navigation Functionality

For any navigation tab in the sidebar, clicking that tab should navigate to the corresponding module page, and the clicked tab should be visually highlighted as active.

**Validates: Requirements 20.3, 20.4**

### Property 33: Sidebar Persistence

For any page in the application, the sidebar navigation should be visible and accessible, containing links to all major modules.

**Validates: Requirements 20.5**

### Property 34: Dropdown Interaction

For any dropdown menu, clicking the dropdown should expand it to show options, selecting an option should collapse the dropdown and apply the selection, and clicking outside the dropdown should close it without applying a selection.

**Validates: Requirements 21.3, 21.4, 21.5**

### Property 35: Decimal Precision

For any price, used_quantity, or wasted_quantity value, the stored value should maintain exactly two decimal places of precision.

**Validates: Requirements 22.4, 24.5**

### Property 36: Timestamp Tracking

For any item, daily entry, or entry item, the database record should include created_at and updated_at timestamps, with created_at set on creation and updated_at set on both creation and any subsequent updates.

**Validates: Requirements 22.5, 23.5, 24.7**

### Property 37: Cascade Deletion

For any daily entry with associated entry items, deleting the daily entry should automatically delete all associated entry items.

**Validates: Requirements 25.1**

### Property 38: Foreign Key Integrity

For any entry item, it must reference a valid daily_entry_id that exists in the daily_entries table and a valid item_id that exists in the items table, and attempting to create an entry item with invalid references should be rejected.

**Validates: Requirements 25.3, 25.4**


## Error Handling

### Error Handling Strategy

The EcoManager application follows Laravel's exception handling patterns with user-friendly error messages and appropriate HTTP status codes.

### Error Categories

#### 1. Validation Errors (HTTP 422)

**Scenarios**:
- Missing required fields (item name, category, date)
- Invalid data types (non-numeric quantities, invalid dates)
- Constraint violations (duplicate item names, duplicate entry dates)
- Business rule violations (at least one quantity must be > 0)

**Handling**:
```php
// Form Request validation automatically returns 422 with error messages
// Example: StoreItemRequest
public function rules(): array
{
    return [
        'name' => 'required|string|max:255|unique:items,name,NULL,id,user_id,' . auth()->id(),
        'category' => 'required|string|max:100',
        'price' => 'nullable|numeric|min:0',
    ];
}

public function messages(): array
{
    return [
        'name.required' => 'Item name is required.',
        'name.unique' => 'An item with this name already exists.',
        'category.required' => 'Category is required.',
        'price.numeric' => 'Price must be a valid number.',
    ];
}
```

**User Experience**:
- Display validation errors inline next to form fields
- Use red text and border highlighting for error fields
- Preserve user input so they don't have to re-enter everything
- Show a summary message at the top of the form

#### 2. Authentication Errors (HTTP 401/403)

**Scenarios**:
- Invalid login credentials
- Unauthenticated access to protected routes
- Session expiration

**Handling**:
```php
// AuthController
public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('dashboard');
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
}

// Middleware redirects unauthenticated users to login
```

**User Experience**:
- Show clear error message on login page
- Redirect to login page when session expires
- Preserve intended destination for post-login redirect

#### 3. Authorization Errors (HTTP 403)

**Scenarios**:
- User attempting to access another user's data
- User attempting unauthorized operations

**Handling**:
```php
// Policy-based authorization
public function update(User $user, Item $item): bool
{
    return $user->id === $item->user_id;
}

// In controller
$this->authorize('update', $item);
```

**User Experience**:
- Show "Access Denied" message
- Redirect to dashboard or previous page
- Log security violations for monitoring

#### 4. Database Errors (HTTP 500)

**Scenarios**:
- Foreign key constraint violations
- Database connection failures
- Transaction failures

**Handling**:
```php
// ItemController
public function destroy(Item $item): RedirectResponse
{
    try {
        if (!$item->canBeDeleted()) {
            return back()->with('error', 
                'Cannot delete this item because it is used in existing entries.');
        }
        
        $item->delete();
        return redirect()->route('items.index')
            ->with('success', 'Item deleted successfully.');
            
    } catch (\Exception $e) {
        Log::error('Item deletion failed: ' . $e->getMessage());
        return back()->with('error', 
            'An error occurred while deleting the item. Please try again.');
    }
}
```

**User Experience**:
- Show generic error message to user
- Log detailed error for debugging
- Provide option to retry or contact support

#### 5. Not Found Errors (HTTP 404)

**Scenarios**:
- Accessing non-existent resources
- Accessing resources belonging to other users (due to global scope)

**Handling**:
```php
// Laravel route model binding with global scope automatically returns 404
Route::get('/items/{item}', [ItemController::class, 'edit']);
// If item doesn't exist or belongs to another user, returns 404
```

**User Experience**:
- Show "Resource Not Found" page
- Provide link back to relevant list page
- Suggest checking the URL or using navigation

### Error Logging

```php
// Log levels based on severity
Log::error('Critical database error', ['exception' => $e]);
Log::warning('User attempted unauthorized access', ['user_id' => $userId]);
Log::info('Validation failed', ['errors' => $validator->errors()]);
```

### User-Facing Error Messages

**Principles**:
- Clear and actionable
- Non-technical language
- Suggest next steps
- Maintain professional tone

**Examples**:
- ✅ "An item with this name already exists. Please choose a different name."
- ❌ "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry"

- ✅ "This item cannot be deleted because it's used in existing entries."
- ❌ "Foreign key constraint violation on entry_items.item_id"

- ✅ "Please enter a valid number for the quantity."
- ❌ "Invalid input type: expected numeric, got string"


## Testing Strategy

### Overview

The EcoManager testing strategy employs a dual approach combining unit tests for specific scenarios and property-based tests for universal correctness guarantees. This comprehensive approach ensures both concrete functionality and general correctness across all possible inputs.

### Testing Framework

- **Unit Testing**: PHPUnit (Laravel's default testing framework)
- **Property-Based Testing**: PHPUnit with custom property test helpers or Eris (PHP property-based testing library)
- **Database Testing**: Laravel's database testing features with in-memory SQLite for speed

### Test Organization

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── ItemTest.php
│   │   ├── DailyEntryTest.php
│   │   └── EntryItemTest.php
│   ├── Services/
│   │   └── AnalyticsServiceTest.php
│   └── Scopes/
│       └── UserScopeTest.php
├── Feature/
│   ├── Auth/
│   │   └── AuthenticationTest.php
│   ├── Items/
│   │   ├── ItemCrudTest.php
│   │   └── ItemValidationTest.php
│   ├── Entries/
│   │   ├── DailyEntryTest.php
│   │   └── EntryItemTest.php
│   ├── Records/
│   │   └── RecordsViewerTest.php
│   └── Analytics/
│       └── AnalyticsTest.php
└── Property/
    ├── ItemPropertiesTest.php
    ├── EntryPropertiesTest.php
    ├── AnalyticsPropertiesTest.php
    └── AuthPropertiesTest.php
```

### Unit Testing Approach

Unit tests focus on specific examples, edge cases, and integration points between components.

**Key Areas**:
- Specific validation scenarios
- Edge cases (empty values, boundary conditions)
- Error conditions and exception handling
- Model relationships and methods
- Service class calculations
- Controller responses

**Example Unit Tests**:

```php
// tests/Unit/Models/ItemTest.php
class ItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_can_be_deleted_when_not_referenced(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->for($user)->create();
        
        $this->assertTrue($item->canBeDeleted());
    }
    
    public function test_item_cannot_be_deleted_when_referenced(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->for($user)->create();
        $entry = DailyEntry::factory()->for($user)->create();
        EntryItem::factory()->for($entry)->for($item)->create();
        
        $this->assertFalse($item->canBeDeleted());
    }
    
    public function test_price_defaults_to_zero_when_null(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->for($user)->create(['price' => null]);
        
        $this->assertEquals(0.00, $item->price);
    }
}
```

```php
// tests/Unit/Services/AnalyticsServiceTest.php
class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_waste_rating_calculation_with_normal_values(): void
    {
        $service = new AnalyticsService();
        $item = Item::factory()->create();
        
        // Create entry items: used=80, wasted=20
        // Expected waste rating: (20 / 100) * 100 = 20.00%
        
        $rating = $service->calculateWasteRating($item);
        
        $this->assertEquals(20.00, $rating);
    }
    
    public function test_waste_rating_returns_zero_for_zero_quantities(): void
    {
        $service = new AnalyticsService();
        $item = Item::factory()->create();
        
        // No entry items, so totals are zero
        $rating = $service->calculateWasteRating($item);
        
        $this->assertEquals(0.00, $rating);
    }
}
```

### Property-Based Testing Approach

Property tests verify universal properties across many randomly generated inputs (minimum 100 iterations per test). Each property test references its corresponding design document property.

**Configuration**:
- Minimum 100 iterations per property test
- Use Eris library for property-based testing in PHP
- Tag each test with feature name and property number

**Example Property Tests**:

```php
// tests/Property/ItemPropertiesTest.php
use Eris\Generator;
use Eris\TestTrait;

class ItemPropertiesTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Feature: eco-manager-food-waste-system, Property 5: Item CRUD Round-Trip
     * 
     * For any valid item data (name, category, optional price), creating an item 
     * should result in that item being retrievable with the same data.
     */
    public function test_item_creation_round_trip(): void
    {
        $this->forAll(
            Generator\string()->withMaxSize(255),
            Generator\string()->withMaxSize(100),
            Generator\oneOf(
                Generator\constant(null),
                Generator\float()->between(0, 9999.99)
            )
        )
        ->withMaxSize(100)
        ->then(function ($name, $category, $price) {
            $user = User::factory()->create();
            $this->actingAs($user);
            
            $item = Item::create([
                'user_id' => $user->id,
                'name' => $name,
                'category' => $category,
                'price' => $price,
            ]);
            
            $retrieved = Item::find($item->id);
            
            $this->assertEquals($name, $retrieved->name);
            $this->assertEquals($category, $retrieved->category);
            $this->assertEquals($price ?? 0.00, $retrieved->price);
        });
    }

    /**
     * Feature: eco-manager-food-waste-system, Property 6: Item Name Uniqueness
     * 
     * For any user, attempting to create an item with a name that already exists 
     * for that user should be rejected with an error message.
     */
    public function test_item_name_uniqueness_within_user(): void
    {
        $this->forAll(
            Generator\string()->withMaxSize(255),
            Generator\string()->withMaxSize(100)
        )
        ->withMaxSize(100)
        ->then(function ($name, $category) {
            $user = User::factory()->create();
            $this->actingAs($user);
            
            // Create first item
            Item::create([
                'user_id' => $user->id,
                'name' => $name,
                'category' => $category,
            ]);
            
            // Attempt to create duplicate
            $this->expectException(\Illuminate\Database\QueryException::class);
            
            Item::create([
                'user_id' => $user->id,
                'name' => $name,
                'category' => $category,
            ]);
        });
    }

    /**
     * Feature: eco-manager-food-waste-system, Property 4: User Data Isolation
     * 
     * For any two different authenticated users, querying for items should return 
     * only data belonging to the authenticated user.
     */
    public function test_user_data_isolation_for_items(): void
    {
        $this->forAll(
            Generator\string()->withMaxSize(255),
            Generator\string()->withMaxSize(100)
        )
        ->withMaxSize(100)
        ->then(function ($name, $category) {
            $user1 = User::factory()->create();
            $user2 = User::factory()->create();
            
            // User 1 creates an item
            $this->actingAs($user1);
            $item1 = Item::create([
                'user_id' => $user1->id,
                'name' => $name,
                'category' => $category,
            ]);
            
            // User 2 should not see user 1's item
            $this->actingAs($user2);
            $user2Items = Item::all();
            
            $this->assertCount(0, $user2Items);
            $this->assertFalse($user2Items->contains($item1));
        });
    }
}
```

```php
// tests/Property/AnalyticsPropertiesTest.php
class AnalyticsPropertiesTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    /**
     * Feature: eco-manager-food-waste-system, Property 24: Waste Rating Calculation
     * 
     * For any entry item with used_quantity and wasted_quantity, the waste rating 
     * should equal (wasted_quantity / (used_quantity + wasted_quantity)) × 100.
     */
    public function test_waste_rating_calculation_formula(): void
    {
        $this->forAll(
            Generator\float()->between(0, 1000),
            Generator\float()->between(0, 1000)
        )
        ->when(function ($used, $wasted) {
            return $used > 0 || $wasted > 0; // At least one must be positive
        })
        ->withMaxSize(100)
        ->then(function ($used, $wasted) {
            $user = User::factory()->create();
            $item = Item::factory()->for($user)->create();
            $entry = DailyEntry::factory()->for($user)->create();
            
            $entryItem = EntryItem::create([
                'daily_entry_id' => $entry->id,
                'item_id' => $item->id,
                'used_quantity' => $used,
                'wasted_quantity' => $wasted,
            ]);
            
            $expectedRating = ($wasted / ($used + $wasted)) * 100;
            $actualRating = $entryItem->waste_rating;
            
            $this->assertEqualsWithDelta($expectedRating, $actualRating, 0.01);
        });
    }

    /**
     * Feature: eco-manager-food-waste-system, Property 27: Quantity Aggregation
     * 
     * For any item, the total used quantity should equal the sum of used_quantity 
     * across all entry items for that item.
     */
    public function test_quantity_aggregation_across_entries(): void
    {
        $this->forAll(
            Generator\seq(Generator\tuple(
                Generator\float()->between(0, 100),
                Generator\float()->between(0, 100)
            ))->withMaxSize(10)
        )
        ->withMaxSize(100)
        ->then(function ($quantities) {
            $user = User::factory()->create();
            $item = Item::factory()->for($user)->create();
            
            $expectedUsed = 0;
            $expectedWasted = 0;
            
            foreach ($quantities as [$used, $wasted]) {
                if ($used > 0 || $wasted > 0) {
                    $entry = DailyEntry::factory()->for($user)->create();
                    EntryItem::create([
                        'daily_entry_id' => $entry->id,
                        'item_id' => $item->id,
                        'used_quantity' => $used,
                        'wasted_quantity' => $wasted,
                    ]);
                    
                    $expectedUsed += $used;
                    $expectedWasted += $wasted;
                }
            }
            
            $actualUsed = $item->entryItems()->sum('used_quantity');
            $actualWasted = $item->entryItems()->sum('wasted_quantity');
            
            $this->assertEqualsWithDelta($expectedUsed, $actualUsed, 0.01);
            $this->assertEqualsWithDelta($expectedWasted, $actualWasted, 0.01);
        });
    }
}
```

### Feature Testing

Feature tests verify end-to-end functionality through HTTP requests, testing the full stack from routes to database.

```php
// tests/Feature/Items/ItemCrudTest.php
class ItemCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_item_through_web_interface(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post('/items', [
            'name' => 'Bread',
            'category' => 'Product',
            'price' => 2.50,
        ]);
        
        $response->assertRedirect('/items');
        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'Bread',
            'category' => 'Product',
            'price' => 2.50,
        ]);
    }
    
    public function test_user_cannot_create_duplicate_item_name(): void
    {
        $user = User::factory()->create();
        Item::factory()->for($user)->create(['name' => 'Bread']);
        
        $response = $this->actingAs($user)->post('/items', [
            'name' => 'Bread',
            'category' => 'Product',
        ]);
        
        $response->assertSessionHasErrors('name');
    }
}
```

### Test Coverage Goals

- **Unit Tests**: 80%+ code coverage
- **Feature Tests**: All user-facing workflows covered
- **Property Tests**: All 38 correctness properties implemented
- **Integration Tests**: All module interactions tested

### Continuous Integration

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install Dependencies
        run: composer install
      - name: Run Unit Tests
        run: php artisan test --testsuite=Unit
      - name: Run Feature Tests
        run: php artisan test --testsuite=Feature
      - name: Run Property Tests
        run: php artisan test --testsuite=Property
```

### Testing Best Practices

1. **Isolation**: Each test should be independent and not rely on other tests
2. **Database**: Use `RefreshDatabase` trait to reset database between tests
3. **Factories**: Use Laravel factories for generating test data
4. **Assertions**: Use specific assertions (assertEquals, assertDatabaseHas, etc.)
5. **Naming**: Use descriptive test names that explain what is being tested
6. **Coverage**: Aim for high coverage but focus on meaningful tests
7. **Speed**: Keep tests fast by using in-memory SQLite for unit tests
8. **Documentation**: Tag property tests with their corresponding design properties


## API and Routing Structure

### Route Organization

All routes are defined in `routes/web.php` following RESTful conventions.

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\DailyEntryController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Items (RESTful resource)
    Route::resource('items', ItemController::class);
    // Generates:
    // GET    /items              -> index   (list all items)
    // GET    /items/create       -> create  (show create form)
    // POST   /items              -> store   (save new item)
    // GET    /items/{item}       -> show    (show single item)
    // GET    /items/{item}/edit  -> edit    (show edit form)
    // PUT    /items/{item}       -> update  (update item)
    // DELETE /items/{item}       -> destroy (delete item)
    
    // Daily Entries
    Route::resource('entries', DailyEntryController::class)->except(['update']);
    // GET    /entries                -> index   (list all entries)
    // GET    /entries/create         -> create  (show create form)
    // POST   /entries                -> store   (save new entry)
    // GET    /entries/{entry}        -> show    (show entry details)
    // DELETE /entries/{entry}        -> destroy (delete entry)
    
    // Entry Items (nested under entries)
    Route::post('/entries/{entry}/items', [DailyEntryController::class, 'addItem'])
        ->name('entries.items.store');
    Route::delete('/entry-items/{entryItem}', [DailyEntryController::class, 'removeItem'])
        ->name('entry-items.destroy');
    
    // Records Viewer
    Route::get('/records', [RecordController::class, 'index'])->name('records.index');
    
    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
});
```

### Route Naming Conventions

All routes use named routes for easy reference in views and controllers:

```php
// In views
<a href="{{ route('items.index') }}">View Items</a>
<form action="{{ route('items.store') }}" method="POST">

// In controllers
return redirect()->route('dashboard');
return redirect()->route('items.index')->with('success', 'Item created!');
```

### Middleware Stack

```php
// Global middleware (applied to all routes)
- EncryptCookies
- AddQueuedCookiesToResponse
- StartSession
- ShareErrorsFromSession
- VerifyCsrfToken
- SubstituteBindings

// Route-specific middleware
- 'auth' => Authenticate::class  // Redirects to login if not authenticated
- 'guest' => RedirectIfAuthenticated::class  // Redirects to dashboard if authenticated
```

### Request/Response Flow

```
User Request
    ↓
Route Matching (web.php)
    ↓
Middleware Pipeline (auth, csrf, etc.)
    ↓
Controller Method
    ↓
Form Request Validation (if applicable)
    ↓
Business Logic / Service Layer
    ↓
Model / Database Query (with UserScope)
    ↓
View Rendering (Blade)
    ↓
Response to User
```

### API Response Patterns

**Successful Operations**:
```php
// Create/Update
return redirect()->route('items.index')
    ->with('success', 'Item created successfully.');

// Delete
return redirect()->route('items.index')
    ->with('success', 'Item deleted successfully.');
```

**Failed Operations**:
```php
// Validation errors (automatic from Form Requests)
return back()->withErrors($validator)->withInput();

// Business logic errors
return back()->with('error', 'Cannot delete item that is in use.');

// Not found (automatic from route model binding)
abort(404);

// Unauthorized (automatic from policies)
abort(403);
```

### Route Model Binding

Laravel automatically resolves model instances from route parameters:

```php
// Route definition
Route::get('/items/{item}', [ItemController::class, 'edit']);

// Controller method - $item is automatically resolved
public function edit(Item $item): View
{
    // $item is already loaded from database
    // Global scope ensures it belongs to authenticated user
    return view('items.edit', compact('item'));
}
```

If the item doesn't exist or belongs to another user (filtered by UserScope), Laravel automatically returns a 404 response.


## Authentication Flow

### Authentication Architecture

EcoManager uses Laravel's built-in authentication system with session-based authentication.

### Authentication Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    User Authentication Flow                  │
└─────────────────────────────────────────────────────────────┘

1. User visits application
   │
   ├─→ Not authenticated
   │   │
   │   └─→ Redirect to /login
   │       │
   │       └─→ Display login form
   │           │
   │           ├─→ User enters credentials
   │           │   │
   │           │   └─→ POST /login
   │           │       │
   │           │       ├─→ Valid credentials
   │           │       │   │
   │           │       │   ├─→ Create session
   │           │       │   ├─→ Regenerate session ID (security)
   │           │       │   └─→ Redirect to /dashboard
   │           │       │
   │           │       └─→ Invalid credentials
   │           │           │
   │           │           └─→ Return to /login with error
   │           │
   │           └─→ User clicks "Logout"
   │               │
   │               └─→ POST /logout
   │                   │
   │                   ├─→ Destroy session
   │                   └─→ Redirect to /login
   │
   └─→ Already authenticated
       │
       └─→ Access protected routes
           │
           ├─→ Session valid
           │   │
           │   └─→ Allow access
           │
           └─→ Session expired/invalid
               │
               └─→ Redirect to /login
```

### Session Management

**Session Configuration** (`config/session.php`):
```php
return [
    'driver' => env('SESSION_DRIVER', 'database'),
    'lifetime' => 120, // 2 hours
    'expire_on_close' => false,
    'encrypt' => true,
    'secure' => env('SESSION_SECURE_COOKIE', false),
    'http_only' => true,
    'same_site' => 'lax',
];
```

**Session Storage**:
- Sessions stored in database for persistence across server restarts
- Session table includes: id, user_id, ip_address, user_agent, payload, last_activity

**Session Security**:
- Session ID regenerated on login (prevents session fixation)
- HTTP-only cookies (prevents XSS attacks)
- Encrypted session data
- CSRF token validation on all POST/PUT/DELETE requests

### Authentication Implementation

#### AuthController

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Display the login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }
    
    /**
     * Handle login attempt
     */
    public function login(Request $request): RedirectResponse
    {
        // Validate input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        
        // Attempt authentication
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Regenerate session ID for security
            $request->session()->regenerate();
            
            // Redirect to intended page or dashboard
            return redirect()->intended('dashboard');
        }
        
        // Authentication failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
    
    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        
        // Invalidate session
        $request->session()->invalidate();
        
        // Regenerate CSRF token
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }
}
```

#### Authenticate Middleware

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            // Store intended URL for post-login redirect
            return redirect()->guest(route('login'));
        }
        
        return $next($request);
    }
}
```

### User Model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;
    
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Automatically hash passwords
    ];
}
```

### Login View

```blade
{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="login-container">
    <div class="login-card">
        <h1>EcoManager</h1>
        <p>Food and Waste Management System</p>
        
        @if ($errors->any())
            <div class="alert alert-error">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                >
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">
                Login
            </button>
        </form>
    </div>
</div>
@endsection
```

### Password Security

**Hashing**:
- Passwords hashed using bcrypt (Laravel default)
- Cost factor: 10 (configurable in `config/hashing.php`)
- Automatic hashing via `password` cast in User model

**Password Requirements** (can be enforced via validation):
```php
'password' => 'required|string|min:8|confirmed',
```

### Remember Me Functionality

When "Remember me" is checked:
- Laravel creates a long-lived cookie (default: 5 years)
- Cookie contains encrypted remember token
- User stays logged in across browser sessions
- Token stored in `remember_token` column in users table

### Security Considerations

1. **CSRF Protection**: All forms include `@csrf` directive
2. **Session Fixation**: Session ID regenerated on login
3. **XSS Protection**: Blade automatically escapes output
4. **SQL Injection**: Eloquent ORM uses parameterized queries
5. **Password Hashing**: Bcrypt with appropriate cost factor
6. **Rate Limiting**: Can be added to login route to prevent brute force
7. **HTTPS**: Should be enforced in production (SESSION_SECURE_COOKIE=true)

### Multi-User Isolation in Authentication Context

Once authenticated, the user's ID is available via `auth()->id()` and is automatically used by:

1. **Global Scopes**: Filter all queries to user's data
2. **Controllers**: Set `user_id` when creating records
3. **Policies**: Verify ownership before allowing operations
4. **Views**: Display only user's data

Example in controller:
```php
public function store(StoreItemRequest $request): RedirectResponse
{
    $item = Item::create([
        'user_id' => auth()->id(), // Automatically set from session
        'name' => $request->name,
        'category' => $request->category,
        'price' => $request->price,
    ]);
    
    return redirect()->route('items.index')
        ->with('success', 'Item created successfully.');
}
```


## UI/UX Design

### Design Principles

1. **Minimalist**: Clean, uncluttered interfaces focused on functionality
2. **Efficient**: Quick data entry for busy shop owners
3. **Organized**: Well-structured layouts with clear information hierarchy
4. **Professional**: Business-appropriate appearance
5. **Accessible**: Sufficient contrast, readable fonts, keyboard navigation

### Color Scheme

**Primary Colors**:
- White (#FFFFFF): Main background
- Green (#2D7A3E): Primary accent, buttons, active states
- Light Green (#E8F5E9): Hover states, subtle backgrounds
- Dark Green (#1B5E20): Text on green backgrounds, borders

**Secondary Colors**:
- Gray (#757575): Secondary text, borders
- Light Gray (#F5F5F5): Alternate row backgrounds, disabled states
- Red (#D32F2F): Error messages, delete actions
- Blue (#1976D2): Information, links

### Typography

**Font Family**: 
- Primary: 'Inter', 'Segoe UI', system-ui, sans-serif
- Monospace (for numbers): 'Roboto Mono', monospace

**Font Sizes**:
- Heading 1: 28px (Page titles)
- Heading 2: 22px (Section headers)
- Heading 3: 18px (Subsection headers)
- Body: 16px (Main content)
- Small: 14px (Labels, secondary info)
- Tiny: 12px (Timestamps, footnotes)

**Font Weights**:
- Regular: 400 (Body text)
- Medium: 500 (Labels, emphasis)
- Semibold: 600 (Headings, buttons)

### Layout Structure

```
┌─────────────────────────────────────────────────────────────┐
│ Header (fixed)                                              │
│ ┌─────────────────────────────────────────────────────────┐ │
│ │ EcoManager                              [User] [Logout] │ │
│ └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ ┌──────────────────────────────┐  ┌───────────────────┐   │
│ │                              │  │   Sidebar (fixed) │   │
│ │                              │  │                   │   │
│ │                              │  │ ☰ Dashboard       │   │
│ │                              │  │ ☰ Items           │   │
│ │      Main Content Area       │  │ ☰ Daily Entry     │   │
│ │                              │  │ ☰ Records         │   │
│ │                              │  │ ☰ Analytics       │   │
│ │                              │  │                   │   │
│ │                              │  └───────────────────┘   │
│ └──────────────────────────────┘                          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Dimensions**:
- Header height: 60px
- Sidebar width: 200px
- Main content: Fluid (with max-width: 1200px, centered)
- Padding: 20px standard, 40px for main content

### Component Designs

#### Buttons

```css
/* Primary Button */
.btn-primary {
    background: #2D7A3E;
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    border: none;
    font-weight: 600;
    cursor: pointer;
}

.btn-primary:hover {
    background: #1B5E20;
}

/* Secondary Button */
.btn-secondary {
    background: white;
    color: #2D7A3E;
    border: 2px solid #2D7A3E;
}

/* Danger Button (Delete) */
.btn-danger {
    background: #D32F2F;
    color: white;
}
```

#### Form Elements

```css
/* Input Fields */
input[type="text"],
input[type="email"],
input[type="number"],
input[type="date"],
select,
textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #CCCCCC;
    border-radius: 4px;
    font-size: 16px;
}

input:focus,
select:focus,
textarea:focus {
    outline: none;
    border-color: #2D7A3E;
    box-shadow: 0 0 0 3px rgba(45, 122, 62, 0.1);
}

/* Labels */
label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333333;
}

/* Error State */
.error input {
    border-color: #D32F2F;
}

.error-message {
    color: #D32F2F;
    font-size: 14px;
    margin-top: 5px;
}
```

#### Tables

```css
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

thead {
    background: #F5F5F5;
    border-bottom: 2px solid #2D7A3E;
}

th {
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #333333;
}

td {
    padding: 12px;
    border-bottom: 1px solid #EEEEEE;
}

tr:hover {
    background: #F9F9F9;
}

/* Alternating rows */
tbody tr:nth-child(even) {
    background: #FAFAFA;
}
```

#### Cards

```css
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-bottom: 20px;
}

.card-header {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 15px;
    color: #333333;
}
```

#### Dropdowns

```css
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-toggle {
    padding: 10px 15px;
    background: white;
    border: 1px solid #CCCCCC;
    border-radius: 4px;
    cursor: pointer;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #CCCCCC;
    border-radius: 4px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    z-index: 1000;
}

.dropdown-item {
    padding: 10px 15px;
    cursor: pointer;
}

.dropdown-item:hover {
    background: #E8F5E9;
}
```

### Page-Specific Layouts

#### Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│ Dashboard                                                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ ┌─────────────────────┐  ┌─────────────────────┐          │
│ │ Quick Stats         │  │ Recent Entries      │          │
│ │                     │  │                     │          │
│ │ Total Items: 25     │  │ 2024-01-15 | 12%   │          │
│ │ Total Entries: 45   │  │ 2024-01-14 | 8%    │          │
│ │ Avg Waste: 10.5%    │  │ 2024-01-13 | 15%   │          │
│ └─────────────────────┘  └─────────────────────┘          │
│                                                             │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Top Wasted Items This Week                            │  │
│ │                                                       │  │
│ │ 1. Bread        25.5 kg    15.30% ████░░░░░░        │  │
│ │ 2. Croissant    18.0 kg    12.00% ███░░░░░░░        │  │
│ │ 3. Flour        10.5 kg     8.50% ██░░░░░░░░        │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

#### Item Management

```
┌─────────────────────────────────────────────────────────────┐
│ Item Management                          [+ New Item]       │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Filter by Category: [All ▼]        Search: [_________]     │
│                                                             │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Name          Category      Price      Actions        │  │
│ ├───────────────────────────────────────────────────────┤  │
│ │ Bread         Product       $2.50      [Edit] [Del]   │  │
│ │ Flour         Ingredient    $1.20      [Edit] [Del]   │  │
│ │ Croissant     Product       $3.00      [Edit] [Del]   │  │
│ │ Sugar         Ingredient    $0.80      [Edit] [Del]   │  │
│ │ Baguette      Product       $2.00      [Edit] [Del]   │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                             │
│ Showing 5 of 25 items                    [1] 2 3 4 5 >     │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

#### Daily Entry Creation

```
┌─────────────────────────────────────────────────────────────┐
│ Create Daily Entry                                          │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Date: [2024-01-15 ▼]                    [Create Entry]     │
│                                                             │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Add Item                                              │  │
│ │                                                       │  │
│ │ Select Item: [Bread ▼]                                │  │
│ │                                                       │  │
│ │ Used Quantity:   [____] kg                            │  │
│ │ Wasted Quantity: [____] kg                            │  │
│ │                                                       │  │
│ │ Notes (optional):                                     │  │
│ │ [_____________________________________________]       │  │
│ │                                                       │  │
│ │                                    [Add Item]         │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                             │
│ Current Items (3)                                           │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Item      Used    Wasted   Notes           Remove    │  │
│ ├───────────────────────────────────────────────────────┤  │
│ │ Bread     10 kg   2 kg     Stale           [X]       │  │
│ │ Flour     5 kg    0.5 kg   -               [X]       │  │
│ │ Sugar     2 kg    0 kg      -               [X]       │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                             │
│ Total: Used 17 kg, Wasted 2.5 kg (12.8% waste)             │
│                                                             │
│                              [Save Entry] [Cancel]          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

#### Analytics

```
┌─────────────────────────────────────────────────────────────┐
│ Analytics                                                   │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│ Time Period: [Daily ▼]  Date Range: [Last 30 Days ▼]       │
│                                                             │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Most Wasted Items                      [Show All]     │  │
│ │                                                       │  │
│ │ Rank  Item        Wasted    Waste Rating            │  │
│ │ ───────────────────────────────────────────────────  │  │
│ │  1    Bread       25.5 kg   15.30% ████░░░░░░       │  │
│ │  2    Croissant   18.0 kg   12.00% ███░░░░░░░       │  │
│ │  3    Flour       10.5 kg    8.50% ██░░░░░░░░       │  │
│ │  4    Sugar        8.2 kg    7.20% ██░░░░░░░░       │  │
│ │  5    Baguette     6.8 kg    6.10% █░░░░░░░░░       │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                             │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Most Used Items                        [Show All]     │  │
│ │                                                       │  │
│ │ Rank  Item        Used      Waste Rating            │  │
│ │ ───────────────────────────────────────────────────  │  │
│ │  1    Flour       150.0 kg   8.50% ██░░░░░░░░       │  │
│ │  2    Bread       140.5 kg  15.30% ████░░░░░░       │  │
│ │  3    Croissant   132.0 kg  12.00% ███░░░░░░░       │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                             │
│ ┌───────────────────────────────────────────────────────┐  │
│ │ Usage vs Waste Comparison                             │  │
│ │                                                       │  │
│ │ Item        Used      Wasted    Comparison           │  │
│ │ ─────────────────────────────────────────────────────│  │
│ │ Bread       140.5 kg  25.5 kg   ████████████░░       │  │
│ │ Flour       150.0 kg  10.5 kg   ██████████████░      │  │
│ │ Croissant   132.0 kg  18.0 kg   █████████████░░      │  │
│ │                                                       │  │
│ │ ⚠ Wasted > Used: None                                │  │
│ └───────────────────────────────────────────────────────┘  │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Responsive Design

**Breakpoints**:
- Desktop: > 1024px (full layout with sidebar)
- Tablet: 768px - 1024px (collapsible sidebar)
- Mobile: < 768px (hamburger menu, stacked layout)

**Mobile Adaptations**:
- Sidebar becomes hamburger menu
- Tables become card-based layouts
- Forms stack vertically
- Buttons become full-width
- Reduced padding and margins

### Accessibility Features

1. **Keyboard Navigation**: All interactive elements accessible via Tab
2. **Focus Indicators**: Clear visual focus states
3. **ARIA Labels**: Screen reader support for complex components
4. **Color Contrast**: WCAG AA compliant (4.5:1 for normal text)
5. **Form Labels**: All inputs have associated labels
6. **Error Messages**: Clearly associated with form fields
7. **Skip Links**: "Skip to main content" for screen readers

### Loading States

```css
/* Loading Spinner */
.spinner {
    border: 3px solid #F5F5F5;
    border-top: 3px solid #2D7A3E;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

/* Skeleton Loading */
.skeleton {
    background: linear-gradient(90deg, #F5F5F5 25%, #EEEEEE 50%, #F5F5F5 75%);
    background-size: 200% 100%;
    animation: loading 1.5s ease-in-out infinite;
}
```

### Success/Error Messages

```css
/* Success Alert */
.alert-success {
    background: #E8F5E9;
    border-left: 4px solid #2D7A3E;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}

/* Error Alert */
.alert-error {
    background: #FFEBEE;
    border-left: 4px solid #D32F2F;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
}
```


## Implementation Notes

### Development Phases

**Phase 1: Foundation** (Week 1-2)
- Set up Laravel project and database
- Implement authentication system
- Create base layout and navigation
- Set up testing infrastructure

**Phase 2: Core Features** (Week 3-4)
- Implement Item Management module
- Implement Daily Entry System
- Create database migrations and models
- Write unit tests for core functionality

**Phase 3: Analytics** (Week 5-6)
- Implement Analytics Engine
- Create Records Viewer
- Implement sorting and filtering
- Write property-based tests

**Phase 4: Polish** (Week 7-8)
- Implement Dashboard
- Refine UI/UX
- Complete test coverage
- Performance optimization
- Documentation

### Performance Considerations

1. **Database Indexing**: Indexes on user_id, date, category for fast queries
2. **Eager Loading**: Use `with()` to prevent N+1 query problems
3. **Query Optimization**: Use aggregation queries instead of loading all records
4. **Caching**: Cache analytics calculations for frequently accessed data
5. **Pagination**: Limit result sets to prevent memory issues

### Security Checklist

- ✅ CSRF protection on all forms
- ✅ SQL injection prevention via Eloquent ORM
- ✅ XSS protection via Blade escaping
- ✅ Password hashing with bcrypt
- ✅ Session security (HTTP-only, encrypted)
- ✅ User data isolation via global scopes
- ✅ Authorization checks via policies
- ✅ Input validation on all forms
- ✅ HTTPS enforcement in production
- ✅ Rate limiting on authentication

### Deployment Considerations

**Environment Requirements**:
- PHP 8.2+
- MySQL 8.0+
- Composer for dependency management
- Node.js for asset compilation (if using Laravel Mix/Vite)

**Production Configuration**:
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Configure proper database credentials
- Set up SSL certificate
- Configure session driver (database or Redis)
- Set up backup strategy
- Configure error logging

**Optimization**:
- Run `php artisan config:cache`
- Run `php artisan route:cache`
- Run `php artisan view:cache`
- Enable OPcache for PHP
- Use CDN for static assets

### Maintenance and Monitoring

**Logging**:
- Application logs in `storage/logs/laravel.log`
- Monitor for errors and warnings
- Set up log rotation

**Backups**:
- Daily database backups
- Weekly full system backups
- Test restore procedures regularly

**Monitoring**:
- Track application performance
- Monitor database query times
- Track user activity and errors
- Set up alerts for critical issues

### Future Enhancements

**Potential Features**:
- Export analytics to PDF/Excel
- Email notifications for high waste rates
- Multi-location support for chain businesses
- Mobile app for on-the-go entry
- Barcode scanning for items
- Predictive analytics for waste patterns
- Integration with inventory management systems
- Team collaboration features
- Custom reporting builder

### Conclusion

This design document provides a comprehensive blueprint for implementing the EcoManager Food and Waste Management System. The architecture follows Laravel best practices, ensures complete data isolation between users, and provides a clean, efficient interface for food shop owners to track and analyze their inventory usage and waste patterns.

The dual testing approach with both unit tests and property-based tests ensures correctness at both the specific and universal levels, while the modular design allows for easy maintenance and future enhancements.

