#!/bin/bash

# Storage Diagnostics Script
# Run this to identify storage permission issues

echo "========================================="
echo "STORAGE DIAGNOSTICS"
echo "========================================="
echo ""

cd ~

echo "1. Checking if storage directory exists..."
if [ -d "public_html/storage" ]; then
    echo "   ✅ public_html/storage exists"
else
    echo "   ❌ public_html/storage does NOT exist"
    echo "   Run: mkdir -p public_html/storage"
    exit 1
fi

echo ""
echo "2. Checking permissions..."
PERMS=$(stat -c "%a" public_html/storage 2>/dev/null || stat -f "%A" public_html/storage 2>/dev/null)
echo "   Current permissions: $PERMS"

if [ "$PERMS" = "775" ] || [ "$PERMS" = "777" ]; then
    echo "   ✅ Permissions are correct ($PERMS)"
elif [ "$PERMS" = "755" ]; then
    echo "   ⚠️  Permissions are 755 - web server CANNOT write!"
    echo "   Fix: chmod -R 775 public_html/storage"
else
    echo "   ⚠️  Unusual permissions: $PERMS"
fi

echo ""
echo "3. Checking ownership..."
ls -ld public_html/storage/ | awk '{print "   Owner: " $3 "   Group: " $4}'

echo ""
echo "4. Checking subdirectories..."
for dir in profile-images company-logos cover-images packages qr-codes; do
    if [ -d "public_html/storage/$dir" ]; then
        echo "   ✅ $dir exists"
    else
        echo "   ❌ $dir missing - Run: mkdir -p public_html/storage/$dir"
    fi
done

echo ""
echo "5. Testing write access..."
TEST_FILE="public_html/storage/.write-test-$$"
if touch "$TEST_FILE" 2>/dev/null; then
    echo "   ✅ Can create files (write access OK)"
    rm -f "$TEST_FILE"
else
    echo "   ❌ CANNOT create files (PERMISSION DENIED)"
    echo "   This is why uploads fail!"
    echo ""
    echo "   Fix with: chmod -R 775 public_html/storage"
fi

echo ""
echo "6. Checking Laravel config..."
if [ -f "linkadi-web/config/filesystems.php" ]; then
    if grep -q "public_path('storage')" linkadi-web/config/filesystems.php; then
        echo "   ✅ Laravel config is correct (using public_path)"
    else
        echo "   ⚠️  Laravel config may be wrong"
        echo "   Check: linkadi-web/config/filesystems.php"
    fi
else
    echo "   ❌ Cannot find linkadi-web/config/filesystems.php"
fi

echo ""
echo "7. Checking for uploaded files..."
FILE_COUNT=$(find public_html/storage -type f 2>/dev/null | wc -l)
echo "   Found $FILE_COUNT files in storage"

echo ""
echo "8. Detailed permissions list..."
ls -lah public_html/storage/ | head -20

echo ""
echo "========================================="
echo "DIAGNOSIS COMPLETE"
echo "========================================="
echo ""
echo "If write test FAILED, run:"
echo "  chmod -R 775 public_html/storage"
echo "  cd linkadi-web"
echo "  php artisan config:clear"
echo "========================================="
