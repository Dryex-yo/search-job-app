# Security Implementation Guide

This document outlines all the security measures that have been implemented to protect your application from SQL Injection attacks and other common vulnerabilities.

## 1. SQL Injection Prevention

### 1.1 PreventSqlInjection Middleware
**File:** `app/Http/Middleware/PreventSqlInjection.php`

This middleware automatically scans all incoming request parameters for SQL injection attack patterns.

#### Features:
- Detects SQL command keywords (UNION, SELECT, INSERT, UPDATE, DELETE, etc.)
- Blocks SQL comments (`--`, `/* */`, `;`)
- Detects quote breaking attempts
- Prevents hex encoding attacks
- Prevents command execution patterns (`cmd`, `bash`, `powershell`, etc.)

#### How it works:
1. All request parameters (GET, POST, and route parameters) are automatically validated
2. Suspicious patterns trigger a 403 Forbidden response
3. All detection attempts are logged for security monitoring

#### Example Log:
```
[2024-04-03] Potential SQL Injection detected
- Parameter: search
- Value: " OR 1=1 --
- IP: 192.168.1.1
- URL: /jobs?search=" OR 1=1 --
```

### 1.2 DatabaseSecurityService
**File:** `app/Services/DatabaseSecurityService.php`

Provides additional database-level security functions.

#### Key Methods:

**enableStrictMode()**
- Enables strict SQL mode for MySQL connections
- Prevents unusual SQL operations
- Called automatically on application boot

**sanitizeSearchTerm($term, $maxLength)**
- Trims and sanitizes user input
- Removes control characters
- Limits length to prevent DOS attacks

**validateNumericId($id)**
- Validates that IDs are numeric only
- Prevents ID injection attacks

**logSuspiciousQuery()**
- Logs potentially suspicious database queries
- Helps with security auditing

### 1.3 NoSqlInjection Validation Rule
**File:** `app/Rules/NoSqlInjection.php`

Custom Laravel validation rule for form inputs.

#### Usage in Controllers:
```php
$request->validate([
    'search' => ['required', new NoSqlInjection()],
    'email' => ['required', 'email', new NoSqlInjection()],
]);
```

#### Or use as string validation:
```php
$request->validate([
    'search' => 'required|no_sql_injection',
]);
```

## 2. Input Validation

### 2.1 Enhanced Controller Validation

All controllers now include strict input validation:

#### JobController::index()
```php
$validated = $request->validate([
    'search' => 'nullable|string|max:255',
    'type' => 'nullable|string|max:50',
    'location' => 'nullable|string|max:255',
    'salary_min' => 'nullable|numeric|min:0',
    'salary_max' => 'nullable|numeric|min:0',
]);
```

#### JobController::apply()
```php
$request->validate([
    'job_id' => 'required|integer|exists:jobs,id',
    'resume' => 'nullable|mimes:pdf|max:2048',
    'cover_letter' => 'required|string|min:10|max:5000',
]);
```

#### Admin DashboardController::jobs()
```php
$validated = $request->validate([
    'search' => 'nullable|string|max:255',
    'status' => 'nullable|in:active,inactive',
    'type' => 'nullable|in:Full-time,Part-time,Contract,Freelance',
]);
```

### 2.2 Validation Best Practices

1. **Always use `exists` rule** for foreign keys
2. **Enforce type checking** with `integer|string|numeric`
3. **Limit string length** to prevent DOS attacks
4. **Use `in` rule** for enum-like fields
5. **Validate file uploads** with proper MIME types and sizes

## 3. Rate Limiting

### 3.1 RateLimitMiddleware
**File:** `app/Http/Middleware/RateLimitMiddleware.php`

Prevents abuse through rate limiting.

#### Current Limits:
- **Login**: 5 attempts per minute per IP
- **Job Applications**: 10 per hour per user
- **Search Operations**: 30 per minute per IP
- **Export Operations**: 5 per hour per user
- **Admin Operations**: 100 per hour per user
- **General API**: 60 per minute per IP/user

#### How to Modify Limits:
Edit the `defineRateLimits()` method in RateLimitMiddleware.php:

```php
RateLimiter::for('apply', function (Request $request) {
    return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
});
```

## 4. Eloquent ORM Safety

The application uses Laravel's Eloquent ORM which is inherently safe from SQL injection:

