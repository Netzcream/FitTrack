<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('ssl:maintain')->dailyAt('03:30');
Schedule::command('queue:work --stop-when-empty')->everyMinute();

// Desactivar planes vencidos
Schedule::command('plans:deactivate-expired')->dailyAt('00:01');

// Activar planes futuros que ya llegaron a su fecha de inicio
Schedule::command('plans:activate-pending')->dailyAt('00:05');

// Generar invoices mensuales al vencer el plan
Schedule::command('invoices:generate-monthly')->dailyAt('00:10');

// Actualizar invoices vencidos
Schedule::command('invoices:update-overdue')->dailyAt('00:20');
