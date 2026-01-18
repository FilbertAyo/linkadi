#!/bin/bash

# Check Uploaded Files Diagnostic Script
# Run this on your server to see what files were actually uploaded

echo "========================================="
echo "CHECKING UPLOADED FILES"
echo "========================================="
echo ""

cd ~

echo "1. Files in public_html/storage/:"
echo "-----------------------------------"
find public_html/storage/ -type f -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" 2>/dev/null | while read file; do
    echo "  $file"
    ls -lh "$file" | awk '{print "    Size: " $5 "  Modified: " $6 " " $7 " " $8}'
done

echo ""
echo "2. Count by directory:"
echo "-----------------------------------"
for dir in profile-images company-logos cover-images packages qr-codes; do
    count=$(find public_html/storage/$dir -type f 2>/dev/null | wc -l)
    echo "  $dir: $count files"
done

echo ""
echo "3. Recent uploads (last 10):"
echo "-----------------------------------"
find public_html/storage/ -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" \) -mmin -60 2>/dev/null | head -10 | while read file; do
    echo "  $file"
    ls -lh "$file" | awk '{print "    Size: " $5 "  Modified: " $6 " " $7 " " $8}'
done

echo ""
echo "4. Checking permissions:"
echo "-----------------------------------"
for dir in profile-images company-logos cover-images packages qr-codes; do
    if [ -d "public_html/storage/$dir" ]; then
        perms=$(stat -c "%a" public_html/storage/$dir 2>/dev/null || stat -f "%A" public_html/storage/$dir 2>/dev/null)
        echo "  $dir: $perms"
    else
        echo "  $dir: MISSING!"
    fi
done

echo ""
echo "5. Sample file URLs (test these in browser):"
echo "-----------------------------------"
find public_html/storage/ -type f \( -name "*.jpg" -o -name "*.jpeg" -o -name "*.png" -o -name "*.gif" \) 2>/dev/null | head -5 | while read file; do
    # Remove public_html/ prefix to get web path
    webpath=$(echo "$file" | sed 's|public_html/||')
    echo "  https://linkadi.co.tz/$webpath"
done

echo ""
echo "6. Check if files are in wrong location:"
echo "-----------------------------------"
if [ -d "linkadi-web/public/storage" ]; then
    count=$(find linkadi-web/public/storage -type f 2>/dev/null | wc -l)
    echo "  linkadi-web/public/storage: $count files (should be 0!)"
    if [ $count -gt 0 ]; then
        echo "  ⚠️  WARNING: Files found in wrong location!"
        echo "  These files are NOT accessible via web:"
        find linkadi-web/public/storage -type f 2>/dev/null | head -5
    fi
else
    echo "  linkadi-web/public/storage: Does not exist (good!)"
fi

echo ""
echo "7. Database check (last 5 profiles):"
echo "-----------------------------------"
cd linkadi-web
php artisan tinker --execute="
\$profiles = App\Models\Profile::latest()->take(5)->get(['id', 'profile_name', 'profile_image', 'company_logo', 'cover_image']);
foreach (\$profiles as \$profile) {
    echo 'Profile: ' . \$profile->profile_name . PHP_EOL;
    echo '  profile_image: ' . (\$profile->profile_image ?: 'NULL') . PHP_EOL;
    echo '  company_logo: ' . (\$profile->company_logo ?: 'NULL') . PHP_EOL;
    echo '  cover_image: ' . (\$profile->cover_image ?: 'NULL') . PHP_EOL;
    echo PHP_EOL;
}
" 2>/dev/null

echo ""
echo "========================================="
echo "DONE"
echo "========================================="
