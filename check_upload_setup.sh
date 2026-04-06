#!/bin/bash

echo "=== Testing Profile Photo Upload Setup ==="
echo ""

# Test 1: Check storage directories
echo "✓ Checking storage directories..."
if [ -d "storage/app/public/profile-photos" ];then
    echo "  - profile-photos directory exists"
else
    echo "  ✗ profile-photos directory missing"
fi

if [ -d "storage/app/public/resumes" ]; then
    echo "  - resumes directory exists"
else
    echo "  ✗ resumes directory missing"
fi
echo ""

# Test 2: Check symlink
echo "✓ Checking public storage symlink..."
if [ -L "public/storage" ]; then
    echo "  - Symlink exists"
else
    echo "  ✗ Symlink not found"
fi
echo ""

# Test 3: Check permissions
echo "✓ Checking write permissions..."
touch "storage/app/public/test.txt" 2>/dev/null && {
    echo "  - storage/app/public is writable"
    rm "storage/app/public/test.txt"
} || {
    echo "  ✗ storage/app/public is not writable"
}
echo ""

# Test 4: Check GD extension
echo "✓ Checking PHP GD extension..."
php -r "if (extension_loaded('gd')) { echo '  - GD extension is installed\n'; } else { echo '  ✗ GD extension is NOT installed\n'; }" 2>/dev/null
echo ""

echo "=== All checks complete ==="
