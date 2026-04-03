# Sentry Integration Quick Start Guide

Welcome to the Search Job App's Sentry error monitoring system! This guide will get you up and running in minutes.

## 🚀 5-Minute Setup

### Step 1: Get Your Sentry DSN

1. Visit https://sentry.io and sign up/log in
2. Create a new project and select **Laravel** as the platform
3. Copy the **DSN** (looks like: `https://xxxxx@xxxxx.ingest.sentry.io/xxxxx`)

### Step 2: Configure Your Environment

Add to `.env`:

```env
SENTRY_ENABLED=true
SENTRY_LARAVEL_DSN=your-sentry-dsn-here
SENTRY_ENVIRONMENT=production
```

### Step 3: Install Dependencies

```bash
composer require sentry/sentry-laravel
```

### Step 4: Test It Works

```bash
php artisan sentry:test
```

You should see success messages and events appearing in your Sentry dashboard!

---

## 📧 Email Notifications

Admin users automatically get emailed when errors occur.

**What you need:**
1. At least one user with `role='admin'`
2. Email configured in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
```

**Test it:**

```bash
php artisan sentry:test --type=email
```

---

## 💬 Slack Notifications

Get instant error alerts in Slack!

### Get Your Slack Webhook

1. Go to your Slack workspace
2. Browse to **Apps** and find **Incoming Webhooks**
3. Click **Create New Webhook**
4. Select your desired channel
5. Copy the **Webhook URL**

### Add to .env

```env
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

### Test it

```bash
php artisan sentry:test --type=slack
```

You'll get a formatted error alert in your Slack channel.

---

## 📊 Sentry Dashboard

After events are captured, view them at:
- **Production Errors:** https://sentry.io/your-project
- **Real-time Issues:** Check the "Issues" tab
- **Performance:** View under "Performance" for transaction tracking

---

## 🎯 Common Usage

### In Your Controllers

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Services\SentryService;

class ApplicationController extends Controller
{
    public function updateStatus(Request $request, $id)
    {
        try {
            // Track user action
            SentryService::setUserContext($request->user());
            SentryService::addBreadcrumb('Updating application', ['app_id' => $id]);
            SentryService::setTag('action', 'status_update');

            // Your application logic
            $app = Application::findOrFail($id);
            $app->status = $request->status;
            $app->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            // Automatically captured with full context
            SentryService::captureException($e, [
                'application_id' => $id,
            ]);
            
            return response()->json(['error' => 'Failed to update'], 500);
        }
    }
}
```

### Manual Error Tracking

```php
use App\Services\SentryService;

// Capture an exception
try {
    // risky code
} catch (\Exception $e) {
    SentryService::captureException($e, ['context' => 'data']);
}

// Capture a message
SentryService::captureMessage('Important event', 'info', ['data' => 'value']);

// Add tags for filtering
SentryService::setTag('feature', 'job_posting');

// Add breadcrumbs (trail of events)
SentryService::addBreadcrumb('User clicked submit', ['button' => 'save']);
```

---

## ✅ Verification Checklist

- [ ] Sentry DSN added to `.env`
- [ ] Composer installed: `composer require sentry/sentry-laravel`
- [ ] Test command runs successfully: `php artisan sentry:test`
- [ ] Events appear in Sentry dashboard
- [ ] At least one admin user exists in database
- [ ] Email configured (if using email notifications)
- [ ] Slack webhook added (if using Slack alerts)

---

## 🔍 Troubleshooting

### Events not appearing in Sentry?

1. Check `.env` has `SENTRY_ENABLED=true`
2. Verify DSN is correct (starts with `https://`)
3. Check application logs: `tail -f storage/logs/laravel.log`
4. Run test: `php artisan sentry:test`

### Email notifications not coming?

1. Ensure at least one user has `role='admin'`
2. Test mail config: `php artisan sentry:test --type=email`
3. Check queue: `php artisan queue:work` (if using async)
4. Review mail logs

### Slack messages not sending?

1. Verify webhook URL in `.env`
2. Test webhook: `php artisan sentry:test --type=slack`
3. Check Slack channel name is correct
4. Ensure webhook has permission to post

---

## 📚 Full Documentation

For detailed setup and advanced configuration:
- See: `SENTRY_SETUP.md` in project root

---

## 🎓 Key Concepts

### Breadcrumbs
Track a series of actions leading up to an error for better debugging.

### Tags
Label errors for easier filtering (e.g., `feature`, `action`, `severity`).

### Context
Attach structured data to errors (request data, user info, etc.).

### Issues
Grouped errors shown in Sentry dashboard. Auto-grouped by error type.

### Releases
Track which code version caused errors.

---

## 🚨 Sample Error Alert (Email)

When an error occurs, admins receive an email with:

```
Subject: 🚨 Error Report: Search Job App

Exception Type: Exception
Message: Application error occurred
File: app/Models/User.php:123
Time: 2024-04-04 14:30:00 UTC
Environment: production

Stack Trace: [full traceback]

Additional Context: [error data]
```

---

## 💬 Sample Error Alert (Slack)

```
🚨 Error in Search Job App

Exception: ValidationException
Environment: production
File: app/Http/Controllers/JobController.php:45
Time: 2024-04-04 14:30:00

Message: "Validation failed for job posting"
```

---

## 🎯 Next Steps

1. **Monitor:** Check Sentry dashboard daily for errors
2. **Resolve:** Mark issues as resolved after fixing
3. **Tune:** Adjust sample rates based on traffic volume
4. **Alert:** Configure custom alert rules for critical errors

---

## Support

- Sentry Docs: https://docs.sentry.io/
- Laravel Guide: https://docs.sentry.io/platforms/php/guides/laravel/
- GitHub Issues: https://github.com/getsentry/sentry-laravel/issues

---

**Any questions?** Check `SENTRY_SETUP.md` for comprehensive documentation.
