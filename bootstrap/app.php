<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('patrol:dispatch-schedules')
            ->everyMinute()
            ->withoutOverlapping()   // evita solapamientos
            ->runInBackground()      // no bloquea el scheduler
            ->appendOutputTo(storage_path('logs/schedule.log'));
            // ->onOneServer();      // si corrés en varios nodos + lock store compatible
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active'             => \App\Http\Middleware\ActiveUser::class,
            'check.branch'       => \App\Http\Middleware\CheckUserBranch::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
