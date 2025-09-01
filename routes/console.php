<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('ssl:maintain')->dailyAt('03:30');
Schedule::command('queue:work --stop-when-empty')->everyMinute();
