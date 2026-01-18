# cPanel Storage Setup Guide (No Symlinks)

This guide shows how to set up Laravel storage on cPanel shared hosting without symlinks.

## Server Structure
```
/home/your-username/
├── linkadi-web/          # Laravel application
└── public_html/          # Public web root
    └── storage/          # Public storage (we'll create this)
```

## Setup Steps

### 1. Create Storage Directory in public_html

SSH into your cPanel or use File Manager:

```bash
cd /home/your-username
mkdir -p public_html/storage
```

### 2. Copy Existing Files (if any)

If you have existing files in `linkadi-web/storage/app/public/`, copy them:

```bash
cp -R linkadi-web/storage/app/public/* public_html/storage/
```

**Note:** This copies:
- `profile-images/`
- `company-logos/`
- `cover-images/`
- `packages/`
- `qr-codes/`

### 3. Set Proper Permissions

```bash
chmod -R 755 public_html/storage
chown -R your-username:your-username public_html/storage
```

Or if using cPanel File Manager:
- Right-click `storage` folder → Permissions
- Set to `755` (drwxr-xr-x)

### 4. Clear Laravel Config Cache

```bash
cd linkadi-web
php artisan config:clear
php artisan cache:clear
```

### 5. Verify Configuration

The `config/filesystems.php` has been updated to:

```php
'public' => [
    'driver' => 'local',
    'root' => public_path('storage'),  // Points to public_html/storage
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
    'throw' => false,
    'report' => false,
],
```

### 6. Test Image Upload

Upload a profile image through your application. The file should be saved to:

```
/home/your-username/public_html/storage/profile-images/filename.jpg
```

And accessible at:

```
https://linkadi.co.tz/storage/profile-images/filename.jpg
```

## How Files Are Stored

Your application uses these storage paths:

| Feature | Storage Path | Public URL |
|---------|-------------|------------|
| Profile Images | `storage/profile-images/` | `/storage/profile-images/filename.jpg` |
| Company Logos | `storage/company-logos/` | `/storage/company-logos/filename.jpg` |
| Cover Images | `storage/cover-images/` | `/storage/cover-images/filename.jpg` |
| Package Images | `storage/packages/` | `/storage/packages/filename.jpg` |
| QR Codes | `storage/qr-codes/` | `/storage/qr-codes/filename.png` |

## Code Examples

All your existing code will work without changes:

```php
// Upload profile image
$path = $this->profile_image->store('profile-images', 'public');
// Saves to: public_html/storage/profile-images/xxx.jpg

// Delete image
Storage::disk('public')->delete($profile->profile_image);

// Get URL
$url = Storage::disk('public')->url($profile->profile_image);
// Returns: /storage/profile-images/xxx.jpg
```

## Troubleshooting

### Images not showing?

1. **Check permissions:**
   ```bash
   ls -la public_html/storage/
   # Should show: drwxr-xr-x
   ```

2. **Verify files exist:**
   ```bash
   ls -la public_html/storage/profile-images/
   ```

3. **Check APP_URL in .env:**
   ```
   APP_URL=https://linkadi.co.tz
   ```

4. **Clear config:**
   ```bash
   php artisan config:clear
   ```

### Getting 404 errors?

Make sure the directory structure exists:
```bash
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes
```

## Why This Works

✅ **No symlinks** - cPanel doesn't need to support them  
✅ **Direct file access** - Files are directly in public_html  
✅ **Stable** - Won't break on cPanel updates  
✅ **Simple** - Easy to troubleshoot  
✅ **Compatible** - Works with all shared hosting  

## Important Notes

- **Never run** `php artisan storage:link` on production (not needed with this method)
- Files go directly to `public_html/storage/` instead of `linkadi-web/storage/app/public/`
- Make sure `public_html/storage/` has write permissions (755 or 775)
- When deploying code updates, run `php artisan config:clear` to refresh config

## Backup Strategy

When backing up files, remember to backup:
```bash
# Backup uploaded files
tar -czf storage-backup-$(date +%Y%m%d).tar.gz public_html/storage/

# Backup database
mysqldump -u username -p database_name > backup-$(date +%Y%m%d).sql
```

## Migration from Old Setup

If you were using symlinks before:

1. Delete the old symlink:
   ```bash
   rm public_html/storage
   ```

2. Create real directory:
   ```bash
   mkdir public_html/storage
   ```

3. Copy files:
   ```bash
   cp -R linkadi-web/storage/app/public/* public_html/storage/
   ```

4. Clear cache:
   ```bash
   cd linkadi-web
   php artisan config:clear
   ```

---

**You're all set!** Images will now save and display correctly on your cPanel hosting.
