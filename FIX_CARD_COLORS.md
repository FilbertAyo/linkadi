# Fix Card Colors Issue on Production

## Problem
Production site shows 5 card colors even though you've updated to only 2 colors locally. This is because the production database still has the old data.

## Solution

You need to update the `card_colors` field in the `packages` table on production. Here are two ways to do it:

### Option 1: Update via Database Directly (Quickest)

1. SSH into your production server
2. Connect to your database (MySQL/MariaDB):
   ```bash
   mysql -u your_username -p your_database_name
   ```

3. Update the NFC Card package:
   ```sql
   UPDATE packages 
   SET card_colors = '["white", "black"]' 
   WHERE slug = 'nfc-card';
   ```
   
   Replace `["white", "black"]` with your actual 2 colors (in JSON array format).

4. Exit MySQL:
   ```sql
   exit;
   ```

5. Clear Laravel caches:
   ```bash
   cd ~/linkadi-web
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

### Option 2: Update via Laravel Tinker (Recommended)

1. SSH into your production server
2. Navigate to project directory:
   ```bash
   cd ~/linkadi-web
   ```

3. Run Laravel Tinker:
   ```bash
   php artisan tinker
   ```

4. Update the package:
   ```php
   $package = App\Models\Package::where('slug', 'nfc-card')->first();
   $package->card_colors = ['white', 'black']; // Replace with your 2 colors
   $package->save();
   exit
   ```

5. Clear caches:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

### Option 3: Update via Admin Panel (If Available)

If you have an admin panel for managing packages:
1. Log in to admin panel
2. Go to Packages → Edit NFC Card package
3. Update the "Available Card Colors" field to only your 2 colors
4. Save

## Verify the Fix

After updating, visit: https://linkadi.co.tz/dashboard/cards/checkout/nfc-card

You should now see only your 2 card colors instead of 5.

## Why This Happened

- Card colors are stored in the `packages` table's `card_colors` column (JSON)
- When you updated locally, it only changed your local database
- Production database still had the old values
- The checkout page reads directly from the database, not from cache

## Prevention

After updating the database, run your deployment script to ensure caches are cleared:
```bash
./deploy.sh
```

This will ensure all caches are refreshed and the new colors are displayed.
