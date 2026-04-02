<?php

namespace App\Providers;

use App\Events\ApplicationStatusChanged;
use App\Events\ApplicationSubmitted;
use App\Listeners\SendApplicationReceivedEmail;
use App\Listeners\SendApplicationStatusChangedEmail;
use App\Listeners\AnalyzeApplicationOnSubmit;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        ApplicationSubmitted::class => [
            SendApplicationReceivedEmail::class,
            AnalyzeApplicationOnSubmit::class,
        ],
        ApplicationStatusChanged::class => [
            SendApplicationStatusChangedEmail::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
