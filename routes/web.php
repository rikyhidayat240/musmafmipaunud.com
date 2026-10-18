<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\KandidatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Middleware\IsAdmin;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('dashboard', [HomeController::class, 'index'])->middleware(['auth'])->name('dashboard');

Route::get('/vote/{id}', [VoteController::class, 'index'])->middleware(['auth', 'throttle:15,1']);
Route::post('/vote/{id}', [VoteController::class, 'vote'])->middleware(['auth', 'throttle:5,1']);

Route::resource('events', EventController::class)->except(['show'])->middleware(['auth', 'verified', IsAdmin::class]);

Route::resource('candidates', KandidatController::class)
        ->only(['index', 'store', 'update', 'destroy'])->middleware(['auth', 'verified', IsAdmin::class]);

Route::match(['post', 'delete'], 'users/bulk-delete', [MahasiswaController::class, 'bulkDestroy'])
        ->name('users.bulk-destroy')->middleware(['auth', 'verified', IsAdmin::class]);

Route::resource('users', MahasiswaController::class)
        ->only(['index', 'store', 'update', 'destroy'])->middleware(['auth', 'verified', IsAdmin::class]);

Route::post('users/sync-data/{year}', [MahasiswaController::class, 'syncMahasiswa'])
        ->name('users.sync-data')->middleware(['auth', 'verified', IsAdmin::class]);

Route::post('users/send-bulk-credentials', [MahasiswaController::class, 'sendBulkCredentials'])
        ->name('users.send-bulk-credentials')->middleware(['auth', 'verified', IsAdmin::class]);

Route::post('users/{nim}/send-credentials', [MahasiswaController::class, 'sendCredentials'])
        ->name('users.send-credentials')->middleware(['auth', 'verified', IsAdmin::class]);

Route::get('result', [EventController::class, 'hasil'])->middleware(['auth', IsAdmin::class]);
Route::get('audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index')->middleware(['auth', 'verified', IsAdmin::class]);

if (app()->environment() !== 'production') {
    Route::get('staging/proxy-diagnostic', function (\Illuminate\Http\Request $request) {
        if (app()->environment('production')) {
            abort(404);
        }

        return response()->json([
            'client_ip'         => $request->ip(),
            'scheme'            => $request->getScheme(),
            'is_secure'         => $request->isSecure(),
            'remote_addr'       => $request->server('REMOTE_ADDR'),
            'x_forwarded_for'   => $request->header('x-forwarded-for'),
            'x_forwarded_proto' => $request->header('x-forwarded-proto'),
            'x_forwarded_host'  => $request->header('x-forwarded-host'),
            'x_forwarded_port'  => $request->header('x-forwarded-port'),
        ]);
    })->middleware(['auth', 'verified', IsAdmin::class])->name('staging.proxy-diagnostic');
}

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
