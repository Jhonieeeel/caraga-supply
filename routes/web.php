<?php

use App\Livewire\Actions\Logout;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Livewire\Pages\Afms\StockTable;
use App\Livewire\Pages\Afms\SupplyTable;
use App\Livewire\Pages\Afms\UserTable;
use Illuminate\Support\Facades\Route;

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard')->lazy();
    Route::get('supply', SupplyTable::class)->name('supply.index');
    Route::get('stock', StockTable::class)->name('stock.index');
    Route::get('requisition', RequisitionTable::class)->name('requisition.index');

    // logout
    Route::post('/logout', Logout::class)->name('logout');

    // user management
    Route::get('user', UserTable::class)->name('user.index');
});

require __DIR__ . '/auth.php';
