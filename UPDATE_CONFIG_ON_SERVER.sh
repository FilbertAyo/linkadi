#!/bin/bash

# This script updates the config file directly on your server

echo "========================================="
echo "UPDATING CONFIG FILE ON SERVER"
echo "========================================="
echo ""

cd ~/linkadi-web/config

# Backup current config
echo "Creating backup..."
cp filesystems.php filesystems.php.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ Backup created"

echo ""
echo "Updating config file..."

# Use sed to replace the line
sed -i.tmp "s|'root' => storage_path('app/public')|'root' => public_path('storage')|g" filesystems.php

# Remove temp file
rm -f filesystems.php.tmp

echo "✅ Config updated"

echo ""
echo "Verifying change..."
grep "public_path('storage')" filesystems.php

if grep -q "public_path('storage')" filesystems.php; then
    echo "✅ Config file is now correct!"
else
    echo "❌ Update failed - restoring backup"
    cp filesystems.php.backup.* filesystems.php
    exit 1
fi

echo ""
echo "Removing old storage directory..."
if [ -d ~/linkadi-web/public/storage ]; then
    # Move files first
    if [ -d ~/public_html/storage ]; then
        cp -R ~/linkadi-web/public/storage/* ~/public_html/storage/ 2>/dev/null || true
    fi
    rm -rf ~/linkadi-web/public/storage
    echo "✅ Old directory removed"
else
    echo "✅ Old directory doesn't exist (good)"
fi

echo ""
echo "Setting permissions..."
chmod -R 775 ~/public_html/storage
echo "✅ Permissions set"

echo ""
echo "Clearing caches..."
cd ~/linkadi-web
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear 2>/dev/null || true
echo "✅ Caches cleared"

echo ""
echo "========================================="
echo "DONE! ✅"
echo "========================================="
echo ""
echo "Now try uploading an image."
echo "It should save to: ~/public_html/storage/"
echo ""
