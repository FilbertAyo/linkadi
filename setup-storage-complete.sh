#!/bin/bash

# Complete Storage Setup Script
# This script does EVERYTHING needed to fix storage on cPanel

set -e  # Exit on error

echo "========================================="
echo "LINKADI STORAGE SETUP"
echo "========================================="
echo ""
echo "This script will:"
echo "  1. Create storage directories"
echo "  2. Copy existing files"
echo "  3. Set correct permissions (775)"
echo "  4. Clear Laravel cache"
echo ""
read -p "Press Enter to continue or Ctrl+C to cancel..."
echo ""

# Navigate to home directory
cd ~

echo "Step 1: Creating directory structure..."
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes
echo "✅ Directories created"

echo ""
echo "Step 2: Copying existing files (if any)..."
if [ -d "linkadi-web/storage/app/public" ]; then
    cp -R linkadi-web/storage/app/public/* public_html/storage/ 2>/dev/null || echo "No existing files to copy (this is OK)"
    echo "✅ Files copied"
else
    echo "⚠️  No existing files found (this is OK for fresh install)"
fi

echo ""
echo "Step 3: Setting permissions to 775..."
chmod -R 775 public_html/storage
echo "✅ Permissions set"

echo ""
echo "Step 4: Setting ownership..."
chown -R linkadic:linkadic public_html/storage
echo "✅ Ownership set"

echo ""
echo "Step 5: Clearing Laravel cache..."
cd linkadi-web
php artisan config:clear
php artisan cache:clear
php artisan view:clear
cd ~
echo "✅ Cache cleared"

echo ""
echo "========================================="
echo "VERIFICATION"
echo "========================================="

echo ""
echo "Directory structure:"
ls -la public_html/storage/

echo ""
echo "Testing write access..."
TEST_FILE="public_html/storage/.test-$$"
if touch "$TEST_FILE" 2>/dev/null; then
    echo "✅ Write test PASSED - uploads will work!"
    rm -f "$TEST_FILE"
else
    echo "❌ Write test FAILED - there's still a problem"
    echo "You may need to contact your hosting provider"
    exit 1
fi

echo ""
echo "========================================="
echo "SUCCESS! 🎉"
echo "========================================="
echo ""
echo "Your storage is now configured correctly!"
echo ""
echo "Next steps:"
echo "  1. Go to your website"
echo "  2. Try uploading a profile image"
echo "  3. It should now work!"
echo ""
echo "Images will be accessible at:"
echo "  https://linkadi.co.tz/storage/profile-images/filename.jpg"
echo "  https://linkadi.co.tz/storage/company-logos/filename.jpg"
echo "  https://linkadi.co.tz/storage/cover-images/filename.jpg"
echo ""
echo "========================================="
