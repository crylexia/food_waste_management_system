# Implementation Plan: EcoManager Food and Waste Management System

## Overview

This implementation plan breaks down the EcoManager system into discrete, sequential coding tasks. The system is a Laravel-based multi-user web application for tracking food inventory usage and waste. Each task builds on previous work, with checkpoints to ensure quality and allow for user feedback.

The implementation follows this order:
1. Project foundation and database setup
2. Authentication system
3. Core models with user isolation
4. Item Management module
5. Daily Entry System module
6. Records Viewer module
7. Analytics Engine module
8. Dashboard module
9. UI/UX polish and final integration

## Tasks

- [ ] 1. Set up Laravel project foundation and database
  - Create new Laravel project with required dependencies
  - Configure database connection for MySQL
  - Set up testing environment with SQLite in-memory database
  - Configure session driver to use database
  - _Requirements: Technical Constraints_

- [ ] 2. Create database migrations
  - [ ] 2.1 Create users table migration
    - Create migration file `2024_01_01_000000_create_users_table.php`
    - Define schema: id, name, email (unique), password, remember_token, timestamps
    - Add index on email column
    - _Requirements: 1.1, 1.2_

  - [ ] 2.2 Create items table migration
    - Create migration file `2024_01_01_000001_create_items_table.php`
    - Define schema: id, user_id (FK), name, category, price (decimal), timestamps
    - Add unique constraint on (user_id, name) combination
    - Add indexes on user_id and category columns
    - Set up foreign key with CASCADE delete on user_id
    - _Requirements: 3.1, 3.3, 6.1, 22.1-22.5_

  - [ ] 2.3 Create daily_entries table migration
    - Create migration file `2024_01_01_000002_create_daily_entries_table.php`
    - Define schema: id, user_id (FK), date, timestamps
    - Add unique constraint on (user_id, date) combination
    - Add indexes on user_id and date columns
    - Set up foreign key with CASCADE delete on user_id
    - _Requirements: 7.1, 7.5, 23.1-23.5_

  - [ ] 2.4 Create entry_items table migration
    - Create migration file `2024_01_01_000003_create_entry_items_table.php`
    - Define schema: id, daily_entry_id (FK), item_id (FK), used_quantity (decimal), wasted_quantity (decimal), notes (text), timestamps
    - Add indexes on daily_entry_id and item_id columns
    - Set up foreign key with CASCADE delete on daily_entry_id
    - Set up foreign key with RESTRICT delete on item_id
    - Add CHECK constraints for non-negative quantities and at least one quantity > 0
    - _Requirements: 8.1, 9.1, 9.2, 24.1-24.7, 25.1-25.4_

  - [ ] 2.5 Create sessions table migration
    - Create migration for database session storage
    - Run all migrations to set up database schema
    - _Requirements: 1.4_

- [x] 3. Create core models with user isolation
  - [x] 3.1 Create User model
    - Extend Laravel's Authenticatable class
    - Define fillable fields: name, email, password
    - Define hidden fields: password, remember_token
    - Add password hashing cast
    - Define relationships: hasMany items, hasMany dailyEntries
    - _Requirements: 1.1, 1.2_

  - [x] 3.2 Create UserScope global scope
    - Create `app/Scopes/UserScope.php`
    - Implement Scope interface
    - Apply WHERE user_id = auth()->id() filter automatically
    - _Requirements: Multi-user data isolation_

  - [x] 3.3 Create Item model
    - Define fillable fields: user_id, name, category, price
    - Add decimal cast for price field
    - Apply UserScope global scope
    - Define relationships: belongsTo user, hasMany entryItems
    - Add canBeDeleted() method to check if item is referenced
    - _Requirements: 3.1-3.5, 5.5, 22.1-22.5_

  - [x] 3.4 Create DailyEntry model
    - Define fillable fields: user_id, date
    - Add date cast for date field
    - Apply UserScope global scope
    - Define relationships: belongsTo user, hasMany entryItems
    - Add getWasteRatingAttribute() accessor for calculating entry waste rating
    - _Requirements: 7.1-7.5, 23.1-23.5_

  - [x] 3.5 Create EntryItem model
    - Define fillable fields: daily_entry_id, item_id, used_quantity, wasted_quantity, notes
    - Add decimal casts for quantity fields
    - Define relationships: belongsTo dailyEntry, belongsTo item
    - Add getWasteRatingAttribute() accessor for calculating item waste rating
    - _Requirements: 8.1-8.5, 9.1-9.5, 10.1-10.5, 24.1-24.7_

