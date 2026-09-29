<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectAssignmentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectImportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, config('app.supported_locales'), true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.switch');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/projects', [DashboardController::class, 'projects'])->name('dashboard.projects');

    Route::get('/projects/export', [ProjectController::class, 'export'])->name('projects.export');
    Route::get('/projects/import', [ProjectImportController::class, 'create'])->name('projects.import.create');
    Route::get('/projects/import/template', [ProjectImportController::class, 'template'])->name('projects.import.template');
    Route::post('/projects/import', [ProjectImportController::class, 'store'])->name('projects.import.store');
    Route::post('/projects/bulk-assign', [ProjectAssignmentController::class, 'bulkAssign'])->name('projects.bulk-assign');
    Route::resource('projects', ProjectController::class)->except(['destroy']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::post('/projects/{project}/assign', [ProjectAssignmentController::class, 'assign'])->name('projects.assign');
    Route::delete('/projects/{project}/assign', [ProjectAssignmentController::class, 'unassign'])->name('projects.unassign');
    Route::post('/projects/{project}/complete', [ProjectAssignmentController::class, 'complete'])->name('projects.complete');

    Route::resource('users', UserController::class)->except(['show']);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
