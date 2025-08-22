<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CheckpointController;
use App\Http\Controllers\Admin\PatrolAssignmentController;
use App\Http\Controllers\Admin\PatrolDashboardController;
use App\Http\Controllers\Admin\PatrolRouteController;
use App\Http\Controllers\Admin\QrController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Patrol\PatrolController;
use App\Http\Controllers\Patrol\ScanController;
use App\Http\Controllers\PeopleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | HOME / DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/', function () {
        $u = auth()->user();

        if ($u->can('access.view')) {
            return redirect()->route('access.index');
        }
        // listado general
        if ($u->can('access.view.active')) {
            return redirect()->route('access.active');
        }
        // activos (guardia)
        if ($u->can('access.enter')) {
            return redirect()->route('access.create');
        }
        // registrar entrada

        abort(403, 'Sin permisos para acceder a ninguna sección.');
    })->name('home');

    Route::get('/resumen', [DashboardController::class, 'summary'])
    // ->middleware('permission:access.view') // si querés protegerlo
        ->name('dashboard.summary');

    /*
    |--------------------------------------------------------------------------
    | PERSONAS (lookup rápido para accesos)
    |--------------------------------------------------------------------------
    */
    Route::get('/personas/lookup', [PeopleController::class, 'lookup'])
        ->middleware('permission:access.enter')
        ->name('people.lookup');

    /*
    |--------------------------------------------------------------------------
    | ACCESOS (entradas / salidas / listados)
    |--------------------------------------------------------------------------
    */
    Route::prefix('accesos')->name('access.')->group(function () {
        // crear entrada
        Route::get('/crear', [AccessController::class, 'create'])
            ->middleware('permission:access.enter')->name('create');
        Route::post('/', [AccessController::class, 'store'])
            ->middleware('permission:access.enter')->name('store');

        // listados
        Route::get('/', [AccessController::class, 'index'])
            ->middleware('permission:access.view')->name('index');
        Route::get('/activos', [AccessController::class, 'active'])
            ->middleware('permission:access.view.active')->name('active');
        Route::get('/{access}', [AccessController::class, 'show'])
            ->middleware('permission:access.show')->name('show');
    });

    // SALIDAS (dejo fuera del prefix para conservar tus rutas y nombres actuales si los usás en vistas)
    Route::get('/salida', [AccessController::class, 'exitForm'])
        ->middleware('permission:access.exit')->name('access.exit.form');
    Route::match(['GET', 'POST'], '/salida/buscar', [AccessController::class, 'search'])
        ->middleware('permission:access.exit')->name('access.search');
    Route::post('/salida/registrar/{access}', [AccessController::class, 'registerExit'])
        ->middleware('permission:access.exit')->name('access.registerExit');

    /*
    |--------------------------------------------------------------------------
    | REPORTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('reportes')->name('reports.')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])
            ->middleware('permission:reports.view')->name('index');
        Route::get('/export/excel', [ReportsController::class, 'exportExcel'])
            ->middleware('permission:reports.view')->name('export.excel');
        Route::get('/export/pdf', [ReportsController::class, 'exportPdf'])
            ->middleware('permission:reports.view')->name('export.pdf');
    });

    /*
    |--------------------------------------------------------------------------
    | PERFIL (Breeze)
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | SUCURSALES (Branches)
    |--------------------------------------------------------------------------
    */
    Route::resource('branches', BranchController::class)
        ->middleware('permission:branches.manage');
    Route::post('branches/{branch}/mass-update', [BranchController::class, 'massUpdate'])
        ->name('branches.mass-update')
        ->middleware('permission:branches.manage');

    /*
    |--------------------------------------------------------------------------
    | PATRULLAS - VISTA DEL GUARDIA
    |--------------------------------------------------------------------------
    */
    Route::prefix('patrol')->name('patrol.')
        ->middleware(['permission:patrol.view|patrol.scan']) // cualquiera de los dos
        ->group(function () {
            Route::get('/', [PatrolController::class, 'index'])->name('index');
            Route::get('/scan', [ScanController::class, 'showScanner'])
                ->middleware('permission:patrol.scan')->name('scan');
            Route::post('/scan', [ScanController::class, 'store'])
                ->middleware(['permission:patrol.scan', 'throttle:patrol-scan'])
                ->name('scan.store');

            Route::post('/{assignment}/start', [\App\Http\Controllers\Patrol\PatrolController::class, 'start'])
                ->middleware(['permission:patrol.scan', 'can:update,assignment'])
                ->name('start');

            Route::post('/{assignment}/finish', [\App\Http\Controllers\Patrol\PatrolController::class, 'finish'])
                ->middleware(['permission:patrol.scan', 'can:update,assignment'])
                ->name('finish');
        });

    /*
    |--------------------------------------------------------------------------
    | PATRULLAS - ADMIN (rutas, checkpoints, asignaciones, QRs)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin/patrol')->name('admin.patrol.')
        ->middleware(['permission:patrol.manage'])->group(function () {
        Route::get('dashboard', [PatrolDashboardController::class, 'index'])->name('dashboard');

        Route::resource('routes', PatrolRouteController::class)->except(['show']);
        Route::resource('routes.checkpoints', CheckpointController::class)->shallow();
        Route::resource('assignments', PatrolAssignmentController::class)->except(['show']);
        Route::get('checkpoints/{checkpoint}/qr', [QrController::class, 'png'])->name('checkpoints.qr');
        Route::post('/patrol/{assignment}/snooze', [\App\Http\Controllers\Patrol\PatrolController::class, 'snooze'])
            ->middleware('permission:patrol.scan')->name('patrol.snooze');

    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN - ROLES Y USUARIOS
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {

        // Roles
        Route::middleware('permission:roles.manage')->group(function () {
            Route::get('roles', [RoleController::class, 'index'])->name('admin.roles.index');
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('admin.roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        });

        // Usuarios
        Route::middleware('permission:users.manage')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('admin.users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('admin.users.create');
            Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('admin.users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');
        });
    });
});

/*
|--------------------------------------------------------------------------
| ALIAS DASHBOARD (compatibilidad Breeze)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', fn() => redirect()->route('home'))->name('dashboard');

require __DIR__ . '/auth.php';
