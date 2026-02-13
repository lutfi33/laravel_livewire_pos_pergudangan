<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::livewire('/', 'pages::auth.login')->name('login');
Route::livewire('/items', 'pages::owner.items')->name('items');