### Why Eloquent is Safe:
1. **Parameterized Queries**: All queries use parameter binding
2. **No String Concatenation**: Values are passed separately from query structure
3. **Query Builder**: Prevents direct SQL from user input

### Good Examples (Safe):
```php
// Safe - uses parameterized query
Job::where('title', 'like', "%{$search}%")->get();

// Safe - uses query builder
Application::where('status', $status)->get();

// Safe - uses Eloquent model
$job = Job::findOrFail($id);
```

### Bad Examples (Unsafe - Avoid):
```php
// UNSAFE - Never do this!
DB::raw("SELECT * FROM jobs WHERE title = '$search'");

// UNSAFE - Never do this!
DB::statement("UPDATE jobs SET title = '$title' WHERE id = $id");
```

## 5. CSRF Protection

The application includes built-in Laravel CSRF protection:

### How CSRF Protection Works:
1. Every form automatically includes a CSRF token (via `@csrf` in Blade or Inertia)
2. All POST, PUT, PATCH, DELETE requests require valid CSRF token
3. Invalid tokens result in a 419 error

### Verify CSRF in Forms:
```html
<!-- Laravel automatically includes this in forms -->
<meta name="csrf-token" content="{{ csrf_token() }}">
```

## 6. Database Configuration

### Strict Mode Enabled
- The application enables MySQL strict mode on boot
- Prevents ambiguous SQL operations
- Logged in `storage/logs/laravel.log`

### Connection Settings
- Foreign key constraints enabled
- Proper type casting for model attributes
- Strict mass assignment protection

## 7. Security Headers

The application should be configured with these security headers (in your web server):

```
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000; includeSubDomains
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';
```

## 8. Security Monitoring

### Log Files to Monitor:
- **SQL Injection Attempts**: `storage/logs/laravel.log` (WARNING level)
- **Rate Limit Exceeded**: HTTP 429 responses
- **Authorization Failures**: 403 Forbidden responses

### Key Log Entries:
```
[2024-04-03] Potential SQL Injection detected
[2024-04-03] Too many requests
[2024-04-03] Unauthorized access attempt
```

## 9. Files/Folders Removed

The following unused files have been removed to clean up the project:

1. **tests/Unit/ExampleTest.php** - Placeholder test file
2. **tests/Feature/ExampleTest.php** - Placeholder test file  
3. **routes/console.php** - Example console commands only
4. Reference to console.php removed from `bootstrap/app.php`

## 10. Best Practices for Development

### Do's:
✅ Use Eloquent ORM for all database queries
✅ Validate all user input with `$request->validate()`
✅ Use type casting for route model binding
✅ Always use prepared statements
✅ Keep security middleware enabled
✅ Monitor logs for suspicious activity
✅ Use environment variables for sensitive data
✅ Enable rate limiting for public endpoints

### Don'ts:
❌ Never concatenate user input into SQL queries
❌ Never use `DB::raw()` with user input
❌ Never disable CSRF protection
❌ Never trust user input without validation
❌ Never store sensitive data in logs
❌ Never commit `.env` files
❌ Never use `dangerous` option in queries
❌ Never disable middleware for "convenience"

## 11. Testing Security

### Test SQL Injection Prevention:
```
GET /jobs?search=' OR '1'='1
GET /jobs?search=1' UNION SELECT * FROM users --
GET /jobs?search=admin' --
```

Expected result: All requests should be blocked with 403 Forbidden

### Test Rate Limiting:
```
Send 6 requests to /jobs/apply within 1 minute
```

Expected result: 6th request returns 429 status code

## 12. Update Security

To keep your application secure:

1. Regularly update Laravel framework
   ```bash
   composer update
   ```

2. Check for known vulnerabilities
   ```bash
   composer audit
   ```

3. Review security advisories
   ```bash
   npm audit (for JavaScript dependencies)
   ```

4. Monitor the logs directory
   ```bash
   tail -f storage/logs/laravel.log
   ```

## 13. Reporting Security Issues

If you find a security vulnerability:

1. Do NOT publicly disclose it
2. Document the issue with steps to reproduce
3. Report it through a secure channel
4. Allow time for the team to fix it

---

**Last Updated:** April 3, 2024
**Version:** 1.0.0

For questions or issues, please contact your security team.
