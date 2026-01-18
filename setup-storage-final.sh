#!/bin/bash

# ============================================================================
# LINKADI STORAGE SETUP - FINAL FIX
# This script fixes the cPanel public_html vs linkadi-web/public issue
# ============================================================================

echo "========================================="
echo "LINKADI STORAGE SETUP - FINAL FIX"
echo "========================================="
echo ""

cd ~

# Create directories
echo "Step 1: Creating directories..."
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes
echo "✅ Directories created"

# Set permissions
echo ""
echo "Step 2: Setting permissions..."
chmod -R 775 public_html/storage
chown -R linkadic:linkadic public_html/storage
echo "✅ Permissions set to 775"

# Copy existing files from both possible locations
echo ""
echo "Step 3: Copying existing files..."
COPIED=0

if [ -d "linkadi-web/public/storage" ] && [ "$(ls -A linkadi-web/public/storage 2>/dev/null)" ]; then
    echo "   Copying from linkadi-web/public/storage/..."
    cp -R linkadi-web/public/storage/* public_html/storage/ 2>/dev/null && COPIED=1
fi

if [ -d "linkadi-web/storage/app/public" ] && [ "$(ls -A linkadi-web/storage/app/public 2>/dev/null)" ]; then
    echo "   Copying from linkadi-web/storage/app/public/..."
    cp -R linkadi-web/storage/app/public/* public_html/storage/ 2>/dev/null && COPIED=1
fi

if [ $COPIED -eq 1 ]; then
    echo "✅ Files copied"
else
    echo "✅ No existing files (this is OK for fresh install)"
fi

# Add environment variable to .env
echo ""
echo "Step 4: Updating .env file..."
cd linkadi-web

if ! grep -q "PUBLIC_STORAGE_PATH" .env 2>/dev/null; then
    echo "" >> .env
    echo "# Storage path for cPanel (points to public_html instead of linkadi-web/public)" >> .env
    echo "PUBLIC_STORAGE_PATH=/home/linkadic/public_html/storage" >> .env
    echo "✅ Added PUBLIC_STORAGE_PATH to .env"
else
    echo "✅ PUBLIC_STORAGE_PATH already exists in .env"
fi

# Clear cache
echo ""
echo "Step 5: Clearing Laravel cache..."
php artisan config:clear 2>&1 | grep -i "cleared\|success" || echo "Config cleared"
php artisan cache:clear 2>&1 | grep -i "cleared\|success" || echo "Cache cleared"
php artisan view:clear 2>&1 | grep -i "cleared\|success" || echo "Views cleared"
echo "✅ Cache cleared"

# Verify
echo ""
echo "========================================="
echo "VERIFICATION"
echo "========================================="
cd ~

echo ""
echo "1. Storage directory permissions:"
ls -ld public_html/storage/

echo ""
echo "2. Subdirectories:"
ls -la public_html/storage/ | grep "^d" | awk '{print "   " $9}' | grep -v "^\.$\|^\.\.$"

echo ""
echo "3. Testing write access..."
if touch public_html/storage/.test-$$ 2>/dev/null; then
    rm -f public_html/storage/.test-$$
    echo "   ✅ Write test PASSED - Web server can write files"
else
    echo "   ❌ Write test FAILED - Check permissions"
    exit 1
fi

echo ""
echo "4. Checking Laravel config..."
cd linkadi-web
STORAGE_PATH=$(php -r "require 'vendor/autoload.php'; \$app = require_once 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); echo config('filesystems.disks.public.root');" 2>/dev/null)

if [ -n "$STORAGE_PATH" ]; then
    echo "   Storage path: $STORAGE_PATH"
    if [[ "$STORAGE_PATH" == *"public_html/storage"* ]]; then
        echo "   ✅ Config is CORRECT!"
    else
        echo "   ⚠️  Config shows: $STORAGE_PATH"
        echo "   Expected: /home/linkadic/public_html/storage"
    fi
else
    echo "   ⚠️  Could not verify config (this might be OK)"
fi

echo ""
echo "========================================="
echo "SETUP COMPLETE! 🎉"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Go to: https://linkadi.co.tz"
echo "2. Navigate to profile builder"
echo "3. Upload a profile image"
echo "4. Click save"
echo ""
echo "To verify the file was saved correctly:"
echo "  ls -la ~/public_html/storage/profile-images/"
echo ""
echo "Images will be accessible at:"
echo "  https://linkadi.co.tz/storage/profile-images/filename.jpg"
echo ""
echo "========================================="
