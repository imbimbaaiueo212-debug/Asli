<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {

    // ================== REKAP PROGRESIF ==================
    $schedule->command('rekap:generate-bulanan')
             ->monthlyOn(26, '01:00')
             ->withoutOverlapping()
             ->runInBackground()
             ->description('Generate Rekap Progresif Bulanan');

    // ================== IMBALAN REKAP ==================
    $schedule->command('imbalan:generate-bulan-ini')
             ->monthlyOn(26, '01:30')
             ->withoutOverlapping()
             ->runInBackground()
             ->description('Generate Imbalan Rekap Bulanan');

    // ================== TRIAL AUTO ACTIVATE ==================
    $schedule->command('trial:auto-activate')
             ->dailyAt('02:00')
             ->withoutOverlapping()
             ->runInBackground()
             ->description('Auto Activate Trial');

    // ================== TRIAL AUTO PROMOTE ==================
    $schedule->command('trial:auto-promote')
             ->dailyAt('00:01')
             ->withoutOverlapping()
             ->runInBackground()
             ->description('Auto Promote Trial');
             
    $schedule->command('stock:generate-template')
             ->monthlyOn(1, '00:05')
             ->withoutOverlapping();

})
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
        ]);

        $middleware->alias([
            'unit.selected' => \App\Http\Middleware\EnsureUnitSelected::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();