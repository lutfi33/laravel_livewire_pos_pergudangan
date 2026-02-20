<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware('guest')->group(function () {
    Route::livewire('/', 'pages::auth.login')->name('login');
});
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'role:staff'])->group(function () {

    Route::prefix('staff')->group(function () {
        Route::livewire('/transaction', 'pages::employee.transaction')->name('staff.transaction');
        Route::livewire('/stock', 'pages::employee.stock')->name('staff.stock');
        Route::livewire('/selling', 'pages::employee.selling')->name('staff.selling');
        Route::livewire('/selling/{id}', 'pages::employee.item-transaction')->name('staff.items');
    });
});


Route::middleware(['auth', 'role:owner'])->group(function () {

    Route::prefix('owner')->group(function () {
        Route::livewire('/inventory', 'pages::owner.items')->name('admin.items');
        Route::livewire('/selling', 'pages::employee.selling')->name('admin.selling');
        Route::livewire('/selling/{id}', 'pages::employee.item-transaction')->name('admin.itemSell');
        Route::livewire('/purchase', 'pages::owner.purchase')->name('admin.purchase');
        Route::livewire('/supplier', 'pages::owner.supplier')->name('admin.supplier');
        Route::livewire('/edit-supplier/{id}', 'pages::owner.update-supplier')->name('admin.updatesup');
        Route::livewire('/setting', 'pages::owner.setting')->name('admin.setting');
    });
});
