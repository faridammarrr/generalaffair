<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FuelUsageController;
use App\Http\Controllers\PettyCashBudgetController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\RequestItemController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StampMovementController;
use App\Http\Controllers\WorkActivityController;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/reports/export/{type}', [ReportsController::class, 'export'])->name('reports.export');
Route::resource('request-items', RequestItemController::class)->except(['show']);
Route::patch('/request-items/{requestItem}/submit', [RequestItemController::class, 'submit'])->name('request-items.submit');
Route::patch('/request-items/{requestItem}/paid', [RequestItemController::class, 'paid'])->name('request-items.paid');
Route::resource('stamp-movements', StampMovementController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
Route::post('/stamp-movements/{stampMovement}/return', [StampMovementController::class, 'returnBorrow'])->name('stamp-movements.return');
Route::resource('bills', BillController::class)->except(['show']);
Route::patch('/bills/{bill}/paid', [BillController::class, 'paid'])->name('bills.paid'); //paid bills
Route::patch('/bills/{bill}/submit', [BillController::class, 'submit'])->name('bills.submit'); //submit bills
Route::resource('petty-cashes', PettyCashController::class)->except(['show']);
Route::patch('/activities/{activity}/complete', [WorkActivityController::class, 'complete'])->name('activities.complete');
Route::get('/petty-cashes/{pettyCash}/invoice', [PettyCashController::class, 'invoice'])->name('petty-cashes.invoice');
Route::get('/petty-cashes/budgets', [PettyCashBudgetController::class, 'index'])->name('petty-cashes.budgets');
Route::post('/petty-cashes/budgets', [PettyCashBudgetController::class, 'store'])->name('petty-cashes.budgets.store');
Route::resource('fuel-usages', FuelUsageController::class)->except(['show']);
Route::get('/fuel-usages/budgets/create', [FuelUsageController::class, 'createBudget'])->name('fuel-usages.budgets.create');
Route::post('/fuel-usages/budgets', [FuelUsageController::class, 'storeBudget'])->name('fuel-usages.budgets.store');
Route::get('/activities', [WorkActivityController::class, 'index'])->name('activities.index');
Route::post('/activities', [WorkActivityController::class, 'store'])->name('activities.store');
Route::get('/activities/{activity}/edit', [WorkActivityController::class, 'edit'])->name('activities.edit');
Route::put('/activities/{activity}', [WorkActivityController::class, 'update'])->name('activities.update');
Route::delete('/activities/{activity}', [WorkActivityController::class, 'destroy'])->name('activities.destroy');
