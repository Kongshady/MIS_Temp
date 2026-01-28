# Equipment Usage/Borrowing Feature - Setup Guide

## Overview
Added equipment usage/borrowing tracking functionality to equipment.php with the following features:
- Record equipment usage with user details
- Track date, user name, item name, quantity (number of uses)
- Record purpose and OR number
- Monitor equipment status (functional/not functional)
- View recent usage history

## Database Setup

1. **Run the SQL file to create the equipment_usage table:**
   - Open phpMyAdmin
   - Select your 'clinlab' database
   - Go to SQL tab
   - Execute the file: `sql/equipment_usage.sql`

   OR run this command in your terminal:
   ```
   mysql -u root -p clinlab < c:\xampp\htdocs\mis_project\sql\equipment_usage.sql
   ```

## Features Added

### 1. Equipment Usage Recording
- **Button:** "Record Equipment Usage" - Opens a modal to record new usage
- **Required Fields:**
  - Equipment (dropdown from existing equipment)
  - Date Used
  - User's Name
  - Item Name
  - Quantity (Number of Uses)
  - Purpose
  - Status (Functional/Not Functional)
- **Optional Fields:**
  - OR Number
  - Remarks

### 2. Equipment Usage Display
- Shows the 20 most recent equipment usage records
- Displays:
  - Date used
  - Equipment name
  - Item name
  - User's name
  - Quantity (number of uses)
  - Purpose (truncated to 50 chars)
  - OR Number
  - Status (color-coded: green for functional, red for not functional)

## How to Use

1. **Setup Database:**
   - Execute the `sql/equipment_usage.sql` file in your database

2. **Record Equipment Usage:**
   - Go to Equipment page (modules/equipment.php)
   - Click "Record Equipment Usage" button
   - Fill in all required fields
   - Submit the form

3. **View Usage Records:**
   - The usage records are displayed in the "Equipment Usage Records" section
   - Most recent 20 records are shown

## Files Modified/Created

### Created:
- `sql/equipment_usage.sql` - Database table structure

### Modified:
- `modules/equipment.php` - Added usage recording functionality and display

## Database Table Structure

```sql
equipment_usage (
  usage_id - Auto-increment primary key
  equipment_id - Foreign key to equipment table
  date_used - Date when equipment was used
  user_name - Name of person using the equipment
  item_name - Name of the item/equipment being used
  quantity - Number of uses
  purpose - Purpose of equipment usage
  or_number - Official Receipt number (optional)
  status - Equipment status (functional/not_functional)
  remarks - Additional notes (optional)
  datetime_added - Timestamp of record creation
)
```

## Status Indicators

- **Green (Functional):** Equipment working properly after use
- **Red (Not Functional):** Equipment has issues/not working after use

## Future Enhancements (Optional)

You may consider adding:
- Search/filter for usage records
- Export usage reports to PDF/Excel
- Usage statistics and analytics
- Return date tracking for borrowed equipment
- Email notifications for equipment issues
- Usage history per equipment in equipment_details.php
