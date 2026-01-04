<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientObjectController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('clients', ClientController::class);
    Route::resource('objects', ClientObjectController::class);
    Route::get('/api/clients/{client}/data', [ClientObjectController::class, 'getClientData'])->name('api.clients.data');

    Route::get('/settings/company', [CompanySettingController::class, 'edit'])->name('company.edit');
    Route::put('/settings/company', [CompanySettingController::class, 'update'])->name('company.update');

    Route::get('/settings/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/settings/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/settings/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/settings/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/settings/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/settings/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
