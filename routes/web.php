<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController, CustomerController, LicenseTypeController, SubscriptionController, ReportController};

Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::get('/customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('/customers/import', [CustomerController::class, 'importForm'])->name('customers.import.form');
    Route::post('/customers/import', [CustomerController::class, 'import'])->name('customers.import');
    Route::resource('customers', CustomerController::class);

    // Licenses
    Route::get('/licenses/{license}/price', [LicenseTypeController::class, 'priceApi'])->name('licenses.price');
    Route::resource('licenses', LicenseTypeController::class)->except(['show']);

    // Subscriptions
    Route::get('/subscriptions/export', [SubscriptionController::class, 'export'])->name('subscriptions.export');
    Route::post('/subscriptions/{subscription}/remind', [SubscriptionController::class, 'sendReminder'])->name('subscriptions.remind');
    Route::resource('subscriptions', SubscriptionController::class)->except(['show']);

    // Reports
    Route::get('/reports/expiring', [ReportController::class, 'expiring'])->name('reports.expiring');
});
