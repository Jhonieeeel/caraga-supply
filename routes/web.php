<?php

use App\Livewire\Actions\Logout;
use App\Livewire\DTR;
use App\Livewire\Managedtr;
use App\Livewire\Pages\Afms\Procurement;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Livewire\Pages\Afms\StockTable;
use App\Livewire\Pages\Afms\SupplyTable;
use App\Livewire\Pages\Afms\UserTable;
use App\Livewire\Rectification;
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

    // pmu
    Route::get('pmu', Procurement::class)->name('pmu.index');

    // user management
    Route::get('user', UserTable::class)->name('user.index');
    Route::get('/Rectification', Rectification::class)->name('Rectification');
<<<<<<< HEAD
<<<<<<< HEAD
    Route::get('/Managedtr', Rectification::class)->name('Managedtr');
=======
    Route::get('/Managedtr', Managedtr::class)->name('Managedtr');
>>>>>>> 694fd663cd32485ddea5fe9c9d3e5398541cb59b
=======
    Route::get('/Managedtr', Managedtr::class)->name('Managedtr');
>>>>>>> 694fd663cd32485ddea5fe9c9d3e5398541cb59b
});

require __DIR__ . '/auth.php';