- [x] 4. Implement authentication system
  - [x] 4.1 Create AuthController
    - Implement showLoginForm() method to display login page
    - Implement login() method with credential validation
    - Implement logout() method with session invalidation
    - Add session regeneration on successful login for security
    - _Requirements: 1.1-1.5_

  - [x] 4.2 Create authentication routes
    - Add GET /login route for login form
    - Add POST /login route for authentication
    - Add POST /logout route for logout
    - Apply 'guest' middleware to login routes
    - _Requirements: 1.1-1.5_

  - [x] 4.3 Create login view
    - Create `resources/views/auth/login.blade.php`
    - Add email and password input fields
    - Add "Remember me" checkbox
    - Display validation errors
    - Include CSRF token
    - _Requirements: 1.1, 1.3_

  - [x] 4.4 Configure authentication middleware
    - Set up Authenticate middleware to protect routes
    - Configure redirect to login page for unauthenticated users
    - Store intended URL for post-login redirect
    - _Requirements: 1.4_

  - [ ]* 4.5 Write unit tests for authentication
    - Test successful login with valid credentials
    - Test failed login with invalid credentials
    - Test logout functionality
    - Test session persistence across requests
    - _Requirements: 1.2, 1.3, 1.5_

- [x] 5. Create base layout and navigation
  - [x] 5.1 Create main layout template
    - Create `resources/views/layouts/app.blade.php`
    - Add header with "EcoManager" title and logout button
    - Add sidebar navigation with links to all modules
    - Add main content area
    - Include CSS and JavaScript assets
    - _Requirements: 19.1-19.5, 20.1-20.5_

  - [x] 5.2 Create CSS stylesheet
    - Create `public/css/app.css`
    - Define color scheme (white and green theme)
    - Style header, sidebar, and main content areas
    - Style buttons, forms, tables, and cards
    - Add responsive design breakpoints
    - _Requirements: 19.1-19.5_

  - [x] 5.3 Implement navigation highlighting
    - Add JavaScript to highlight active navigation tab
    - Persist active tab state during navigation
    - _Requirements: 20.4_

- [x] 6. Checkpoint - Ensure authentication and layout work
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 7. Implement Item Management module
  - [x] 7.1 Create ItemController with CRUD methods
    - Implement index() to list all user's items
    - Implement create() to show item creation form
    - Implement store() to save new items
    - Implement edit() to show item edit form
    - Implement update() to save item changes
    - Implement destroy() to delete items with reference check
    - _Requirements: 3.1-3.5, 4.1-4.5, 5.1-5.5_

  - [ ] 7.2 Create Form Request validators
    - Create StoreItemRequest with validation rules
    - Create UpdateItemRequest with validation rules
    - Add unique name constraint scoped to user_id
    - Add custom error messages
    - _Requirements: 3.3, 3.4, 4.5, 22.3_

  - [ ] 7.3 Create item views
    - Create `resources/views/items/index.blade.php` for item list
    - Create `resources/views/items/create.blade.php` for creation form
    - Create `resources/views/items/edit.blade.php` for edit form
    - Add category filter dropdown
    - Display success/error messages
    - _Requirements: 3.1-3.5, 6.1-6.5_

  - [ ] 7.4 Add item routes
    - Add resource routes for items (index, create, store, edit, update, destroy)
    - Apply 'auth' middleware to all item routes
    - _Requirements: 3.1-3.5_

  - [ ]* 7.5 Write unit tests for Item Management
    - Test item creation with valid data
    - Test item creation with duplicate name
    - Test item update functionality
    - Test item deletion when not referenced
    - Test item deletion prevention when referenced
    - Test category filtering
    - _Requirements: 3.1-3.5, 5.5, 6.5_

  - [ ]* 7.6 Write property test for Item CRUD round-trip
    - **Property 5: Item CRUD Round-Trip**
    - **Validates: Requirements 3.2, 4.3, 5.3**
    - Test that creating, retrieving, updating, and deleting items works correctly
    - _Requirements: 3.2, 4.3, 5.3_

  - [ ]* 7.7 Write property test for item name uniqueness
    - **Property 6: Item Name Uniqueness**
    - **Validates: Requirements 3.3, 4.5, 22.3**
    - Test that duplicate item names are rejected per user
    - _Requirements: 3.3, 4.5, 22.3_

  - [ ]* 7.8 Write property test for user data isolation
    - **Property 4: User Data Isolation**
    - **Validates: Multi-user requirement**
    - Test that users can only see their own items
    - _Requirements: Multi-user data isolation_

