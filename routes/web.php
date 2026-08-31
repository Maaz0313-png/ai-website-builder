<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('projects', [\App\Http\Controllers\ProjectController::class, 'index'])->name('projects.index');
    Route::post('projects', [\App\Http\Controllers\ProjectController::class, 'store'])
        ->middleware('throttle:generations')
        ->name('projects.store');
    Route::get('projects/models', [\App\Http\Controllers\ProjectController::class, 'models'])->name('projects.models');
    Route::get('projects/{project}', [\App\Http\Controllers\ProjectController::class, 'show'])->name('projects.show');
    Route::delete('projects/{project}', [\App\Http\Controllers\ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('projects/{project}/editor', [\App\Http\Controllers\SiteEditorController::class, 'edit'])->name('projects.editor');
    Route::put('projects/{project}/spec', [\App\Http\Controllers\SiteEditorController::class, 'updateSpec'])->name('projects.spec.update');
    Route::put('projects/{project}/code', [\App\Http\Controllers\SiteEditorController::class, 'updateCode'])->name('projects.code.update');
    Route::get('preview/{project}/{path?}', [\App\Http\Controllers\SiteEditorController::class, 'preview'])->where('path', '.*')->name('projects.preview');

    Route::get('billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('billing');
    Route::post('billing/subscribe', [\App\Http\Controllers\BillingController::class, 'subscribe'])->name('billing.subscribe');
    Route::get('billing/portal', [\App\Http\Controllers\BillingController::class, 'portal'])->name('billing.portal');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::middleware(['auth', \App\Http\Middleware\EnsureUserIsAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\Admin\AdminController::class, 'users'])->name('users');
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\AdminController::class, 'updateUser'])->name('users.update');
        Route::get('/plans', [\App\Http\Controllers\Admin\AdminController::class, 'plans'])->name('plans');
        Route::put('/plans/{plan}', [\App\Http\Controllers\Admin\AdminController::class, 'updatePlan'])->name('plans.update');
    });
