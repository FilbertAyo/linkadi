# Test Image Display Issue

## Quick Diagnostic

Run these commands on your server to diagnose why company logo and cover image don't display:

### 1. Check if files exist

```bash
cd ~

# Check profile images (working)
ls -la public_html/storage/profile-images/

# Check company logos (not working)
ls -la public_html/storage/company-logos/

# Check cover images (not working)
ls -la public_html/storage/cover-images/

# Check packages (working)
ls -la public_html/storage/packages/
```

### 2. Check what's in the database

```bash
cd ~/linkadi-web

php artisan tinker
```

Then in tinker:
```php
// Get your latest profile
$profile = App\Models\Profile::latest()->first();

// Check what paths are stored
echo "Profile Image: " . $profile->profile_image . "\n";
echo "Company Logo: " . $profile->company_logo . "\n";
echo "Cover Image: " . $profile->cover_image . "\n";

// Check if files exist on disk
echo "\nFile exists check:\n";
echo "Profile: " . (file_exists(public_path('storage/' . $profile->profile_image)) ? 'YES' : 'NO') . "\n";
echo "Company: " . (file_exists(public_path('storage/' . $profile->company_logo)) ? 'YES' : 'NO') . "\n";
echo "Cover: " . (file_exists(public_path('storage/' . $profile->cover_image)) ? 'YES' : 'NO') . "\n";

exit
```

### 3. Check browser console

1. Open your profile page: https://linkadi.co.tz/your-profile-slug
2. Press F12 to open browser console
3. Go to "Network" tab
4. Refresh the page
5. Look for any failed image requests (red color)
6. Click on the failed requests to see the exact URL

### 4. Test direct URL access

Try accessing the images directly in your browser:

```
https://linkadi.co.tz/storage/profile-images/[filename].jpg
https://linkadi.co.tz/storage/company-logos/[filename].jpg
https://linkadi.co.tz/storage/cover-images/[filename].jpg
```

Replace `[filename]` with actual filename from step 1.

## Common Issues & Solutions

### Issue 1: Files don't exist in the directory

**Symptom:** `ls -la` shows empty directory

**Solution:** Files weren't uploaded. Check:
```bash
# Are they in the wrong location?
ls -la ~/linkadi-web/public/storage/company-logos/
ls -la ~/linkadi-web/public/storage/cover-images/

# If yes, move them:
cp -R ~/linkadi-web/public/storage/* ~/public_html/storage/
```

### Issue 2: Database has wrong path

**Symptom:** Database shows path like `company-logos/abc.jpg` but file doesn't exist

**Solution:** File was deleted or never uploaded. Re-upload the image.

### Issue 3: Permission issue

**Symptom:** Files exist but browser shows 403 Forbidden

**Solution:**
```bash
chmod -R 755 ~/public_html/storage/company-logos/
chmod -R 755 ~/public_html/storage/cover-images/
```

### Issue 4: Wrong path in database

**Symptom:** Database shows `linkadi-web/public/storage/...` or similar

**Solution:** Config not updated. Check:
```bash
cd ~/linkadi-web
grep PUBLIC_STORAGE_PATH .env
php artisan config:clear
```

### Issue 5: Files uploaded before fix

**Symptom:** Old uploads don't work, new uploads do

**Solution:** Old files are in wrong location. Move them:
```bash
# Find and move old files
if [ -d ~/linkadi-web/public/storage ]; then
    cp -R ~/linkadi-web/public/storage/* ~/public_html/storage/
fi
```

## Automated Check

Run this script to diagnose everything:

```bash
chmod +x check-uploaded-files.sh
./check-uploaded-files.sh
```

## What to Report

After running the diagnostics, report:

1. **Do the files exist?**
   - Profile images: YES/NO
   - Company logos: YES/NO
   - Cover images: YES/NO

2. **What does the database show?**
   - Copy the output from tinker

3. **What error appears in browser console?**
   - 404 Not Found?
   - 403 Forbidden?
   - No error but image doesn't show?

4. **Can you access the image directly via URL?**
   - YES/NO

## Most Likely Cause

Based on your description (profile images work, company logo and cover don't), the most likely causes are:

1. **Files uploaded before the fix** - They're in `linkadi-web/public/storage/` instead of `public_html/storage/`

2. **Directories don't exist:**
   ```bash
   mkdir -p ~/public_html/storage/company-logos
   mkdir -p ~/public_html/storage/cover-images
   chmod -R 775 ~/public_html/storage/company-logos
   chmod -R 775 ~/public_html/storage/cover-images
   ```

3. **Cache issue** - Browser cached broken images:
   - Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
   - Or clear browser cache

## Quick Fix to Try First

```bash
cd ~

# Ensure directories exist
mkdir -p public_html/storage/{profile-images,company-logos,cover-images,packages,qr-codes}

# Set permissions
chmod -R 775 public_html/storage

# Copy any files from wrong location
cp -R linkadi-web/public/storage/* public_html/storage/ 2>/dev/null || true

# Clear Laravel cache
cd linkadi-web
php artisan config:clear
php artisan cache:clear

# Check what files you have
ls -la ~/public_html/storage/company-logos/
ls -la ~/public_html/storage/cover-images/
```

Then try uploading NEW company logo and cover image to test.