- [ ] 8. Checkpoint - Ensure Item Management works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 9. Implement Daily Entry System module
  - [ ] 9.1 Create DailyEntryController
    - Implement index() to list user's daily entries
    - Implement create() to show entry creation form
    - Implement store() to save new daily entries
    - Implement show() to display entry details with items
    - Implement addItem() to add items to entries
    - Implement removeItem() to remove items from entries
    - Implement destroy() to delete entries
    - _Requirements: 7.1-7.5, 8.1-8.5_

  - [ ] 9.2 Create Form Request validators for entries
    - Create StoreDailyEntryRequest with date validation
    - Create StoreEntryItemRequest with quantity validation
    - Add unique date constraint scoped to user_id
    - Add custom validation for at least one quantity > 0
    - _Requirements: 7.5, 9.2, 9.4, 9.5_

  - [ ] 9.3 Create daily entry views
    - Create `resources/views/entries/index.blade.php` for entry list
    - Create `resources/views/entries/create.blade.php` for entry creation
    - Add item selection dropdown populated from user's items
    - Add quantity input fields (used and wasted)
    - Add notes textarea
    - Display current entry items with remove buttons
    - Show running total of used/wasted quantities
    - _Requirements: 7.1-7.5, 8.1-8.5, 9.1-9.5, 10.1-10.5_

  - [ ] 9.4 Add daily entry routes
    - Add resource routes for entries (index, create, store, show, destroy)
    - Add POST route for adding items to entries
    - Add DELETE route for removing items from entries
    - Apply 'auth' middleware to all entry routes
    - _Requirements: 7.1-7.5, 8.1-8.5_

  - [ ]* 9.5 Write unit tests for Daily Entry System
    - Test daily entry creation with valid date
    - Test daily entry creation with duplicate date
    - Test adding items to entries
    - Test quantity validation
    - Test notes field
    - Test removing items from entries
    - _Requirements: 7.1-7.5, 8.1-8.5, 9.1-9.5, 10.1-10.5_

  - [ ]* 9.6 Write property test for daily entry round-trip
    - **Property 12: Daily Entry CRUD Round-Trip**
    - **Validates: Requirements 7.2**
    - Test that creating and retrieving daily entries works correctly
    - _Requirements: 7.2_

  - [ ]* 9.7 Write property test for entry item round-trip
    - **Property 15: Entry Item CRUD Round-Trip**
    - **Validates: Requirements 8.4, 9.3, 10.3**
    - Test that adding items to entries preserves all data
    - _Requirements: 8.4, 9.3, 10.3_

  - [ ]* 9.8 Write property test for quantity validation
    - **Property 17: Quantity Validation**
    - **Validates: Requirements 9.2, 9.4, 9.5**
    - Test that quantity validation rules are enforced
    - _Requirements: 9.2, 9.4, 9.5_

