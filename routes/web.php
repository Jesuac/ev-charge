<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/charges');

Route::resource('charges', ChargeController::class)->except('show');
Route::resource('apartments', ApartmentController::class)->except('show');

Route::get('report', [ReportController::class, 'index'])->name('report.index');

Route::singleton('settings', SettingController::class)->only(['edit', 'update']);
