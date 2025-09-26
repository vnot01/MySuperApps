<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the pulse check to run every 30 seconds
Schedule::command('rvm:schedule-pulse-checks')->everyThirtySeconds();

// Schedule the health check to run every five minutes
Schedule::command('rvm:schedule-health-checks')->everyFiveMinutes();