- [ ] 10. Checkpoint - Ensure Daily Entry System works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 11. Implement Records Viewer module
  - [ ] 11.1 Create RecordController
    - Implement index() method with sorting and pagination
    - Add support for date sorting (ascending/descending)
    - Add support for name sorting (ascending/descending)
    - Store sort preferences in session
    - Implement pagination (50 entries per page)
    - _Requirements: 11.1-11.5, 12.1-12.5, 13.1-13.5_

  - [ ] 11.2 Create records view
    - Create `resources/views/records/index.blade.php`
    - Add sort dropdown for date/name selection
    - Add sort order toggle (ascending/descending)
    - Display daily entries with all entry items
    - Show item name, used quantity, wasted quantity, notes
    - Add pagination controls
    - _Requirements: 11.1-11.5, 12.1-12.5, 13.1-13.5_

  - [ ] 11.3 Add records routes
    - Add GET route for records index with query parameters
    - Apply 'auth' middleware
    - _Requirements: 11.1-11.5_

  - [ ]* 11.4 Write unit tests for Records Viewer
    - Test date sorting (ascending and descending)
    - Test name sorting (ascending and descending)
    - Test pagination with more than 50 entries
    - Test sort preference persistence
    - _Requirements: 11.5, 12.2, 12.3, 12.4, 13.2, 13.3, 13.5_

  - [ ]* 11.5 Write property test for date sorting
    - **Property 20: Date Sorting Invariant**
    - **Validates: Requirements 12.2, 12.3**
    - Test that date sorting orders entries chronologically
    - _Requirements: 12.2, 12.3_

  - [ ]* 11.6 Write property test for name sorting
    - **Property 21: Name Sorting Invariant**
    - **Validates: Requirements 13.2, 13.3**
    - Test that name sorting orders items alphabetically
    - _Requirements: 13.2, 13.3_

- [ ] 12. Implement Analytics Engine module
  - [ ] 12.1 Create AnalyticsService
    - Create `app/Services/AnalyticsService.php`
    - Implement calculateWasteRating() method
    - Implement getMostWastedItems() method
    - Implement getMostUsedItems() method
    - Implement getUsageComparison() method
    - Implement getTimePeriodStatistics() method with daily/weekly/monthly aggregation
    - _Requirements: 14.1-14.5, 15.1-15.5, 16.1-16.5, 17.1-17.5, 18.1-18.5_

  - [ ] 12.2 Create AnalyticsController
    - Implement index() method to display analytics
    - Add support for time period selection (daily/weekly/monthly)
    - Add support for date range filtering
    - Use AnalyticsService for all calculations
    - _Requirements: 14.1-14.5, 15.1-15.5, 16.1-16.5, 17.1-17.5, 18.1-18.5_

  - [ ] 12.3 Create analytics view
    - Create `resources/views/analytics/index.blade.php`
    - Add time period dropdown (daily/weekly/monthly)
    - Add date range filter dropdown
    - Display most wasted items ranking with waste ratings
    - Display most used items ranking with waste ratings
    - Display usage vs waste comparison
    - Add visual indicators (progress bars) for waste ratings
    - Add "Show All" buttons to expand rankings
    - _Requirements: 14.1-14.5, 15.1-15.5, 16.1-16.5, 17.1-17.5, 18.1-18.5_

  - [ ] 12.4 Add analytics routes
    - Add GET route for analytics index
    - Apply 'auth' middleware
    - _Requirements: 14.1-14.5_

  - [ ]* 12.5 Write unit tests for Analytics Engine
    - Test waste rating calculation with normal values
    - Test waste rating calculation with zero quantities
    - Test most wasted items ranking
    - Test most used items ranking
    - Test date range filtering
    - Test time period aggregation (daily/weekly/monthly)
    - _Requirements: 14.1-14.5, 15.1-15.5, 16.1-16.5, 17.1-17.5, 18.1-18.5_

  - [ ]* 12.6 Write property test for waste rating calculation
    - **Property 24: Waste Rating Calculation**
    - **Validates: Requirements 14.1, 14.3, 14.4**
    - Test that waste rating formula is correct for all inputs
    - _Requirements: 14.1, 14.3, 14.4_

  - [ ]* 12.7 Write property test for quantity aggregation
    - **Property 27: Quantity Aggregation**
    - **Validates: Requirements 15.1, 15.2**
    - Test that totals are correctly summed across entries
    - _Requirements: 15.1, 15.2_

  - [ ]* 12.8 Write property test for waste ranking
    - **Property 29: Waste Ranking Invariant**
    - **Validates: Requirements 16.1, 16.4**
    - Test that items are correctly ranked by wasted quantity
    - _Requirements: 16.1, 16.4_

