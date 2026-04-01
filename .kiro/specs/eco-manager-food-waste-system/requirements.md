# Requirements Document

## Introduction

The EcoManager Food and Waste Management System is a Laravel-based web application designed to help businesses track food inventory usage and waste. The system enables users to create and manage food items, record daily usage and waste quantities, and analyze waste patterns through comprehensive reporting and analytics. The application uses a clean green-and-white interface with authentication, item management, daily entry tracking, historical records viewing, and analytics capabilities.

## Glossary

- **EcoManager**: The food and waste management web application system
- **User**: An authenticated person who interacts with the EcoManager system
- **Item**: A food product or ingredient tracked in the system (e.g., bread, flour, croissant)
- **Category**: A classification for items (e.g., "ingredient", "product")
- **Daily_Entry**: A record created for a specific date containing multiple item usage records
- **Entry_Item**: A specific item record within a daily entry, containing quantity and usage type
- **Used_Quantity**: The amount of an item consumed for productive purposes
- **Wasted_Quantity**: The amount of an item discarded or lost
- **Waste_Rating**: A calculated percentage representing wasted quantity divided by total quantity
- **Dashboard**: The main landing page displaying summary information from all system modules
- **Authentication_System**: The login and session management component
- **Item_Manager**: The module responsible for creating, editing, and deleting items
- **Entry_System**: The module for creating and managing daily entries
- **Records_Viewer**: The module for viewing and sorting historical entries
- **Analytics_Engine**: The component that calculates waste ratings and usage statistics

## Requirements

### Requirement 1: User Authentication

**User Story:** As a user, I want to log into the system with my credentials, so that I can access my food and waste management data securely.

#### Acceptance Criteria

1. THE Authentication_System SHALL provide a login form accepting username or email and password
2. WHEN valid credentials are submitted, THE Authentication_System SHALL authenticate the user and redirect to the Dashboard
3. WHEN invalid credentials are submitted, THE Authentication_System SHALL display an error message and remain on the login page
4. THE Authentication_System SHALL maintain user session state across page navigation
5. THE Authentication_System SHALL provide a logout function that terminates the user session

### Requirement 2: Dashboard Display

**User Story:** As a user, I want to see a summary of recent activity when I log in, so that I can quickly understand my current food usage and waste status.

#### Acceptance Criteria

1. WHEN a user successfully authenticates, THE EcoManager SHALL display the Dashboard as the landing page
2. THE Dashboard SHALL display a summary of recent daily entries
3. THE Dashboard SHALL display summary statistics from the Analytics_Engine
4. THE Dashboard SHALL display recent waste ratings
5. THE Dashboard SHALL provide navigation links to all other system modules

### Requirement 3: Item Creation

**User Story:** As a user, I want to create new food items, so that I can track them in my daily entries.

#### Acceptance Criteria

1. THE Item_Manager SHALL provide a form to create new items with name, category, and price fields
2. WHEN a user submits a valid item creation form, THE Item_Manager SHALL save the item to the database
3. WHEN a user submits an item with a duplicate name, THE Item_Manager SHALL display an error message
4. THE Item_Manager SHALL require item name and category fields before saving
5. THE Item_Manager SHALL allow price to be optional or zero

### Requirement 4: Item Modification

**User Story:** As a user, I want to edit existing items, so that I can update their details when needed.

#### Acceptance Criteria

1. THE Item_Manager SHALL provide an edit function for existing items
2. WHEN a user selects an item to edit, THE Item_Manager SHALL display a form pre-populated with current item data
3. WHEN a user submits valid changes, THE Item_Manager SHALL update the item in the database
4. THE Item_Manager SHALL allow modification of item name, category, and price
5. WHEN a user attempts to change an item name to a duplicate, THE Item_Manager SHALL display an error message

### Requirement 5: Item Deletion

**User Story:** As a user, I want to delete items I no longer need, so that my item list remains relevant and organized.

#### Acceptance Criteria

