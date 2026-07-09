<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily due-date / overdue reminders for incidents and CAPA.
Schedule::command('qms:overdue-reminders')->dailyAt('08:00');
