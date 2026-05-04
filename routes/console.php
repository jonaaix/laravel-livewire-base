<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Local DB backup every 4 hours, 14-day retention. Uncomment to enable.
// Schedule::command('easy-backups:db:create --local --compress --max-local-days=14')
//     ->everyFourHours()
//     ->withoutOverlapping();
