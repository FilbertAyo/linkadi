#!/bin/bash

# Fix Storage Permissions for cPanel
# Run this script on your server to fix write permissions

echo "==================================="
echo "Fixing Storage Permissions"
echo "==================================="

# Navigate to home directory
cd ~

# Create storage directory if it doesn't exist
if [ ! -d "public_html/storage" ]; then
    echo "Creating public_html/storage directory..."
    mkdir -p public_html/storage
fi

# Create subdirectories
echo "Creating subdirectories..."
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes

# Set permissions - 775 allows web server to write
echo "Setting permissions to 775 (rwxrwxr-x)..."
chmod -R 775 public_html/storage

# Set ownership
echo "Setting ownership..."
chown -R linkadic:linkadic public_html/storage

# Check if web server group exists and add it
# Common web server groups: apache, www-data, nobody
if getent group apache > /dev/null 2>&1; then
    echo "Setting group to apache..."
    chgrp -R apache public_html/storage
elif getent group www-data > /dev/null 2>&1; then
    echo "Setting group to www-data..."
    chgrp -R www-data public_html/storage
elif getent group nobody > /dev/null 2>&1; then
    echo "Setting group to nobody..."
    chgrp -R nobody public_html/storage
fi

# Verify permissions
echo ""
echo "==================================="
echo "Current Permissions:"
echo "==================================="
ls -la public_html/storage/

echo ""
echo "==================================="
echo "Testing write permissions..."
echo "==================================="
touch public_html/storage/test-write.txt 2>/dev/null
if [ -f "public_html/storage/test-write.txt" ]; then
    echo "✅ Write test SUCCESSFUL"
    rm public_html/storage/test-write.txt
else
    echo "❌ Write test FAILED - You may need to contact your hosting provider"
fi

echo ""
echo "==================================="
echo "Done! Now run:"
echo "cd linkadi-web"
echo "php artisan config:clear"
echo "php artisan cache:clear"
echo "==================================="