- [ ] 13. Checkpoint - Ensure Analytics Engine works
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 14. Implement Dashboard module
  - [ ] 14.1 Create DashboardController
    - Implement index() method to display dashboard
    - Fetch recent daily entries (last 5-10)
    - Calculate summary statistics (total items, total entries, avg waste rate)
    - Fetch top wasted items for quick overview
    - _Requirements: 2.1-2.5_

  - [ ] 14.2 Create dashboard view
    - Create `resources/views/dashboard.blade.php`
    - Display recent entries with dates and waste ratings
    - Display quick stats cards
    - Display top wasted items this week
    - Add navigation links to all modules
    - _Requirements: 2.1-2.5_

  - [ ] 14.3 Add dashboard route
    - Add GET route for dashboard
    - Set as default redirect after login
    - Apply 'auth' middleware
    - _Requirements: 2.1_

  - [ ]* 14.4 Write unit tests for Dashboard
    - Test dashboard displays recent entries
    - Test dashboard displays summary statistics
    - Test dashboard displays top wasted items
    - _Requirements: 2.2, 2.3, 2.4_

- [ ] 15. UI/UX polish and final integration
  - [ ] 15.1 Enhance form styling
    - Style all form elements consistently
    - Add focus states and error states
    - Ensure proper spacing and alignment
    - Add loading states for form submissions
    - _Requirements: 19.1-19.5_

  - [ ] 15.2 Enhance table styling
    - Style all tables consistently
    - Add hover states for rows
    - Add alternating row colors
    - Ensure proper column alignment
    - _Requirements: 19.1-19.5_

  - [ ] 15.3 Implement dropdown menus
    - Style dropdown menus consistently
    - Add expand/collapse animations
    - Implement click-outside-to-close functionality
    - _Requirements: 21.1-21.5_

  - [ ] 15.4 Add success/error message styling
    - Create alert components for success messages
    - Create alert components for error messages
    - Add auto-dismiss functionality
    - _Requirements: 19.1-19.5_

  - [ ] 15.5 Implement responsive design
    - Add mobile breakpoints
    - Convert sidebar to hamburger menu on mobile
    - Stack forms vertically on mobile
    - Convert tables to card layout on mobile
    - _Requirements: 19.1-19.5_

  - [ ] 15.6 Add accessibility features
    - Ensure all interactive elements are keyboard accessible
    - Add ARIA labels for screen readers
    - Ensure color contrast meets WCAG AA standards
    - Add skip links for navigation
    - _Requirements: 19.4_

  - [ ]* 15.7 Write integration tests
    - Test complete user flow: login → create item → create entry → view records → view analytics
    - Test navigation between all modules
    - Test error handling across modules
    - _Requirements: All_

- [ ] 16. Final checkpoint - Complete system verification
  - Run all tests (unit, property, integration)
  - Verify all requirements are met
  - Test multi-user data isolation
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Checkpoints ensure incremental validation
- Property tests validate universal correctness properties
- Unit tests validate specific examples and edge cases
- All implementation tasks involve writing, modifying, or testing code