1. THE Item_Manager SHALL provide a delete function for existing items
2. WHEN a user initiates item deletion, THE Item_Manager SHALL display a confirmation prompt
3. WHEN a user confirms deletion, THE Item_Manager SHALL remove the item from the database
4. WHEN a user cancels deletion, THE Item_Manager SHALL retain the item without changes
5. IF an item is referenced in existing daily entries, THEN THE Item_Manager SHALL prevent deletion and display an informative message

### Requirement 6: Item Categorization

**User Story:** As a user, I want to assign categories to items, so that I can organize them by type such as ingredients or products.

#### Acceptance Criteria

1. THE Item_Manager SHALL provide a category field for each item
2. THE Item_Manager SHALL support category values including "ingredient" and "product"
3. THE Item_Manager SHALL allow users to define custom category values
4. WHEN displaying items, THE Item_Manager SHALL show the assigned category
5. THE Item_Manager SHALL allow filtering items by category

### Requirement 7: Daily Entry Creation

**User Story:** As a user, I want to create daily entries for specific dates, so that I can record food usage and waste for that day.

#### Acceptance Criteria

1. THE Entry_System SHALL provide a form to create daily entries with a date field
2. WHEN a user submits a valid date, THE Entry_System SHALL create a new daily entry record
3. THE Entry_System SHALL assign a unique identifier to each daily entry
4. THE Entry_System SHALL default the date field to the current date
5. WHEN a user attempts to create a duplicate entry for the same date, THE Entry_System SHALL display an error message

### Requirement 8: Adding Items to Daily Entries

**User Story:** As a user, I want to add items to daily entries with quantities, so that I can record what was used or wasted.

#### Acceptance Criteria

1. WHEN a daily entry is open, THE Entry_System SHALL provide a dropdown to select items from the Item_Manager
2. WHEN a user selects an item, THE Entry_System SHALL display input fields for used quantity and wasted quantity
3. THE Entry_System SHALL provide an optional notes field for each entry item
4. WHEN a user submits valid entry item data, THE Entry_System SHALL save the entry item to the database with reference to the daily entry and item
5. THE Entry_System SHALL allow multiple items to be added to a single daily entry

### Requirement 9: Quantity Tracking

**User Story:** As a user, I want to separately track used and wasted quantities, so that I can distinguish between productive usage and waste.

#### Acceptance Criteria

1. THE Entry_System SHALL provide separate input fields for used quantity and wasted quantity
2. THE Entry_System SHALL accept numeric values greater than or equal to zero for both quantity types
3. THE Entry_System SHALL store used quantity and wasted quantity as separate values in the database
4. WHEN a user enters non-numeric values, THE Entry_System SHALL display a validation error
5. THE Entry_System SHALL allow either used quantity or wasted quantity to be zero, but require at least one to be greater than zero

### Requirement 10: Entry Notes

**User Story:** As a user, I want to add optional notes to entry items, so that I can record context or explanations for the quantities.

#### Acceptance Criteria

1. THE Entry_System SHALL provide a notes field for each entry item
2. THE Entry_System SHALL allow the notes field to be empty
3. THE Entry_System SHALL store notes text in the database with the entry item
4. THE Entry_System SHALL display notes when viewing entry items
5. THE Entry_System SHALL support notes text up to 500 characters in length

### Requirement 11: Historical Records Viewing

**User Story:** As a user, I want to view past daily entries, so that I can review historical usage and waste data.

#### Acceptance Criteria

1. THE Records_Viewer SHALL display a list of all daily entries
2. THE Records_Viewer SHALL show the date for each daily entry
3. WHEN a user selects a daily entry, THE Records_Viewer SHALL display all entry items for that date
4. THE Records_Viewer SHALL display item name, used quantity, wasted quantity, and notes for each entry item
5. THE Records_Viewer SHALL paginate results when more than 50 entries exist

### Requirement 12: Date-Based Sorting

**User Story:** As a user, I want to sort records by date, so that I can view entries chronologically.

#### Acceptance Criteria

1. THE Records_Viewer SHALL provide a sort option for date
2. WHEN a user selects date sorting, THE Records_Viewer SHALL order entries by date in descending order (newest first)
3. THE Records_Viewer SHALL allow toggling between ascending and descending date order
4. THE Records_Viewer SHALL persist the selected sort order during the session
5. THE Records_Viewer SHALL default to descending date order when first loaded

