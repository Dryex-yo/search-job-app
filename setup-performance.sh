#!/bin/bash

# Performance Optimization Setup Script
# This script installs and configures all Lighthouse optimizations

set -e

echo "🚀 Starting Performance Optimization Setup..."
echo "================================================"

# Step 1: Install dependencies
echo "📦 Step 1: Installing dependencies..."
composer require predis/predis:^2.0 spatie/laravel-medialibrary:^11.0
npm install vite-plugin-compression terser

# Step 2: Publish Spatie Media Library config
echo "📝 Step 2: Publishing Spatie configurations..."
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"

# Step 3: Update cache configuration
echo "💾 Step 3: Setting up cache configuration..."
php artisan cache:clear

# Step 4: Build frontend with compression
echo "🔨 Step 4: Building frontend assets..."
npm run build

# Step 5: Clear all caches
echo "🧹 Step 5: Clearing application caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Step 6: Verify configuration
echo "✅ Step 6: Verifying configuration..."
php artisan config:cache

echo ""
echo "✨ Performance optimization setup completed!"
echo "================================================"
echo ""
echo "📋 Next Steps:"
echo "1. Start Redis: redis-server (or use Docker)"
echo "2. Run migrations: php artisan migrate"
echo "3. Run tests: php artisan test"
echo "4. Audit with Lighthouse: lighthouse http://localhost:8000"
echo ""
echo "💡 Tips:"
echo "   - Check /LIGHTHOUSE_OPTIMIZATION.md for detailed checklist"
echo "   - Monitor Redis: redis-cli MONITOR"
echo "   - View cache: php artisan cache:show"
echo ""
