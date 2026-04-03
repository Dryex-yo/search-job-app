<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),

    // Nama opsi sekarang langsung 'environment' dan 'release'
    'environment' => env('SENTRY_ENVIRONMENT') ?? env('APP_ENV'),
    'release' => env('SENTRY_RELEASE'),

    // Error and Exception Tracking
    'error_types' => E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_STRICT,

    // Tracing / Performance Monitoring
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 1.0),
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.0),

    // Ignore Exceptions (Daftar exception yang tidak perlu dikirim ke Sentry)
    'ignore_exceptions' => [
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ],

    'send_default_pii' => false,
    'attach_stacktrace' => true,
];