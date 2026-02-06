# Deployment Script Guide

## Overview
The `deploy.sh` script automates the deployment process for your Laravel application on shared hosting.

## Prerequisites
- SSH access to your shared hosting account
- Git repository configured
- PHP and Composer available in PATH
- Project directory: `~/linkadi-web`
- Public HTML directory: `~/public_html`

## Usage

### First Time Setup
1. Upload the `deploy.sh` script to your home directory or project root
2. Make it executable:
   ```bash
   chmod +x deploy.sh
   ```

### Running the Deployment
```bash
./deploy.sh
```

Or if the script is in a different location:
```bash
bash ~/deploy.sh
```

## What the Script Does

1. **Pulls Latest Code**: Fetches the latest changes from the `main` branch
2. **Updates Dependencies**: Runs `composer install` with production optimizations
3. **Clears Caches**: Removes all Laravel caches (application, config, route, view)
4. **Optimizes Application**: Caches configuration, routes, and views for better performance
5. **Syncs Images**: Copies `public/images` to `public_html/images`
6. **Sets Permissions**: Ensures proper file permissions

## Customization

### Change Project Directory
Edit the `PROJECT_DIR` variable in the script:
```bash
PROJECT_DIR="$HOME/linkadi-web"  # Change this to your project path
```

### Change Branch
Edit the `BRANCH` variable:
```bash
BRANCH="main"  # Change to "master" or any other branch
```

### Enable Database Migrations
Uncomment the migration line in the script:
```bash
# print_message "  - Running database migrations..."
# php artisan migrate --force || print_warning "migrate failed"
```

Change to:
```bash
print_message "  - Running database migrations..."
php artisan migrate --force || print_warning "migrate failed"
```

## Troubleshooting

### Permission Denied
If you get permission errors:
```bash
chmod +x deploy.sh
```

### Git Pull Fails
- Ensure you're in the correct directory
- Check that git is configured correctly
- Verify you have access to the repository

### Composer Not Found
- Check if Composer is installed: `which composer`
- You may need to use the full path: `/usr/local/bin/composer` or `php composer.phar`

### Artisan Commands Fail
- Ensure you're in the Laravel project root
- Check PHP version compatibility
- Verify `.env` file exists and is configured correctly

### Images Not Copying
- Check that `public/images` directory exists
- Verify `public_html` directory exists and is writable
- Check disk space availability

## Manual Steps (if needed)

If the script fails at any step, you can run commands manually:

```bash
cd ~/linkadi-web
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
cp -r public/images/* ~/public_html/images/
```

## Notes

- The script uses `set -e` which exits on any error
- All commands have error handling to continue even if one step fails
- Images are synced using `rsync` if available, otherwise `cp` is used
- The script preserves existing files in `public_html/images` (unless using rsync with --delete)
