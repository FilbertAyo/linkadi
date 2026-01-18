# 🚀 START HERE - Fix Your Image Upload Problem

## Your Issue

✅ Old images display (after copying files)  
❌ New uploads don't save/show

## The Problem

You used permission `755` but need `775` for the web server to write files.

## The Solution (2 minutes)

SSH into your server and run:

```bash
# 1. Create directories
mkdir -p public_html/storage/{profile-images,company-logos,cover-images,packages,qr-codes}

# 2. Set permissions
chmod -R 775 public_html/storage

# 3. Add storage path to .env (THIS IS CRITICAL!)
cd linkadi-web
echo "PUBLIC_STORAGE_PATH=/home/linkadic/public_html/storage" >> .env

# 4. Clear cache
php artisan config:clear && php artisan cache:clear
```

**That's it!** Try uploading an image now. It should work! ✨

**Why the .env change?** On cPanel, `public_path()` points to `linkadi-web/public/` instead of `public_html/`. The environment variable tells Laravel the correct path.

## Files in This Folder

### Quick Reference
- **START_HERE.md** ← You are here
- **QUICK_FIX_COMMANDS.txt** - Copy-paste commands
- **STORAGE_FIX_README.md** - Detailed fix guide

### Understanding
- **UNDERSTANDING_THE_PROBLEM.md** - Visual explanation of the issue

### Setup Scripts
- **setup-storage-complete.sh** - Automated setup (does everything)
- **fix-storage-permissions.sh** - Fix permissions only
- **diagnose-storage.sh** - Diagnostic tool

### Complete Guide
- **DEPLOYMENT_STORAGE_SETUP.md** - Full deployment documentation

## Which File Should I Use?

### If you want the fastest fix:
→ Open **QUICK_FIX_COMMANDS.txt** and copy-paste the commands

### If you want to understand what's wrong:
→ Read **UNDERSTANDING_THE_PROBLEM.md**

### If you want an automated solution:
→ Upload and run **setup-storage-complete.sh**

### If you want to troubleshoot:
→ Run **diagnose-storage.sh**

### If you want complete documentation:
→ Read **DEPLOYMENT_STORAGE_SETUP.md**

## Quick Diagnostic

Run this on your server to see if permissions are wrong:

```bash
ls -ld public_html/storage/
```

**If you see:** `drwxr-xr-x` → Wrong! Need to fix  
**If you see:** `drwxrwxr-x` → Correct! ✅

## After Fixing

Once you run the fix command:

1. Go to your website: https://linkadi.co.tz
2. Navigate to profile builder
3. Upload a new profile image
4. Click save
5. Image should display immediately! ✅

## Still Not Working?

1. Run the diagnostic: `./diagnose-storage.sh`
2. Check Laravel logs: `tail -f linkadi-web/storage/logs/laravel.log`
3. Verify all subdirectories exist:
   ```bash
   ls -la public_html/storage/
   ```
   Should see: profile-images, company-logos, cover-images, packages, qr-codes

## What Changed in Your Code

I updated `config/filesystems.php`:

**Before:**
```php
'root' => storage_path('app/public'),  // Uses symlink
```

**After:**
```php
'root' => public_path('storage'),  // Direct path (no symlink)
```

This makes Laravel save files directly to `public_html/storage/` instead of trying to use symlinks (which break on cPanel).

## Expected Behavior After Fix

| Action | Result |
|--------|--------|
| Upload profile image | ✅ Saves to `public_html/storage/profile-images/` |
| Upload company logo | ✅ Saves to `public_html/storage/company-logos/` |
| Upload cover image | ✅ Saves to `public_html/storage/cover-images/` |
| View uploaded image | ✅ Displays at `https://linkadi.co.tz/storage/...` |
| Update existing image | ✅ Deletes old, saves new |

## Need Help?

If the quick fix doesn't work:

1. **Check permissions:** Run `ls -ld public_html/storage/`
2. **Test write access:** Run `touch public_html/storage/test.txt`
3. **View diagnostics:** Run `./diagnose-storage.sh`
4. **Check logs:** Run `tail -f linkadi-web/storage/logs/laravel.log`

## Remember

- Use `775` not `755` for web-writable directories
- Clear cache after config changes: `php artisan config:clear`
- Never use `php artisan storage:link` with this setup (not needed!)

---

**Ready?** Open `QUICK_FIX_COMMANDS.txt` and copy the commands! 🚀