### Requirement 13: Name-Based Sorting

**User Story:** As a user, I want to sort records by item name, so that I can group entries by product or ingredient.

#### Acceptance Criteria

1. THE Records_Viewer SHALL provide a sort option for item name
2. WHEN a user selects name sorting, THE Records_Viewer SHALL order entry items alphabetically by item name
3. THE Records_Viewer SHALL allow toggling between ascending and descending alphabetical order
4. THE Records_Viewer SHALL group entry items by item name when name sorting is active
5. THE Records_Viewer SHALL persist the selected sort order during the session

### Requirement 14: Waste Rating Calculation

**User Story:** As a user, I want to see waste ratings for items, so that I can identify which items have the highest waste percentages.

#### Acceptance Criteria

1. THE Analytics_Engine SHALL calculate waste rating using the formula: (wasted_quantity / (used_quantity + wasted_quantity)) × 100
2. THE Analytics_Engine SHALL calculate waste ratings for each item across all daily entries
3. THE Analytics_Engine SHALL display waste ratings as percentages with two decimal places
4. WHEN an item has zero total quantity, THE Analytics_Engine SHALL display a waste rating of zero
5. THE Analytics_Engine SHALL update waste ratings when new daily entries are added

### Requirement 15: Usage Comparison Analytics

**User Story:** As a user, I want to compare used versus wasted quantities, so that I can understand my waste patterns.

#### Acceptance Criteria

1. THE Analytics_Engine SHALL calculate total used quantity per item across all daily entries
2. THE Analytics_Engine SHALL calculate total wasted quantity per item across all daily entries
3. THE Analytics_Engine SHALL display used and wasted quantities side by side for comparison
4. THE Analytics_Engine SHALL provide visual indicators when wasted quantity exceeds used quantity
5. THE Analytics_Engine SHALL allow filtering comparisons by date range

### Requirement 16: Most Wasted Items Ranking

**User Story:** As a user, I want to see which items are wasted the most, so that I can focus on reducing waste for those items.

#### Acceptance Criteria

1. THE Analytics_Engine SHALL rank items by total wasted quantity in descending order
2. THE Analytics_Engine SHALL display the top 10 most wasted items by default
3. THE Analytics_Engine SHALL allow users to expand the list to show all items
4. THE Analytics_Engine SHALL display both absolute wasted quantity and waste rating for each item
5. THE Analytics_Engine SHALL update rankings when new daily entries are added

### Requirement 17: Most Used Items Ranking

**User Story:** As a user, I want to see which items are used the most, so that I can understand my primary inventory needs.

#### Acceptance Criteria

1. THE Analytics_Engine SHALL rank items by total used quantity in descending order
2. THE Analytics_Engine SHALL display the top 10 most used items by default
3. THE Analytics_Engine SHALL allow users to expand the list to show all items
4. THE Analytics_Engine SHALL display both absolute used quantity and waste rating for each item
5. THE Analytics_Engine SHALL update rankings when new daily entries are added

### Requirement 18: Time-Based Statistics

**User Story:** As a user, I want to see daily, weekly, and monthly statistics per item, so that I can track trends over different time periods.

#### Acceptance Criteria

1. THE Analytics_Engine SHALL calculate daily totals for used and wasted quantities per item
2. THE Analytics_Engine SHALL calculate weekly totals by summing daily entries within 7-day periods
3. THE Analytics_Engine SHALL calculate monthly totals by summing daily entries within calendar months
4. THE Analytics_Engine SHALL provide a time period selector with options for daily, weekly, and monthly views
5. WHEN a user selects a time period, THE Analytics_Engine SHALL display statistics aggregated for that period

### Requirement 19: User Interface Theme

**User Story:** As a user, I want a clean and visually appealing interface, so that the application is pleasant to use.

#### Acceptance Criteria

1. THE EcoManager SHALL apply a color scheme using white and green as primary colors
2. THE EcoManager SHALL display "EcoManager" as the title in the header at the leftmost position
3. THE EcoManager SHALL use consistent typography and spacing throughout the interface
4. THE EcoManager SHALL ensure sufficient contrast between text and background for readability
5. THE EcoManager SHALL apply the theme consistently across all pages and modules

