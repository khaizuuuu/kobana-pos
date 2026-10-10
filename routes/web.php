<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard (will redirect to login if unauthenticated)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile & Change Password
    Route::get('/profile/password', [AuthController::class, 'showChangePassword'])->name('profile.password');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Menu & Categories (View for all authenticated, mutate for admin only)
    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');

    // Admin Only: Master Mutate, Users & Imports
    Route::middleware('role:admin')->group(function () {
        // Categories CRUD
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        // Menus CRUD & Toggle
        Route::get('/menus/create', [MenuController::class, 'create'])->name('menus.create');
        Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
        Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
        Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::patch('/menus/{menu}/toggle-status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');
        Route::delete('/menus/{menu}', [MenuController::class, 'destroy'])->name('menus.destroy');

        // User Management
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Imports
        Route::get('/imports', [ImportController::class, 'index'])->name('imports.index');
        Route::post('/imports', [ImportController::class, 'store'])->name('imports.store');
    });

// Transaction routes
    Route::get('/transactions', [App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [App\Http\Controllers\TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [App\Http\Controllers\TransactionController::class, 'store'])->name('transactions.store');
    // Transaction detail and admin actions
    Route::get('/transactions/{transaction}', [App\Http\Controllers\TransactionController::class, 'show'])->name('transactions.show');
    Route::middleware('role:admin')->group(function () {
        Route::get('/transactions/{transaction}/edit', [App\Http\Controllers\TransactionController::class, 'edit'])->name('transactions.edit');
        Route::put('/transactions/{transaction}', [App\Http\Controllers\TransactionController::class, 'update'])->name('transactions.update');
        Route::delete('/transactions/{transaction}', [App\Http\Controllers\TransactionController::class, 'destroy'])->name('transactions.destroy');
    });

    // Analysis: FP-Growth (admin & owner)
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/analysis/fp-growth', [App\Http\Controllers\FpGrowthController::class, 'index'])->name('analysis.fp-growth');
        Route::post('/analysis/fp-growth', [App\Http\Controllers\FpGrowthController::class, 'store'])->name('analysis.fp-growth.store');
        Route::get('/analysis/fp-growth/{analysis}', [App\Http\Controllers\FpGrowthController::class, 'show'])->name('analysis.fp-growth.show');
        Route::delete('/analysis/fp-growth/{analysis}', [App\Http\Controllers\FpGrowthController::class, 'destroy'])->name('analysis.fp-growth.destroy');
    });

    // Reports (admin & owner)
    Route::middleware('role:admin,owner')->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-excel', [App\Http\Controllers\ReportController::class, 'exportExcel'])->name('reports.export-excel');
        Route::get('/reports/export-pdf', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    });
});
