#!/bin/bash

# EMERGENCY FIX - Run this on your server NOW!

echo "========================================="
echo "DIAGNOSING THE ISSUE"
echo "========================================="
echo ""

cd ~

echo "1. Checking if config file is updated on server..."
if grep -q "public_path('storage')" linkadi-web/config/filesystems.php; then
    echo "✅ Config file is updated"
else
    echo "❌ Config file NOT updated on server!"
    echo "You need to upload the new config/filesystems.php to your server"
    exit 1
fi

echo ""
echo "2. Checking symlink structure..."
if [ -L "public_html" ]; then
    echo "public_html is a symlink to:"
    ls -la public_html | head -1
fi
if [ -L "linkadi-web/public" ]; then
    echo "linkadi-web/public is a symlink to:"
    ls -la linkadi-web/public | head -1
fi

echo ""
echo "3. Checking where files are actually saving..."
echo "Files in linkadi-web/public/storage:"
ls -la linkadi-web/public/storage/ 2>/dev/null || echo "Directory doesn't exist"

echo ""
echo "Files in public_html/storage:"
ls -la public_html/storage/ 2>/dev/null || echo "Directory doesn't exist"

echo ""
echo "========================================="
echo "APPLYING FIX"
echo "========================================="
echo ""

# Check if linkadi-web/public/storage exists and has files
if [ -d "linkadi-web/public/storage" ]; then
    echo "Found files in linkadi-web/public/storage/"
    echo "Moving them to public_html/storage/..."
    
    # Create directories in public_html/storage if they don't exist
    mkdir -p public_html/storage/profile-images
    mkdir -p public_html/storage/company-logos
    mkdir -p public_html/storage/cover-images
    mkdir -p public_html/storage/packages
    mkdir -p public_html/storage/qr-codes
    
    # Copy files
    cp -R linkadi-web/public/storage/* public_html/storage/ 2>/dev/null || true
    
    # Remove the old directory
    rm -rf linkadi-web/public/storage
    
    echo "✅ Files moved"
fi

# Ensure public_html/storage has correct permissions
chmod -R 775 public_html/storage

# Clear ALL caches
echo ""
echo "Clearing all caches..."
cd linkadi-web
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear 2>/dev/null || true
cd ~

echo ""
echo "========================================="
echo "VERIFICATION"
echo "========================================="
echo ""

echo "public_html/storage permissions:"
ls -ld public_html/storage/

echo ""
echo "public_html/storage contents:"
ls -la public_html/storage/ | head -10

echo ""
echo "linkadi-web/public/storage status:"
if [ -d "linkadi-web/public/storage" ]; then
    echo "❌ Still exists (should be removed)"
else
    echo "✅ Removed (correct)"
fi

echo ""
echo "========================================="
echo "DONE!"
echo "========================================="
echo ""
echo "Now try uploading an image again."
echo "It should save to: public_html/storage/"
echo ""
