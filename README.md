# 🥗 Food Waste Management System

A Laravel-based inventory and food waste tracking system that helps businesses monitor daily consumption, identify waste patterns, and receive actionable recommendations to reduce food loss and cost.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [Dashboard vs Analytics](#dashboard-vs-analytics)
- [Business Intelligence Layer](#business-intelligence-layer)
- [File Structure](#file-structure)

---

## Overview

This system moves beyond simple waste tracking. It is designed as a **Smart Food Waste Decision System** — giving users not just data, but interpretation and direction.

| Layer | Question Answered |# 🥗 Food Waste Management System

A Laravel-based inventory and food waste tracking system that helps businesses monitor daily consumption, identify waste patterns, forecast future waste trends, manage storage inventory, and receive actionable recommendations to reduce food loss and operational cost.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [Dashboard vs Analytics](#dashboard-vs-analytics)
- [Business Intelligence Layer](#business-intelligence-layer)
- [Predictive Analytics Layer](#predictive-analytics-layer)
- [File Structure](#file-structure)

---

## Overview

This system moves beyond simple waste tracking. It is designed as a **Smart Food Waste Decision System** — giving users not just data, but interpretation, forecasting, and operational direction.

| Layer | Question Answered |
|---|---|
| **Dashboard** | *"Do I need to act right now?"* |
| **Analytics** | *"Why is this happening?"* |
| **Predictive Analytics** | *"What will likely happen next?"* |

---

## Features

### 🏠 Operational Dashboard
- Today's waste value and quantity at a glance
- Week-over-week waste trend with direction indicator (↑↓)
- Overall waste rate with health status
- Top 3 most wasted items with visual bars
- Critical alerts only (danger + warning)
- Expiration alerts for near-expiry and expired inventory
- At-risk inventory value visibility
- Quick action shortcuts (log entry, view analytics, manage items)
- Last 5 entries with waste rate badges

### 🧊 Storage & Expiration Monitoring
- Inventory batch tracking with expiration dates
- Real-time monitoring of stored inventory
- Near-expiry and expired item alerts
- Dashboard-based spoilage warnings
- At-risk inventory value estimation
- FIFO (First-In-First-Out) monitoring support
- Expiration analytics and spoilage prevention recommendations

### 📊 Analytics — Descriptive
- Daily / Weekly / Monthly period statistics
- Most wasted items (top 10, bar chart)
- Most used items (top 10, bar chart)
- Full item comparison table with waste rate badges
- Category-level breakdown
- Filterable by date range (7 / 30 / 90 days or all time)

### 🧠 Analytics — Diagnostic + Prescriptive
- **Business KPI Layer** — Efficiency Score (0–100), Inventory Utilization Rate, Revenue Loss, Cost per Waste Unit, Overall Waste Rate
- **Decision Cards** — Contextual critical/warning/improvement cards derived from live data
- **Root Cause Analysis** — Classifies waste into: Overproduction, Low Demand, Storage/Spoilage, or Well-Managed
- **Recommendation Engine** — Prioritized (High/Medium) actionable steps with estimated savings per action
- **Impact Estimation** — Projects monetary savings if all recommendations are applied
- **Item Performance Intelligence** — Classifies each item as Star, Overproduction Risk, Low Demand, Critical Waste, or Normal
- **Storage & Expiration Intelligence** — Detects spoilage-prone inventory and recommends stock optimization strategies
- **System Insights** — Narrative summaries of inventory health

### 🔮 Predictive Analytics
- Forecasted waste projection (7–30 days)
- Overstock and waste risk scoring
- Projected monetary loss estimation
- Day-of-week and seasonal waste pattern detection
- Procurement and reorder quantity recommendations
- Confidence-based prediction reliability indicators

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel (PHP) |
| Database | MySQL |
| Frontend | Blade Templates, vanilla CSS, vanilla JS |
| Query Builder | Laravel Query Builder (DB facade) |
| Auth | Laravel built-in authentication |

---

## System Architecture

```txt
app/
├── Http/Controllers/
│   ├── DashboardController.php
│   ├── AnalyticsController.php
│   ├── InventoryController.php
│   └── PredictionController.php
│
├── Services/
│   ├── AnalyticsService.php
│   ├── BusinessIntelligenceService.php
│   ├── ExpirationMonitoringService.php  ← handled via StorageItem model + DashboardController
│   └── PredictiveAnalyticsService.php
│
└── Models/
    ├── Item.php
    ├── DailyEntry.php
    ├── EntryItem.php
    ├── StorageItem.php
    └── User.php

resources/views/
├── dashboard.blade.php
├── analytics/
│   └── index.blade.php
├── storage/
│   └── index.blade.php
└── predictions/
    └── index.blade.php
```

### Service Responsibilities

**`AnalyticsService`** — Raw data layer
- `getMostWastedItems(int $limit)`
- `getMostUsedItems(int $limit)`
- `getUsageComparison(?string $dateFrom)`
- `getTimePeriodStatistics(string $period, ?string $dateFrom)`
- `getMeaningfulInsights()`

**`BusinessIntelligenceService`** — Interpretation layer
- `getKPIs()`
- `getDecisionCards()`
- `getRecommendations()`
- `getRootCauseAnalysis()`
- `getItemPerformanceIntelligence()`
- `getImpactEstimation()`
- `getCategoryBreakdown()`
- `getPeriodSummary()`

**Expiration monitoring** — implemented directly in `StorageItem` model attributes and surfaced via `DashboardController`. No standalone service class is required.
- `StorageItem::getExpiryStatusAttribute()` — returns `expired`, `critical`, `soon`, or `ok`
- `StorageItem::getDaysUntilExpiryAttribute()` — integer days remaining until expiry
- `StorageItem::scopeActive()` / `scopeExpiring()` — query scopes used by dashboard and storage views

**`PredictiveAnalyticsService`**
- `forecastWaste()`
- `calculateRiskScores()`
- `projectMonetaryLoss()`
- `detectSeasonalPatterns()`
- `generateProcurementSuggestions()`

---

## Database Schema

### `items`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Owner |
| `name` | string | Item name |
| `category` | string | Item category |
| `price` | decimal | Price per unit |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `daily_entries`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Owner |
| `date` | date | Entry date |
| `waste_rating` | decimal | Computed waste % |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `entry_items`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `daily_entry_id` | bigint FK | Parent entry |
| `item_id` | bigint FK | Item reference |
| `used_quantity` | decimal | Units consumed |
| `wasted_quantity` | decimal | Units wasted |
| `waste_reason` | string | Cause of waste |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `storage_items`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `item_id` | bigint FK | Related item |
| `quantity` | decimal | Current stored quantity |
| `purchase_date` | date | Date stocked |
| `expiration_date` | date | Expiration date |
| `status` | string | Fresh / Near Expiry / Expired |
| `batch_number` | string | Batch / Lot number |
| `notes` | text | Optional notes |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## Installation

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Node.js

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/your-username/food-waste-management.git
cd food-waste-management
```

**2. Install dependencies**
```bash
composer install
```

**3. Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure database**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=food_waste_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

**5. Run migrations**
```bash
php artisan migrate
```

**6. Seed sample data (optional)**
```bash
php artisan db:seed
```

**7. Start the server**
```bash
php artisan serve
```

Visit:
```txt
http://localhost:8000
```

---

## Usage Guide

### Logging a Daily Entry
1. Navigate to **Entries → Create New Entry**
2. Select the date
3. Add items with their used and wasted quantities
4. Save — the system computes waste rate automatically

### Managing Storage Inventory
1. Navigate to **Storage**
2. Select an item to store or restock
3. Enter the quantity
4. Set the expiration date
5. (Optional) Add batch/lot number and notes
6. Click **Add to Storage**

The system automatically:
- Tracks active inventory batches
- Detects near-expiry and expired items
- Displays expiration alerts on the dashboard
- Marks depleted or expired inventory statuses
- Supports FIFO-based inventory monitoring

### Reading Storage Alerts
The Storage module provides real-time expiration monitoring:
- **Red alerts** indicate expired inventory
- **Orange alerts** indicate items nearing expiration
- **Green status** indicates healthy inventory
- Dashboard badges display active expiration alerts

Use the inventory table to:
- Monitor remaining shelf life
- Remove depleted stock
- Restore incorrectly marked batches
- Review batch history and notes

### Reading the Dashboard
The dashboard is designed for a **30-second operational review**:
- Check today's waste value
- Review waste trend direction
- Monitor expiration alerts
- Identify high-risk inventory
- Review critical operational warnings
- Access quick actions

### Reading Analytics
Analytics is designed for **weekly or monthly deep reviews**:
- Review the Efficiency Score
- Analyze Decision Cards
- Check Root Cause Analysis
- Follow Recommendation Engine outputs
- Compare historical periods
- Evaluate projected financial loss

---

## Dashboard vs Analytics

| Aspect | Dashboard | Analytics |
|---|---|---|
| Purpose | Fast daily decisions | Deep operational analysis |
| Time to read | ~30 seconds | 5–15 minutes |
| Audience | Operations staff | Management / Analysts |
| Data depth | Top-line only | Full breakdowns |
| Interactivity | Minimal | Filters and comparisons |
| Focus | Immediate action | Root cause and optimization |

---

## Business Intelligence Layer

### Efficiency Score (0–100)

```txt
max(0, min(100, 100 - (waste_rate × 1.5)))
```

| Score | Label |
|---|---|
| 85–100 | Excellent |
| 70–84 | Good |
| 50–69 | Needs Improvement |
| 0–49 | Critical |

### Item Classification Matrix

| Demand | Waste Rate | Classification |
|---|---|---|
| High (≥10 units) | High (≥25%) | 🏭 Overproduction Risk |
| Low (<5 units) | Any | 📦 Low Demand |
| Any | ≥50% | 🚨 Critical Waste |
| High (≥10 units) | Low (<25%) | ⭐ Star Item |
| Other | Other | 📊 Normal |

### Root Cause Categories

| Cause | Trigger Condition | Recommended Action |
|---|---|---|
| Overproduction | `total_used ≥ 10` AND `waste_rate ≥ 25%` | Reduce batch sizes |
| Low Demand | `total_used < 5` AND `total_wasted > 0` | Smaller procurement |
| Storage / Spoilage | `waste_rate ≥ 50%` | Improve FIFO compliance |
| Well-Managed | `waste_rate < 10%` | Replicate practices |

---

## Predictive Analytics Layer

The system includes a lightweight predictive analytics engine using historical operational data.

### Forecasted Waste
```txt
avg_daily_waste = SUM(wasted_quantity) / days
forecasted_waste = avg_daily_waste × days_ahead
```

### Waste Risk Trend
```txt
trend = recent_waste_rate - early_waste_rate
```

### Projected Monetary Loss
```txt
projected_monthly_loss = avg_weekly_loss × 4
```

### Procurement Recommendation
```txt
suggested_reduction = current_order_qty - safe_order_qty
```

### Prediction Reliability

| History Available | Confidence |
|---|---|
| < 7 days | Too early |
| 7–30 days | Low |
| 30–90 days | Reliable |
| 90+ days | Strong seasonal reliability |

---

## File Structure

```txt
app/
├── Http/
│   └── Controllers/
│       ├── DashboardController.php
│       ├── AnalyticsController.php
│       ├── InventoryController.php
│       └── PredictionController.php
│
├── Models/
│   ├── Item.php
│   ├── DailyEntry.php
│   ├── EntryItem.php
│   ├── StorageItem.php
│   └── User.php
│
└── Services/
    ├── AnalyticsService.php
    ├── BusinessIntelligenceService.php
    ├── ExpirationMonitoringService.php  ← handled via StorageItem model + DashboardController
    └── PredictiveAnalyticsService.php

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php
    ├── dashboard.blade.php
    ├── analytics/
    │   └── index.blade.php
    ├── storage/
    │   └── index.blade.php
    └── predictions/
        └── index.blade.php

database/
├── migrations/
├── seeders/
└── factories/

routes/
└── web.php
```

---

## Key Routes

| Method | URI | Controller | Description |
|---|---|---|---|
| GET | `/dashboard` | `DashboardController@index` | Operational dashboard |
| GET | `/analytics` | `AnalyticsController@index` | Full analytics |
| GET | `/storage` | `InventoryController@index` | Storage inventory |
| GET | `/predictions` | `PredictionController@index` | Predictive analytics |
| GET | `/entries` | `EntryController@index` | All entries |
| GET | `/items` | `ItemController@index` | Manage items |

---

*Built with Laravel · Designed for intelligent operational food waste reduction*
|---|---|
| **Dashboard** | *"Do I need to act right now?"* |
| **Analytics** | *"Why is this happening, and what should I do?"* |

---

## Features

### 🏠 Operational Dashboard
- Today's waste value and quantity at a glance
- Week-over-week waste trend with direction indicator (↑↓)
- Overall waste rate with health status
- Top 3 most wasted items with visual bars
- Critical alerts only (danger + warning)
- Quick action shortcuts (log entry, view analytics, manage items)
- Last 5 entries with waste rate badges

### 🧊 Storage & Expiration Monitoring
- Inventory batch tracking with expiration dates
- Real-time monitoring of stored inventory
- Near-expiry and expired item alerts
- Dashboard-based spoilage warnings
- At-risk inventory value estimation
- FIFO (First-In-First-Out) monitoring support
- Expiration analytics and spoilage prevention recommendations

### 📊 Analytics — Descriptive
- Daily / Weekly / Monthly period statistics
- Most wasted items (top 10, bar chart)
- Most used items (top 10, bar chart)
- Full item comparison table with waste rate badges
- Category-level breakdown
- Filterable by date range (7 / 30 / 90 days or all time)

### 🧠 Analytics — Diagnostic + Prescriptive
- **Business KPI Layer** — Efficiency Score (0–100), Inventory Utilization Rate, Revenue Loss, Cost per Waste Unit, Overall Waste Rate
- **Decision Cards** — Contextual critical/warning/improvement cards derived from live data
- **Root Cause Analysis** — Classifies waste into: Overproduction, Low Demand, Storage/Spoilage, or Well-Managed
- **Recommendation Engine** — Prioritized (High/Medium) actionable steps with estimated savings per action
- **Impact Estimation** — Projects monetary savings if all recommendations are applied
- **Item Performance Intelligence** — Classifies each item as Star, Overproduction Risk, Low Demand, Critical Waste, or Normal; includes profitability score
- **System Insights** — Narrative summaries of inventory health

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel (PHP) |
| Database | MySQL |
| Frontend | Blade Templates, vanilla CSS, vanilla JS |
| Query Builder | Laravel Query Builder (DB facade) |
| Auth | Laravel built-in authentication |

---

## System Architecture

```
app/
├── Http/Controllers/
│   ├── DashboardController.php      ← Executive snapshot view
│   └── AnalyticsController.php      ← Deep analysis view
│
├── Services/
│   ├── AnalyticsService.php         ← Core data queries
│   └── BusinessIntelligenceService.php ← KPIs, classification, recommendations
│
└── Models/
    ├── Item.php
    ├── DailyEntry.php
    └── EntryItem.php (entry_items)

resources/views/
├── dashboard.blade.php              ← Operational dashboard UI
└── analytics/
    └── index.blade.php             ← Full analytics UI
```

### Service Responsibilities

**`AnalyticsService`** — Raw data layer
- `getMostWastedItems(int $limit)`
- `getMostUsedItems(int $limit)`
- `getUsageComparison(?string $dateFrom)`
- `getTimePeriodStatistics(string $period, ?string $dateFrom)`
- `getMeaningfulInsights()`

**`BusinessIntelligenceService`** — Interpretation layer (depends on `AnalyticsService`)
- `getKPIs()` — Efficiency score, utilization, revenue loss, cost per waste unit
- `getDecisionCards()` — Contextual action cards from live data patterns
- `getRecommendations()` — Prioritized procurement/operational actions with saving estimates
- `getRootCauseAnalysis()` — Waste category classification (Overproduction, Low Demand, Spoilage, Efficient)
- `getItemPerformanceIntelligence()` — Per-item classification and profitability score
- `getImpactEstimation()` — Monetary impact projection if recommendations applied
- `getCategoryBreakdown()` — Aggregated stats grouped by item category
- `getPeriodSummary(Collection $periodStats)` — Period stats grouped by date

---

## Database Schema

### `items`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Owner |
| `name` | string | Item name |
| `category` | string | Item category |
| `price` | decimal | Price per unit |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `daily_entries`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Owner |
| `date` | date | Entry date |
| `waste_rating` | decimal | Computed waste % for the entry |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `entry_items`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `daily_entry_id` | bigint FK | Parent entry |
| `item_id` | bigint FK | Item reference |
| `used_quantity` | decimal | Units consumed |
| `wasted_quantity` | decimal | Units wasted |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

### `storage_items`
| Column | Type | Description |
|---|---|---|
| `id` | bigint PK | |
| `item_id` | bigint FK | Related item |
| `quantity` | decimal | Current stored quantity |
| `purchase_date` | date | Date stocked |
| `expiration_date` | date | Expiration date |
| `status` | string | Fresh / Near Expiry / Expired |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

---

## Installation

### Prerequisites
- PHP >= 8.1
- Composer
- MySQL >= 8.0
- Node.js (for asset compilation if needed)

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/your-username/food-waste-management.git
cd food-waste-management
```

**2. Install PHP dependencies**
```bash
composer install
```

**3. Set up environment**
```bash
cp .env.example .env
php artisan key:generate
```

**4. Configure your database in `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=food_waste_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

**5. Run migrations**
```bash
php artisan migrate
```

**6. (Optional) Seed sample data**
```bash
php artisan db:seed
```

**7. Serve the application**
```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## Usage Guide

### Logging a Daily Entry
1. Navigate to **Entries → Create New Entry**
2. Select the date
3. Add items with their used and wasted quantities
4. Save — the system computes waste rate automatically

### Reading the Dashboard
The dashboard is designed for a **30-second daily review**:
- Check today's waste value — is it higher than usual?
- Check the weekly trend arrow — is waste going up or down?
- Review any red or orange alerts — do they need action today?
- Use Quick Actions to log or review

### Managing Storage Inventory
1. Navigate to **Storage**
2. Select an item to store or restock
3. Enter the quantity
4. Set the expiration date
5. (Optional) Add batch/lot number and notes
6. Click **Add to Storage**

The system automatically:
- Tracks active inventory batches
- Detects near-expiry and expired items
- Displays expiration alerts on the dashboard
- Marks depleted or expired inventory statuses
- Supports FIFO-based inventory monitoring

### Reading Storage Alerts
The Storage module provides real-time expiration monitoring:
- **Red alerts** indicate expired inventory requiring immediate action
- **Orange alerts** indicate items nearing expiration
- **Green status** indicates healthy active inventory
- Dashboard badges display the number of active expiration alerts

Use the inventory table to:
- Monitor remaining shelf life
- Remove depleted stock
- Restore incorrectly marked batches
- Review batch history and notes

### Reading Analytics
Analytics is designed for **weekly or monthly deep reviews**:
- Start with the **Efficiency Score** — where does the business stand overall?
- Read **Decision Cards** for immediate context
- Check **Root Cause Analysis** — is the problem overproduction, low demand, or spoilage?
- Follow the **Recommendation Engine** — work through High priority items first
- Use the **Impact Estimation** to understand financial stakes
- Filter by date range to compare periods

---

## Dashboard vs Analytics

These two views share the same underlying data but serve completely different purposes.

| Aspect | Dashboard | Analytics |
|---|---|---|
| **Purpose** | Fast daily decisions | Deep exploration |
| **Time to read** | ~30 seconds | 5–15 minutes |
| **Audience** | Operations staff | Management / Analyst |
| **Data depth** | Top-line only | Full breakdowns |
| **Interactivity** | Minimal | Date filters, period toggle |
| **Answers** | *"Should I act today?"* | *"Why is this happening?"* |
| **BI features** | Critical alerts only | Full KPIs, root cause, recommendations |
| **Navigation** | → Links to Analytics | ← Referenced from Dashboard |

---

## Business Intelligence Layer

### Efficiency Score (0–100)
Computed as `max(0, min(100, 100 - (waste_rate × 1.5)))`.

| Score | Label |
|---|---|
| 85–100 | Excellent |
| 70–84 | Good |
| 50–69 | Needs Improvement |
| 0–49 | Critical |

### Item Classification Matrix

| Demand | Waste Rate | Classification |
|---|---|---|
| High (≥10 units) | High (≥25%) | 🏭 Overproduction Risk |
| Low (<5 units) | Any | 📦 Low Demand |
| Any | ≥50% | 🚨 Critical Waste |
| High (≥10 units) | Low (<25%) | ⭐ Star Item |
| Other | Other | 📊 Normal |

### Root Cause Categories

| Cause | Trigger Condition | Recommended Action |
|---|---|---|
| Overproduction | `total_used ≥ 10` AND `waste_rate ≥ 25%` | Calibrate batch sizes to consumption history |
| Low Demand | `total_used < 5` AND `total_wasted > 0` | Switch to on-demand or smaller batches |
| Storage / Spoilage | `waste_rate ≥ 50%` | Review cold chain and FIFO compliance |
| Well-Managed | `waste_rate < 10%` AND `total_used ≥ 5` | Replicate these practices across other items |

### Recommendation Priority

| Priority | Trigger |
|---|---|
| **High** | Waste rate ≥ 30% — reduce procurement by ~60% of waste rate |
| **Medium** | Low demand with waste — smaller batches or on-demand |
| **Medium** | Highest uncovered monetary loss — review storage |

### Impact Estimation Formula
```
estimated_saving = sum of (item.wasted_value × reduction_factor) per recommendation
projected_loss   = current_total_loss - estimated_saving
improvement_pct  = (estimated_saving / current_total_loss) × 100
```
> Note: Estimates are based on historical data. Actual results depend on implementation.

---

## File Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── DashboardController.php
│       ├── AnalyticsController.php
│       └── ...
├── Models/
│   ├── Item.php
│   ├── DailyEntry.php
│   └── EntryItem.php
│   └── StorageItem.php
│   └── User.php
└── Services/
    ├── AnalyticsService.php
    └── BusinessIntelligenceService.php

resources/
└── views/
    ├── layouts/
    │   └── app.blade.php
    ├── dashboard.blade.php
    ├── analytics/
    │   └── index.blade.php
    └── entries/
        ├── index.blade.php
        ├── create.blade.php
        └── show.blade.php

database/
├── migrations/
│   ├── ..._create_items_table.php
│   ├── ..._create_daily_entries_table.php
│   └── ..._create_entry_items_table.php
│   └── ...
└── seeders/
    └── DatabaseSeeder.php

routes/
└── web.php
```

---

## Key Routes

| Method | URI | Controller | Description |
|---|---|---|---|
| GET | `/dashboard` | `DashboardController@index` | Operational dashboard |
| GET | `/analytics` | `AnalyticsController@index` | Full analytics |
| GET | `/entries` | `EntryController@index` | All entries |
| GET | `/entries/create` | `EntryController@create` | Log new entry |
| GET | `/entries/{entry}` | `EntryController@show` | View single entry |
| GET | `/items` | `ItemController@index` | Manage items |

---

*Built with Laravel · Designed for operational food waste reduction*