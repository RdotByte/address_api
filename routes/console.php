<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('postcodes:update')->monthlyOn(1, '00:00')->when(function () {
    $month = date('n'); // Get the current month number
    return in_array($month, [2, 5, 8, 11]); // Only run in February, May, August, and November
});