### Requirement 20: Navigation Structure

**User Story:** As a user, I want easy navigation between different sections, so that I can quickly access the features I need.

#### Acceptance Criteria

1. THE EcoManager SHALL display a sidebar menu on the right side of the interface
2. THE EcoManager SHALL include navigation tabs in the sidebar for Dashboard, Item Management, Daily Entry System, Records Viewing, and Analytics
3. WHEN a user clicks a navigation tab, THE EcoManager SHALL navigate to the corresponding module
4. THE EcoManager SHALL highlight the currently active tab in the sidebar
5. THE EcoManager SHALL keep the sidebar visible and accessible on all pages

### Requirement 21: Dropdown Interface Elements

**User Story:** As a user, I want dropdown menus for selections, so that I can efficiently choose items and filters without clutter.

#### Acceptance Criteria

1. THE Entry_System SHALL use a dropdown menu for item selection when adding items to daily entries
2. THE Records_Viewer SHALL use dropdown menus for sort and filter options
3. WHEN a user clicks a dropdown, THE EcoManager SHALL expand the menu to show available options
4. WHEN a user selects an option, THE EcoManager SHALL collapse the dropdown and apply the selection
5. THE EcoManager SHALL close dropdowns when a user clicks outside the dropdown area

### Requirement 22: Database Schema for Items

**User Story:** As a developer, I want a properly structured items table, so that item data is stored efficiently and reliably.

#### Acceptance Criteria

1. THE EcoManager SHALL maintain an items table with columns for id, name, category, price, and timestamps
2. THE EcoManager SHALL use id as the primary key with auto-increment
3. THE EcoManager SHALL enforce unique constraints on the name column
4. THE EcoManager SHALL store price as a decimal type with two decimal places
5. THE EcoManager SHALL include created_at and updated_at timestamp columns

### Requirement 23: Database Schema for Daily Entries

**User Story:** As a developer, I want a properly structured daily entries table, so that entry data is stored efficiently and reliably.

#### Acceptance Criteria

1. THE EcoManager SHALL maintain a daily_entries table with columns for id, date, and timestamps
2. THE EcoManager SHALL use id as the primary key with auto-increment
3. THE EcoManager SHALL enforce unique constraints on the date column
4. THE EcoManager SHALL store date as a date type
5. THE EcoManager SHALL include created_at and updated_at timestamp columns

### Requirement 24: Database Schema for Entry Items

**User Story:** As a developer, I want a properly structured entry items table, so that item usage data is stored efficiently and reliably.

#### Acceptance Criteria

1. THE EcoManager SHALL maintain an entry_items table with columns for id, daily_entry_id, item_id, used_quantity, wasted_quantity, notes, and timestamps
2. THE EcoManager SHALL use id as the primary key with auto-increment
3. THE EcoManager SHALL define daily_entry_id as a foreign key referencing daily_entries table
4. THE EcoManager SHALL define item_id as a foreign key referencing items table
5. THE EcoManager SHALL store used_quantity and wasted_quantity as decimal types with two decimal places
6. THE EcoManager SHALL store notes as a text type allowing up to 500 characters
7. THE EcoManager SHALL include created_at and updated_at timestamp columns

### Requirement 25: Data Integrity

**User Story:** As a developer, I want referential integrity enforced, so that the database remains consistent and reliable.

#### Acceptance Criteria

1. WHEN a daily entry is deleted, THE EcoManager SHALL cascade delete all associated entry items
2. THE EcoManager SHALL prevent deletion of items that are referenced in entry items
3. THE EcoManager SHALL enforce foreign key constraints between entry_items and daily_entries tables
4. THE EcoManager SHALL enforce foreign key constraints between entry_items and items tables
5. THE EcoManager SHALL validate data types and constraints before inserting or updating records

## Technical Constraints

- The system SHALL be built using the Laravel PHP framework
- The system SHALL use MySQL as the database management system
- The system SHALL use HTML and CSS for frontend presentation
- The system SHALL follow Laravel MVC architecture patterns
- The system SHALL use Laravel Eloquent ORM for database operations
