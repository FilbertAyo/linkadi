#!/bin/bash

# Fix Missing Company Logo and Cover Images
# This script ensures all directories exist and moves any misplaced files

echo "========================================="
echo "FIX MISSING IMAGES"
echo "========================================="
echo ""

cd ~

echo "Step 1: Ensuring all directories exist..."
mkdir -p public_html/storage/profile-images
mkdir -p public_html/storage/company-logos
mkdir -p public_html/storage/cover-images
mkdir -p public_html/storage/packages
mkdir -p public_html/storage/qr-codes
echo "✅ Directories created"

echo ""
echo "Step 2: Setting permissions..."
chmod -R 775 public_html/storage
echo "✅ Permissions set"

echo ""
echo "Step 3: Checking for misplaced files..."

# Check if files are in wrong location
if [ -d "linkadi-web/public/storage" ]; then
    FILE_COUNT=$(find linkadi-web/public/storage -type f 2>/dev/null | wc -l)
    if [ $FILE_COUNT -gt 0 ]; then
        echo "  Found $FILE_COUNT files in wrong location (linkadi-web/public/storage)"
        echo "  Copying to correct location..."
        cp -R linkadi-web/public/storage/* public_html/storage/ 2>/dev/null
        echo "✅ Files copied"
    else
        echo "  No files in wrong location"
    fi
else
    echo "  No wrong location directory found"
fi

# Also check storage/app/public
if [ -d "linkadi-web/storage/app/public" ]; then
    FILE_COUNT=$(find linkadi-web/storage/app/public -type f 2>/dev/null | wc -l)
    if [ $FILE_COUNT -gt 0 ]; then
        echo "  Found $FILE_COUNT files in linkadi-web/storage/app/public"
        echo "  Copying to correct location..."
        cp -R linkadi-web/storage/app/public/* public_html/storage/ 2>/dev/null
        echo "✅ Files copied"
    fi
fi

echo ""
echo "Step 4: Verifying file counts..."
echo "-----------------------------------"
for dir in profile-images company-logos cover-images packages qr-codes; do
    if [ -d "public_html/storage/$dir" ]; then
        count=$(find public_html/storage/$dir -type f 2>/dev/null | wc -l)
        perms=$(stat -c "%a" public_html/storage/$dir 2>/dev/null || stat -f "%A" public_html/storage/$dir 2>/dev/null)
        echo "  $dir: $count files (permissions: $perms)"
    else
        echo "  $dir: MISSING!"
    fi
done

echo ""
echo "Step 5: Clearing Laravel cache..."
cd linkadi-web
php artisan config:clear 2>&1 | grep -i "cleared\|success" || echo "Config cleared"
php artisan cache:clear 2>&1 | grep -i "cleared\|success" || echo "Cache cleared"
echo "✅ Cache cleared"

echo ""
echo "Step 6: Testing file access..."
cd ~
if touch public_html/storage/company-logos/.test-$$ 2>/dev/null; then
    rm -f public_html/storage/company-logos/.test-$$
    echo "✅ Can write to company-logos"
else
    echo "❌ Cannot write to company-logos"
fi

if touch public_html/storage/cover-images/.test-$$ 2>/dev/null; then
    rm -f public_html/storage/cover-images/.test-$$
    echo "✅ Can write to cover-images"
else
    echo "❌ Cannot write to cover-images"
fi

echo ""
echo "========================================="
echo "VERIFICATION"
echo "========================================="
echo ""
echo "Files in each directory:"
echo ""

for dir in profile-images company-logos cover-images packages qr-codes; do
    echo "$dir:"
    if [ -d "public_html/storage/$dir" ]; then
        ls -lh public_html/storage/$dir/ 2>/dev/null | grep -v "^total" | grep -v "^d" | head -3 | awk '{print "  " $9 " (" $5 ")"}'
        count=$(find public_html/storage/$dir -type f 2>/dev/null | wc -l)
        if [ $count -gt 3 ]; then
            echo "  ... and $(($count - 3)) more files"
        fi
    else
        echo "  Directory missing!"
    fi
    echo ""
done

echo "========================================="
echo "DONE!"
echo "========================================="
echo ""
echo "Next steps:"
echo "1. If you see files in company-logos and cover-images above, try accessing your profile"
echo "2. If directories are empty, try uploading NEW company logo and cover image"
echo "3. Hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)"
echo ""
echo "To test if images are accessible, try these URLs in your browser:"
echo "(Replace [filename] with actual filename from above)"
echo ""
echo "  https://linkadi.co.tz/storage/company-logos/[filename]"
echo "  https://linkadi.co.tz/storage/cover-images/[filename]"
echo ""
echo "========================================="
