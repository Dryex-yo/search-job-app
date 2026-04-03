#!/bin/bash

# =============================================================================
# Sentry.io Integration Setup Script
# =============================================================================
# This script helps you set up Sentry error monitoring for the Search Job App
# =============================================================================

set -e

echo "🚀 Sentry.io Integration Setup"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env file exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ Error: .env file not found${NC}"
    echo "Please copy .env.example to .env first"
    exit 1
fi

echo "📋 Step 1: Sentry.io DSN Configuration"
echo "---"
echo "You need a Sentry project DSN to continue."
echo ""
echo "To get your DSN:"
echo "1. Go to https://sentry.io"
echo "2. Create a new project (or select existing)"
echo "3. Select 'Laravel' as the platform"
echo "4. Copy the DSN starting with https://..."
echo ""
read -p "Enter your Sentry DSN (or press Enter to skip): " SENTRY_DSN

if [ -n "$SENTRY_DSN" ]; then
    echo "Updating .env with Sentry DSN..."
    if grep -q "SENTRY_LARAVEL_DSN=" .env; then
        sed -i.bak "s|SENTRY_LARAVEL_DSN=.*|SENTRY_LARAVEL_DSN=${SENTRY_DSN}|" .env
    else
        echo "SENTRY_LARAVEL_DSN=${SENTRY_DSN}" >> .env
    fi
    echo -e "${GREEN}✓ Sentry DSN configured${NC}"
fi

echo ""
echo "📧 Step 2: Email Configuration"
echo "---"
read -p "Enable email notifications for admins? (y/n): " EMAIL_ENABLED

if [ "$EMAIL_ENABLED" = "y" ]; then
    echo "Configure your mail service in .env:"
    echo "  MAIL_MAILER=smtp"
    echo "  MAIL_HOST=your-smtp-host"
    echo "  MAIL_PORT=465"
    echo "  MAIL_USERNAME=your-email"
    echo "  MAIL_PASSWORD=your-password"
    echo ""
    echo -e "${YELLOW}⚠️  Please update these values in your .env file${NC}"
fi

echo ""
echo "💬 Step 3: Slack Integration (Optional)"
echo "---"
read -p "Enable Slack notifications? (y/n): " SLACK_ENABLED

if [ "$SLACK_ENABLED" = "y" ]; then
    echo ""
    echo "To set up Slack notifications:"
    echo "1. Go to your Slack workspace settings"
    echo "2. Create an Incoming Webhook"
    echo "3. Copy the webhook URL"
    echo ""
    read -p "Enter your Slack webhook URL (or press Enter to skip): " SLACK_WEBHOOK
    
    if [ -n "$SLACK_WEBHOOK" ]; then
        if grep -q "SLACK_WEBHOOK_URL=" .env; then
            sed -i.bak "s|SLACK_WEBHOOK_URL=.*|SLACK_WEBHOOK_URL=${SLACK_WEBHOOK}|" .env
        else
            echo "SLACK_WEBHOOK_URL=${SLACK_WEBHOOK}" >> .env
        fi
        echo -e "${GREEN}✓ Slack webhook configured${NC}"
    fi
fi

echo ""
echo "🔧 Step 4: Environment Configuration"
echo "---"
read -p "What environment is this? (local/development/staging/production) [production]: " SENTRY_ENV
SENTRY_ENV=${SENTRY_ENV:-production}

if grep -q "SENTRY_ENVIRONMENT=" .env; then
    sed -i.bak "s/SENTRY_ENVIRONMENT=.*/SENTRY_ENVIRONMENT=${SENTRY_ENV}/" .env
else
    echo "SENTRY_ENVIRONMENT=${SENTRY_ENV}" >> .env
fi
echo -e "${GREEN}✓ Environment set to: ${SENTRY_ENV}${NC}"

echo ""
echo "📦 Step 5: Dependencies"
echo "---"
echo "Installing Sentry package via Composer..."
composer require sentry/sentry-laravel

echo ""
echo "🎯 Step 6: Configuration Files"
echo "---"
echo "Creating Sentry configuration files..."

if [ ! -f config/sentry.php ]; then
    echo -e "${YELLOW}⚠️  config/sentry.php already exists${NC}"
else
    echo -e "${GREEN}✓ Sentry configuration file ready${NC}"
fi

echo ""
echo "✅ Setup Complete!"
echo "========================================"
echo ""
echo "Next steps:"
echo "1. Update your MAIL_* settings in .env (if email notifications enabled)"
echo "2. Create at least one admin user in your application"
echo "3. Test the integration: php artisan sentry:test"
echo ""
echo "Documentation: See SENTRY_SETUP.md for detailed information"
echo ""
echo "🔗 Resources:"
echo "   Sentry Dashboard: https://sentry.io"
echo "   Documentation: https://docs.sentry.io/platforms/php/guides/laravel/"
echo ""
