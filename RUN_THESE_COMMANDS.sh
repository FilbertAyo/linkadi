#!/bin/bash

# ============================================================================
# LINKADI STORAGE FIX - COPY AND PASTE THESE COMMANDS
# ============================================================================
# 
# Instructions:
# 1. SSH into your server: ssh linkadic@your-server.com
# 2. Copy and paste this entire script
# 3. Press Enter
# 4. Done!
#
# Or save this as a file and run: bash RUN_THESE_COMMANDS.sh
# ============================================================================

echo "========================================="
echo "LINKADI STORAGE FIX"
echo "========================================="
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
cp -R linkadi-web/storage/app/public/* public_html/storage/ 2>/dev/null || echo "No existing files (this is OK)"
echo "✅ Files copied"

echo ""
echo "Step 3: Setting permissions to 775 (THIS IS THE KEY!)..."
chmod -R 775 public_html/storage
echo "✅ Permissions set to 775"

echo ""
echo "Step 4: Setting ownership..."
chown -R linkadic:linkadic public_html/storage
echo "✅ Ownership set"

echo ""
echo "Step 5: Clearing Laravel cache..."
cd linkadi-web
php artisan config:clear
php artisan cache:clear
cd ~
echo "✅ Cache cleared"

echo ""
echo "========================================="
echo "VERIFICATION"
echo "========================================="
echo ""

echo "Current permissions:"
ls -ld public_html/storage/

echo ""
echo "Testing write access..."
if touch public_html/storage/.test-$$ 2>/dev/null; then
    rm -f public_html/storage/.test-$$
    echo "✅ SUCCESS! Write test passed - uploads will work!"
else
    echo "❌ FAILED! Still can't write - check permissions"
fi

echo ""
echo "========================================="
echo "DONE! 🎉"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. Go to: https://linkadi.co.tz"
echo "2. Navigate to profile builder"
echo "3. Try uploading a profile image"
echo "4. It should work now!"
echo ""
echo "Images will be accessible at:"
echo "  https://linkadi.co.tz/storage/profile-images/filename.jpg"
echo "  https://linkadi.co.tz/storage/company-logos/filename.jpg"
echo "  https://linkadi.co.tz/storage/cover-images/filename.jpg"
echo ""
echo "========================================="
