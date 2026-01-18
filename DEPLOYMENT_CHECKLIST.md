# 📋 Storage Setup Deployment Checklist

Use this checklist when deploying to your cPanel server.

## Pre-Deployment (On Local Machine)

- [x] Updated `config/filesystems.php` to use `public_path('storage')`
- [ ] Committed changes to git
- [ ] Pushed to repository

## Deployment Steps (On Server)

### 1. Upload Files
- [ ] Pull latest code to `linkadi-web/`
- [ ] Upload setup scripts (optional but helpful):
  - [ ] `setup-storage-complete.sh`
  - [ ] `diagnose-storage.sh`
  - [ ] `fix-storage-permissions.sh`

### 2. Create Storage Structure
```bash
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes
```
- [ ] Directories created

### 3. Copy Existing Files (if any)
```bash
cp -R linkadi-web/storage/app/public/* public_html/storage/
```
- [ ] Files copied (or N/A if fresh install)

### 4. Set Permissions (CRITICAL!)
```bash
chmod -R 775 public_html/storage
```
- [ ] Permissions set to 775 (not 755!)

### 5. Set Ownership
```bash
chown -R linkadic:linkadic public_html/storage
```
- [ ] Ownership set

### 6. Clear Laravel Cache
```bash
cd linkadi-web
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```
- [ ] Config cache cleared
- [ ] Application cache cleared
- [ ] View cache cleared

### 7. Verify Setup

#### Check Permissions
```bash
ls -ld public_html/storage/
```
- [ ] Shows `drwxrwxr-x` (775)

#### Check Subdirectories
```bash
ls -la public_html/storage/
```
- [ ] `profile-images/` exists
- [ ] `company-logos/` exists
- [ ] `cover-images/` exists
- [ ] `packages/` exists
- [ ] `qr-codes/` exists

#### Test Write Access
```bash
touch public_html/storage/test.txt && rm public_html/storage/test.txt
```
- [ ] Write test passed

### 8. Test on Website

- [ ] Navigate to profile builder
- [ ] Upload profile image
- [ ] Click save
- [ ] Image displays correctly
- [ ] Update image (upload different one)
- [ ] New image displays correctly
- [ ] Old image was deleted

### 9. Test All Upload Features

- [ ] Profile image upload works
- [ ] Company logo upload works
- [ ] Cover image upload works
- [ ] Package image upload works (admin)
- [ ] QR code generation works

### 10. Verify URLs

Check that images are accessible:
- [ ] `https://linkadi.co.tz/storage/profile-images/[filename]`
- [ ] `https://linkadi.co.tz/storage/company-logos/[filename]`
- [ ] `https://linkadi.co.tz/storage/cover-images/[filename]`

## Troubleshooting Checklist

If uploads don't work:

- [ ] Permissions are 775 (not 755)
- [ ] Subdirectories exist
- [ ] Web server can write to directory (test with `touch`)
- [ ] Laravel cache is cleared
- [ ] `APP_URL` is set correctly in `.env`
- [ ] No disk space issues (`df -h`)
- [ ] PHP upload limits are sufficient:
  - [ ] `upload_max_filesize` >= 10M
  - [ ] `post_max_size` >= 10M
  - [ ] `max_file_uploads` >= 20

## Rollback Plan (If Something Goes Wrong)

### Quick Rollback
```bash
# Restore old symlink method (if needed)
rm -rf public_html/storage
ln -s ~/linkadi-web/storage/app/public ~/public_html/storage

# Revert config
cd linkadi-web
git checkout config/filesystems.php
php artisan config:clear
```

## Post-Deployment Monitoring

### Check Logs Regularly
```bash
tail -f linkadi-web/storage/logs/laravel.log
```
- [ ] No permission errors
- [ ] No storage errors

### Monitor Disk Space
```bash
du -sh public_html/storage/
df -h
```
- [ ] Adequate space available

### Backup Strategy
```bash
# Weekly backup of uploaded files
tar -czf storage-backup-$(date +%Y%m%d).tar.gz public_html/storage/
```
- [ ] Backup script created
- [ ] Backup schedule set

## Success Criteria

✅ All checkboxes above are checked  
✅ Images upload successfully  
✅ Images display correctly  
✅ Old images are deleted when updated  
✅ No permission errors in logs  
✅ All upload features work  

## Notes

**Important:**
- Never use `php artisan storage:link` with this setup
- Always use 775 (not 755) for storage directories
- Clear cache after any config changes
- Test all upload features after deployment

**Maintenance:**
- Monitor disk space monthly
- Backup uploaded files weekly
- Check logs for errors regularly

---

**Deployment Date:** _______________  
**Deployed By:** _______________  
**All Tests Passed:** ☐ Yes ☐ No  
**Notes:** _____________________________________
