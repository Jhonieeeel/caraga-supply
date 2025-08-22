<?php

use App\Livewire\Pages\Afms\StockTable;
use App\Livewire\Pages\Afms\SupplyTable;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('supply', SupplyTable::class)->name('supply.index');
    Route::get('stock', StockTable::class)->name('stock.index');
});

require __DIR__ . '/auth.php';
