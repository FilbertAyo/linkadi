# 🔧 STORAGE FIX - Image Upload Not Working

## Your Problem

✅ Old images show (after copying from `linkadi-web/storage/app/public/`)  
❌ New uploads don't save/show

## Root Cause

**PERMISSIONS!** You set `755` but the web server needs `775` to write files.

```
755 = rwxr-xr-x  ❌ Owner can write, others can only read
775 = rwxrwxr-x  ✅ Owner AND group can write (web server can save)
```

## Quick Fix (Run on Server)

```bash
# 1. Fix permissions
chmod -R 775 public_html/storage

# 2. Verify
ls -la public_html/storage/
# Should show: drwxrwxr-x (note the 'w' in the middle!)

# 3. Clear Laravel cache
cd linkadi-web
php artisan config:clear
php artisan cache:clear

# 4. Test upload again
```

## Complete Setup (If Starting Fresh)

### Step 1: Upload the fix script

Upload `fix-storage-permissions.sh` to your server home directory.

### Step 2: Run the script

```bash
chmod +x fix-storage-permissions.sh
./fix-storage-permissions.sh
```

### Step 3: Clear Laravel cache

```bash
cd linkadi-web
php artisan config:clear
php artisan cache:clear
```

### Step 4: Test

Try uploading a profile image. It should now work!

## Diagnostic Tool

If still having issues, run the diagnostic:

```bash
chmod +x diagnose-storage.sh
./diagnose-storage.sh
```

This will tell you exactly what's wrong.

## Manual Setup (Alternative)

```bash
# Navigate to home
cd ~

# Create directory structure
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes

# Set WRITE permissions (775, not 755!)
chmod -R 775 public_html/storage

# Set ownership
chown -R linkadic:linkadic public_html/storage

# Copy existing files (if any)
cp -R linkadi-web/storage/app/public/* public_html/storage/

# Clear cache
cd linkadi-web
php artisan config:clear
php artisan cache:clear
```

## Understanding the Issue

### What Happens with 755 (Wrong)

```
User uploads image → Laravel tries to save → Permission denied → Upload fails
```

### What Happens with 775 (Correct)

```
User uploads image → Laravel saves successfully → Image displays ✅
```

## Verification Checklist

Run these commands to verify everything is correct:

```bash
# 1. Check permissions (should be 775)
ls -ld public_html/storage
# Expected: drwxrwxr-x

# 2. Test write access
touch public_html/storage/test.txt
# If this fails, permissions are still wrong

# 3. Check subdirectories exist
ls -la public_html/storage/
# Should see: profile-images, company-logos, cover-images, packages, qr-codes

# 4. Check Laravel config
grep "public_path" linkadi-web/config/filesystems.php
# Should show: 'root' => public_path('storage'),
```

## Common Mistakes

### ❌ Mistake 1: Using 755 instead of 775
```bash
chmod -R 755 public_html/storage  # WRONG - web server can't write
```

### ✅ Correct:
```bash
chmod -R 775 public_html/storage  # RIGHT - web server can write
```

### ❌ Mistake 2: Forgetting subdirectories
```bash
mkdir public_html/storage  # Only creates parent
```

### ✅ Correct:
```bash
mkdir -p public_html/storage/profile-images  # Creates all needed dirs
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes
```

### ❌ Mistake 3: Not clearing cache
```bash
# Make changes but forget to clear cache
```

### ✅ Correct:
```bash
cd linkadi-web
php artisan config:clear
php artisan cache:clear
```

## Files Included

1. **fix-storage-permissions.sh** - Automated fix script
2. **diagnose-storage.sh** - Diagnostic tool to identify issues
3. **DEPLOYMENT_STORAGE_SETUP.md** - Complete deployment guide
4. **STORAGE_FIX_README.md** - This file (quick reference)

## Expected Results

After fixing permissions:

1. **Profile image upload:**
   - File saves to: `public_html/storage/profile-images/xxx.jpg`
   - Accessible at: `https://linkadi.co.tz/storage/profile-images/xxx.jpg`

2. **Company logo upload:**
   - File saves to: `public_html/storage/company-logos/xxx.jpg`
   - Accessible at: `https://linkadi.co.tz/storage/company-logos/xxx.jpg`

3. **Cover image upload:**
   - File saves to: `public_html/storage/cover-images/xxx.jpg`
   - Accessible at: `https://linkadi.co.tz/storage/cover-images/xxx.jpg`

## Still Not Working?

If uploads still fail after setting 775:

1. **Check Laravel logs:**
   ```bash
   tail -f linkadi-web/storage/logs/laravel.log
   ```

2. **Check PHP error logs:**
   ```bash
   tail -f /usr/local/apache/logs/error_log
   # Or wherever your cPanel stores PHP errors
   ```

3. **Verify disk space:**
   ```bash
   df -h
   ```

4. **Check PHP upload settings:**
   ```bash
   php -i | grep upload_max_filesize
   php -i | grep post_max_size
   ```

5. **Contact hosting support** if none of the above work - there may be server-level restrictions.

## Summary

**The fix is simple:**

```bash
chmod -R 775 public_html/storage
cd linkadi-web && php artisan config:clear
```

That's it! The `775` permission allows the web server to write files, which is what you need for uploads to work.
