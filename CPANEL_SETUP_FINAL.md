# ✅ FINAL cPanel Setup - Correct Solution

## The Real Problem

On cPanel, your structure is:
```
/home/linkadic/
├── linkadi-web/          # Laravel app (NOT public)
│   └── public/           # This is NOT the web root!
└── public_html/          # This IS the web root
```

When Laravel uses `public_path('storage')`, it resolves to:
- `linkadi-web/public/storage/` ❌ WRONG (not accessible via web)

We need it to point to:
- `public_html/storage/` ✅ CORRECT (accessible via web)

## Solution: Use Environment Variable

I've updated the config to use an environment variable that you can set on the server.

### Step 1: Update Your Server's .env File

SSH into your server and edit the `.env` file:

```bash
cd ~/linkadi-web
nano .env
```

Add this line at the end:

```bash
PUBLIC_STORAGE_PATH=/home/linkadic/public_html/storage
```

Save and exit (Ctrl+X, then Y, then Enter)

### Step 2: Create Storage Directory Structure

```bash
cd ~
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes
```

### Step 3: Set Permissions

```bash
chmod -R 775 public_html/storage
chown -R linkadic:linkadic public_html/storage
```

### Step 4: Copy Existing Files (if any)

```bash
# If you have files in linkadi-web/public/storage
cp -R linkadi-web/public/storage/* public_html/storage/ 2>/dev/null || true

# If you have files in linkadi-web/storage/app/public
cp -R linkadi-web/storage/app/public/* public_html/storage/ 2>/dev/null || true
```

### Step 5: Clear Cache

```bash
cd linkadi-web
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Step 6: Verify

```bash
# Check that the environment variable is loaded
cd linkadi-web
php artisan tinker
```

In tinker, type:
```php
config('filesystems.disks.public.root')
```

Should output: `/home/linkadic/public_html/storage`

Type `exit` to quit tinker.

### Step 7: Test Upload

1. Go to https://linkadi.co.tz
2. Navigate to profile builder
3. Upload a profile image
4. Click save
5. Check if file appears in: `~/public_html/storage/profile-images/`

```bash
ls -la ~/public_html/storage/profile-images/
```

## Complete Setup Script

Save this as `setup-storage-final.sh` and run it:

```bash
#!/bin/bash

echo "========================================="
echo "LINKADI STORAGE SETUP - FINAL FIX"
echo "========================================="

cd ~

# Create directories
echo "Creating directories..."
mkdir -p public_html/storage/{profile-images,company-logos,cover-images,packages,qr-codes}

# Set permissions
echo "Setting permissions..."
chmod -R 775 public_html/storage
chown -R linkadic:linkadic public_html/storage

# Copy existing files
echo "Copying existing files..."
cp -R linkadi-web/public/storage/* public_html/storage/ 2>/dev/null || true
cp -R linkadi-web/storage/app/public/* public_html/storage/ 2>/dev/null || true

# Add environment variable to .env
echo "Updating .env..."
cd linkadi-web
if ! grep -q "PUBLIC_STORAGE_PATH" .env; then
    echo "" >> .env
    echo "# Storage path for cPanel" >> .env
    echo "PUBLIC_STORAGE_PATH=/home/linkadic/public_html/storage" >> .env
    echo "✅ Added PUBLIC_STORAGE_PATH to .env"
else
    echo "✅ PUBLIC_STORAGE_PATH already in .env"
fi

# Clear cache
echo "Clearing cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Verify
echo ""
echo "========================================="
echo "VERIFICATION"
echo "========================================="
cd ~
echo "Storage directory:"
ls -ld public_html/storage/

echo ""
echo "Testing write access..."
if touch public_html/storage/.test-$$ 2>/dev/null; then
    rm -f public_html/storage/.test-$$
    echo "✅ Write test PASSED"
else
    echo "❌ Write test FAILED"
fi

echo ""
echo "Checking config..."
cd linkadi-web
STORAGE_PATH=$(php artisan tinker --execute="echo config('filesystems.disks.public.root');" 2>/dev/null | tail -1)
echo "Storage path in config: $STORAGE_PATH"

if [[ "$STORAGE_PATH" == *"public_html/storage"* ]]; then
    echo "✅ Config is correct!"
else
    echo "⚠️  Config might not be correct. Expected path with 'public_html/storage'"
fi

echo ""
echo "========================================="
echo "DONE!"
echo "========================================="
echo ""
echo "Now test uploading an image on your website."
echo "Files should appear in: ~/public_html/storage/"
echo ""
```

## Troubleshooting

### Issue: Files still going to linkadi-web/public/storage

**Check .env:**
```bash
cd ~/linkadi-web
grep PUBLIC_STORAGE_PATH .env
```

Should show: `PUBLIC_STORAGE_PATH=/home/linkadic/public_html/storage`

If not, add it:
```bash
echo "PUBLIC_STORAGE_PATH=/home/linkadic/public_html/storage" >> .env
php artisan config:clear
```

### Issue: Permission denied

```bash
chmod -R 775 ~/public_html/storage
```

### Issue: Directory doesn't exist

```bash
mkdir -p ~/public_html/storage/{profile-images,company-logos,cover-images,packages,qr-codes}
```

### Issue: Config not updating

```bash
cd ~/linkadi-web
php artisan config:clear
php artisan cache:clear
php artisan optimize:clear
```

## Verification Commands

```bash
# 1. Check .env has the variable
grep PUBLIC_STORAGE_PATH ~/linkadi-web/.env

# 2. Check directory exists
ls -la ~/public_html/storage/

# 3. Check permissions (should be 775)
stat ~/public_html/storage/ | grep Access

# 4. Check Laravel config
cd ~/linkadi-web && php artisan tinker --execute="echo config('filesystems.disks.public.root');"

# 5. Test write
touch ~/public_html/storage/test.txt && rm ~/public_html/storage/test.txt && echo "Write OK"
```

## Expected Result

After setup:

1. Upload image through website
2. File saves to: `/home/linkadic/public_html/storage/profile-images/xxx.jpg`
3. Accessible at: `https://linkadi.co.tz/storage/profile-images/xxx.jpg`
4. Image displays on website ✅

## Important Notes

- The `.env` file is NOT tracked by git (it's in .gitignore)
- You need to add `PUBLIC_STORAGE_PATH` on the server only
- Don't commit the `.env` file to git
- This setup works perfectly with cPanel's structure

## Summary

**Problem:** `public_path()` points to `linkadi-web/public/` not `public_html/`

**Solution:** Use environment variable `PUBLIC_STORAGE_PATH` to override the path

**Steps:**
1. Add `PUBLIC_STORAGE_PATH=/home/linkadic/public_html/storage` to `.env`
2. Create directories in `public_html/storage/`
3. Set permissions to 775
4. Clear cache
5. Test upload

**Result:** Images save to `public_html/storage/` and display correctly! ✅
