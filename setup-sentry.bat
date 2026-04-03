@echo off
REM =============================================================================
REM Sentry.io Integration Setup Script for Windows
REM =============================================================================
REM This script helps you set up Sentry error monitoring for the Search Job App
REM =============================================================================

setlocal enabledelayedexpansion

echo.
echo 🚀 Sentry.io Integration Setup
echo ========================================
echo.

REM Check if .env file exists
if not exist .env (
    echo ❌ Error: .env file not found
    echo Please copy .env.example to .env first
    pause
    exit /b 1
)

echo 📋 Step 1: Sentry.io DSN Configuration
echo ---
echo You need a Sentry project DSN to continue.
echo.
echo To get your DSN:
echo 1. Go to https://sentry.io
echo 2. Create a new project ^(or select existing^)
echo 3. Select 'Laravel' as the platform
echo 4. Copy the DSN starting with https://...
echo.
set /p SENTRY_DSN="Enter your Sentry DSN (or press Enter to skip): "

if not "!SENTRY_DSN!"=="" (
    echo Updating .env with Sentry DSN...
    for /f "delims=" %%i in ('type .env ^| findstr /v "SENTRY_LARAVEL_DSN"') do (
        echo %%i >> .env.tmp
    )
    echo SENTRY_LARAVEL_DSN=!SENTRY_DSN! >> .env.tmp
    move /y .env.tmp .env > nul
    echo ✓ Sentry DSN configured
)

echo.
echo 📧 Step 2: Email Configuration
echo ---
set /p EMAIL_ENABLED="Enable email notifications for admins? (y/n): "

if /i "!EMAIL_ENABLED!"=="y" (
    echo Configure your mail service in .env:
    echo   MAIL_MAILER=smtp
    echo   MAIL_HOST=your-smtp-host
    echo   MAIL_PORT=465
    echo   MAIL_USERNAME=your-email
    echo   MAIL_PASSWORD=your-password
    echo.
    echo ⚠️  Please update these values in your .env file
)

echo.
echo 💬 Step 3: Slack Integration (Optional)
echo ---
set /p SLACK_ENABLED="Enable Slack notifications? (y/n): "

if /i "!SLACK_ENABLED!"=="y" (
    echo.
    echo To set up Slack notifications:
    echo 1. Go to your Slack workspace settings
    echo 2. Create an Incoming Webhook
    echo 3. Copy the webhook URL
    echo.
    set /p SLACK_WEBHOOK="Enter your Slack webhook URL (or press Enter to skip): "
    
    if not "!SLACK_WEBHOOK!"=="" (
        for /f "delims=" %%i in ('type .env ^| findstr /v "SLACK_WEBHOOK_URL"') do (
            echo %%i >> .env.tmp
        )
        echo SLACK_WEBHOOK_URL=!SLACK_WEBHOOK! >> .env.tmp
        move /y .env.tmp .env > nul
        echo ✓ Slack webhook configured
    )
)

echo.
echo 🔧 Step 4: Environment Configuration
echo ---
set /p SENTRY_ENV="What environment is this? (local/development/staging/production) [production]: "
if "!SENTRY_ENV!"=="" set SENTRY_ENV=production

for /f "delims=" %%i in ('type .env ^| findstr /v "SENTRY_ENVIRONMENT"') do (
    echo %%i >> .env.tmp
)
echo SENTRY_ENVIRONMENT=!SENTRY_ENV! >> .env.tmp
move /y .env.tmp .env > nul
echo ✓ Environment set to: !SENTRY_ENV!

echo.
echo 📦 Step 5: Dependencies
echo ---
echo Installing Sentry package via Composer...
composer require sentry/sentry-laravel

echo.
echo ✅ Setup Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Update your MAIL_* settings in .env ^(if email notifications enabled^)
echo 2. Create at least one admin user in your application
echo 3. Test the integration: php artisan sentry:test
echo.
echo Documentation: See SENTRY_SETUP.md for detailed information
echo.
echo 🔗 Resources:
echo    Sentry Dashboard: https://sentry.io
echo    Documentation: https://docs.sentry.io/platforms/php/guides/laravel/
echo.
pause
