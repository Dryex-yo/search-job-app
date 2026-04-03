@echo off
REM Performance Optimization Setup Script for Windows
REM This script installs and configures all Lighthouse optimizations

echo.
echo 🚀 Starting Performance Optimization Setup...
echo ================================================
echo.

REM Step 1: Install dependencies
echo 📦 Step 1: Installing dependencies...
call composer require predis/predis:^2.0 spatie/laravel-medialibrary:^11.0
call npm install vite-plugin-compression terser

REM Step 2: Publish Spatie Media Library config
echo.
echo 📝 Step 2: Publishing Spatie configurations...
call php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"

REM Step 3: Update cache configuration
echo.
echo 💾 Step 3: Setting up cache configuration...
call php artisan cache:clear

REM Step 4: Build frontend with compression
echo.
echo 🔨 Step 4: Building frontend assets...
call npm run build

REM Step 5: Clear all caches
echo.
echo 🧹 Step 5: Clearing application caches...
call php artisan config:clear
call php artisan route:clear
call php artisan view:clear

REM Step 6: Verify configuration
echo.
echo ✅ Step 6: Verifying configuration...
call php artisan config:cache

echo.
echo ✨ Performance optimization setup completed!
echo ================================================
echo.
echo 📋 Next Steps:
echo 1. Start Redis: redis-server (or use Docker/WSL2)
echo 2. Run migrations: php artisan migrate
echo 3. Run tests: php artisan test
echo 4. Audit with Lighthouse: lighthouse http://localhost:8000
echo.
echo 💡 Tips:
echo    - Check LIGHTHOUSE_OPTIMIZATION.md for detailed checklist
echo    - Monitor Redis: redis-cli MONITOR
echo    - View cache: php artisan cache:show
echo.
pause
