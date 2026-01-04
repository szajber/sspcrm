<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientObjectController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\FireExtinguisherTypeController;
use App\Http\Controllers\ProtocolController;
use App\Http\Controllers\ProtocolSettingController;
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

    // Protokoły
    Route::get('/protocols/list/{object}/{system}', [ProtocolController::class, 'index'])->name('protocols.index');
    Route::get('/protocols/create/{object}/{system}', [ProtocolController::class, 'create'])->name('protocols.create');
    Route::post('/protocols/create/{object}/{system}', [ProtocolController::class, 'storeStep1'])->name('protocols.store.step1');
    Route::get('/protocols/{protocol}/step2', [ProtocolController::class, 'step2'])->name('protocols.step2');
    Route::post('/protocols/{protocol}/step2', [ProtocolController::class, 'storeStep2'])->name('protocols.store.step2');
    Route::get('/protocols/{protocol}/step3', [ProtocolController::class, 'step3'])->name('protocols.step3');
    Route::post('/protocols/{protocol}/step3', [ProtocolController::class, 'storeStep3'])->name('protocols.store.step3');
    Route::get('/protocols/{protocol}/preview', [ProtocolController::class, 'preview'])->name('protocols.preview');
    Route::get('/protocols/{protocol}/pdf', [ProtocolController::class, 'pdf'])->name('protocols.pdf');
    Route::get('/protocols/{protocol}/download', [ProtocolController::class, 'downloadPdf'])->name('protocols.download');
    Route::get('/protocols/{protocol}/edit', [ProtocolController::class, 'edit'])->name('protocols.edit');
    Route::put('/protocols/{protocol}', [ProtocolController::class, 'update'])->name('protocols.update');

    // Ustawienia protokołów
    Route::get('/settings/protocols', [ProtocolSettingController::class, 'index'])->name('settings.protocols.index');
    Route::get('/settings/protocols/{system}/edit', [ProtocolSettingController::class, 'edit'])->name('settings.protocols.edit');

    // Szablony protokołów
    Route::get('/settings/protocols/{system}/templates/create', [ProtocolSettingController::class, 'createTemplate'])->name('settings.protocols.templates.create');
    Route::post('/settings/protocols/{system}/templates', [ProtocolSettingController::class, 'storeTemplate'])->name('settings.protocols.templates.store');
    Route::get('/settings/protocols/{system}/templates/{template}/edit', [ProtocolSettingController::class, 'editTemplate'])->name('settings.protocols.templates.edit');
    Route::put('/settings/protocols/{system}/templates/{template}', [ProtocolSettingController::class, 'updateTemplate'])->name('settings.protocols.templates.update');
    Route::delete('/settings/protocols/{system}/templates/{template}', [ProtocolSettingController::class, 'destroyTemplate'])->name('settings.protocols.templates.destroy');
    Route::post('/settings/protocols/{system}/templates/{template}/set-default', [ProtocolSettingController::class, 'setDefault'])->name('settings.protocols.templates.set-default');

    Route::resource('settings/fire-extinguisher-types', FireExtinguisherTypeController::class)
        ->names('settings.fire-extinguisher-types')
        ->parameters(['fire-extinguisher-types' => 'fireExtinguisherType']);

    Route::resource('settings/door-resistance-classes', \App\Http\Controllers\DoorResistanceClassController::class)
        ->names('settings.door-resistance-classes')
        ->parameters(['door-resistance-classes' => 'doorResistanceClass']);

    Route::resource('settings/fire-damper-types', \App\Http\Controllers\FireDamperTypeController::class)
        ->names('settings.fire-damper-types')
        ->parameters(['fire-damper-types' => 'fireDamperType']);

    Route::get('/settings/company', [CompanySettingController::class, 'edit'])->name('company.edit');
    Route::put('/settings/company', [CompanySettingController::class, 'update'])->name('company.update');

    Route::get('/settings/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/settings/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/settings/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/settings/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/settings/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/settings/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});
