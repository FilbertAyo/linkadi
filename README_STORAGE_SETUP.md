# 📦 Linkadi Storage Setup - Complete Package

This package contains everything you need to fix image uploads on your cPanel hosting.

## 🎯 What This Fixes

**Problem:** Images don't upload/save when users try to update their profile.

**Cause:** Wrong permissions (755 instead of 775) + cPanel doesn't support symlinks well.

**Solution:** Direct storage path + correct permissions.

## 📚 Documentation Files

### 🚀 Quick Start
1. **START_HERE.md** - Start with this file
2. **QUICK_FIX_COMMANDS.txt** - Copy-paste commands for instant fix

### 🔧 Setup Scripts
3. **setup-storage-complete.sh** - Automated complete setup
4. **fix-storage-permissions.sh** - Fix permissions only
5. **diagnose-storage.sh** - Diagnostic tool to identify issues

### 📖 Guides
6. **STORAGE_FIX_README.md** - Detailed fix guide
7. **UNDERSTANDING_THE_PROBLEM.md** - Visual explanation
8. **DEPLOYMENT_STORAGE_SETUP.md** - Complete deployment guide
9. **DEPLOYMENT_CHECKLIST.md** - Step-by-step checklist

## ⚡ Quick Fix (30 seconds)

SSH into your server and run:

```bash
chmod -R 775 public_html/storage
cd linkadi-web && php artisan config:clear && cd ~
```

Done! Try uploading now.

## 🤖 Automated Setup

Upload `setup-storage-complete.sh` to your server and run:

```bash
chmod +x setup-storage-complete.sh
./setup-storage-complete.sh
```

This does everything automatically.

## 🔍 Diagnostic Tool

If you're having issues, run:

```bash
chmod +x diagnose-storage.sh
./diagnose-storage.sh
```

This will tell you exactly what's wrong.

## 📋 What Changed in Your Code

### File: `config/filesystems.php`

**Before (using symlinks):**
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),  // Requires symlink
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

**After (direct path):**
```php
'public' => [
    'driver' => 'local',
    'root' => public_path('storage'),  // Direct path, no symlink needed
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

### Your Code Doesn't Need Changes!

All your existing upload code continues to work:

```php
// This still works exactly the same
$path = $image->store('profile-images', 'public');
Storage::disk('public')->delete($path);
$url = Storage::disk('public')->url($path);
```

## 🗂️ Server Structure

```
/home/linkadic/
├── linkadi-web/                    # Laravel application
│   ├── config/
│   │   └── filesystems.php         # Updated to use public_path()
│   └── storage/
│       └── app/
│           └── public/             # Old location (not used anymore)
│
└── public_html/                    # Public web root
    └── storage/                    # New location (direct access)
        ├── profile-images/         # User profile images
        ├── company-logos/          # Company logos
        ├── cover-images/           # Profile cover images
        ├── packages/               # Package images (admin)
        └── qr-codes/               # Generated QR codes
```

## 🌐 URL Structure

After setup, images are accessible at:

- Profile images: `https://linkadi.co.tz/storage/profile-images/filename.jpg`
- Company logos: `https://linkadi.co.tz/storage/company-logos/filename.jpg`
- Cover images: `https://linkadi.co.tz/storage/cover-images/filename.jpg`
- Package images: `https://linkadi.co.tz/storage/packages/filename.jpg`
- QR codes: `https://linkadi.co.tz/storage/qr-codes/filename.png`

## ✅ Success Checklist

After running the fix, verify:

- [ ] Can upload profile image
- [ ] Can upload company logo
- [ ] Can upload cover image
- [ ] Images display immediately after upload
- [ ] Can update/replace existing images
- [ ] Old images are deleted when replaced
- [ ] Images are accessible via URL

## 🐛 Common Issues

### Issue 1: Uploads still don't work

**Check permissions:**
```bash
ls -ld public_html/storage/
```

Should show: `drwxrwxr-x` (775)

**Fix:**
```bash
chmod -R 775 public_html/storage
```

### Issue 2: Some subdirectories missing

**Create them:**
```bash
mkdir -p public_html/storage/{profile-images,company-logos,cover-images,packages,qr-codes}
```

### Issue 3: Cache not cleared

**Clear it:**
```bash
cd linkadi-web
php artisan config:clear
php artisan cache:clear
```

## 🔐 Security Notes

### Why 775 is Safe

- `7` (owner): Read, write, execute ✅
- `7` (group): Read, write, execute ✅ (web server needs this)
- `5` (others): Read, execute only ✅ (public can view, not modify)

This is the standard permission for web-writable directories.

### Why Not 777?

`777` gives EVERYONE write access (security risk). `775` limits write access to owner and group only.

## 📊 File Upload Limits

Check your PHP settings:

```bash
php -i | grep upload_max_filesize  # Should be >= 10M
php -i | grep post_max_size        # Should be >= 10M
```

If too low, update `php.ini` or `.user.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20
```

## 🔄 Deployment Workflow

### Initial Setup
1. Update code locally
2. Commit and push to git
3. Pull on server
4. Run setup script
5. Test uploads

### Future Deployments
1. Pull latest code
2. Run `php artisan config:clear`
3. No storage changes needed!

## 💾 Backup Strategy

### Backup Uploaded Files
```bash
tar -czf storage-backup-$(date +%Y%m%d).tar.gz public_html/storage/
```

### Restore from Backup
```bash
tar -xzf storage-backup-20260118.tar.gz -C public_html/
```

## 🆘 Getting Help

### Check Logs
```bash
# Laravel logs
tail -f linkadi-web/storage/logs/laravel.log

# PHP error logs (location varies)
tail -f /usr/local/apache/logs/error_log
```

### Run Diagnostics
```bash
./diagnose-storage.sh
```

### Contact Support

If nothing works:
1. Save output from `diagnose-storage.sh`
2. Save recent Laravel logs
3. Contact your hosting provider with this information

## 📝 Important Notes

### DO:
✅ Use `chmod -R 775` for storage directories  
✅ Clear cache after config changes  
✅ Test all upload features after deployment  
✅ Backup uploaded files regularly  

### DON'T:
❌ Use `php artisan storage:link` (not needed with this setup)  
❌ Use `chmod 755` (web server can't write)  
❌ Use `chmod 777` (security risk)  
❌ Forget to clear cache after changes  

## 🎓 Understanding the Solution

### Old Method (Symlinks)
```
storage/app/public/ → symlink → public/storage/
```
**Problem:** Symlinks break on cPanel, hard to troubleshoot

### New Method (Direct Path)
```
public_html/storage/ (direct access, no symlink)
```
**Benefit:** Stable, reliable, cPanel-friendly

## 📞 Support

If you need help:

1. **Read:** START_HERE.md
2. **Try:** Quick fix commands
3. **Run:** diagnose-storage.sh
4. **Check:** Laravel logs
5. **Contact:** Hosting support (if server-level issue)

## 🏆 Success!

Once everything works:

- Images upload instantly ✅
- Images display correctly ✅
- Updates work smoothly ✅
- No more permission errors ✅

**Enjoy your working image uploads!** 🎉

---

**Package Version:** 1.0  
**Last Updated:** January 2026  
**Tested On:** cPanel shared hosting  
**Laravel Version:** 11.x  
