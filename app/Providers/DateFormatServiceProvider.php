<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;

class DateFormatServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set Carbon locale to German for European formatting
        Carbon::setLocale('de');

        // Register custom date format helpers
        $this->registerDateFormatters();
    }

    /**
     * Register custom date formatting helpers.
     */
    private function registerDateFormatters(): void
    {
        // European date format (DD.MM.YYYY)
        Carbon::macro('toEuropeanDate', function () {
            return $this->format('d.m.Y');
        });

        // European date and time format (DD.MM.YYYY HH:MM)
        Carbon::macro('toEuropeanDateTime', function () {
            return $this->format('d.m.Y H:i');
        });

        // European time format (HH:MM)
        Carbon::macro('toEuropeanTime', function () {
            return $this->format('H:i');
        });

        // European date and time with seconds (DD.MM.YYYY HH:MM:SS)
        Carbon::macro('toEuropeanDateTimeWithSeconds', function () {
            return $this->format('d.m.Y H:i:s');
        });
    }
}
