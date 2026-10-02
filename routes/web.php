<?php

use App\Livewire\Actions\Logout;
use App\Livewire\DTR;
use App\Livewire\Managedtr;
use App\Livewire\Pages\Afms\Components\ProcurementPrint;
use App\Livewire\Pages\Afms\Components\ProcurementOrderPrint;
use App\Livewire\Pages\Afms\Dashboard;
use App\Livewire\Pages\Afms\Pmr;
use App\Livewire\Pages\Afms\Procurement;
use App\Livewire\Pages\Afms\RequisitionTable;
use App\Livewire\Pages\Afms\RpciReport;
use App\Livewire\Pages\Afms\RsmiReport;
use App\Livewire\Pages\Afms\ShowData;
use App\Livewire\Pages\Afms\StockTable;
use App\Livewire\Pages\Afms\SupplierTable;
use App\Livewire\Pages\Afms\SupplyTable;
use App\Livewire\Pages\Afms\UserManagement;
use App\Livewire\Pages\Afms\UserTable;
use App\Livewire\Rectification;
use App\Models\Supply;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Volt::route('profile', 'pages.profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('dashboard', Dashboard::class)->name('dashboard')->lazy();
    Route::get('requisition', RequisitionTable::class)->name('requisition.index')
        ->middleware('permission:create-requisition');

    // logout
    Route::post('/logout', Logout::class)->name('logout');

    Route::get('/Rectification', Rectification::class)->name('Rectification');
    Route::get('/Managedtr', Managedtr::class)->name('Managedtr');

    Route::middleware('permission:manage-supply|manage-stock')->group(function () {
        Route::get('supply', SupplyTable::class)->name('supply.index');
        Route::get('stock', StockTable::class)->name('stock.index');
    });

    Route::middleware('permission:approve-requisition')->group(function () {
        Route::get('rsmi', RsmiReport::class)->name('rsmi.index');
        Route::get('rpci', RpciReport::class)->name('rpci.index');
    });

    // pmu
    Route::middleware('permission:manage-procurement')->group(function () {
        Route::get('pmu', Procurement::class)->name('pmu.index');
        Route::get('pmu/suppliers', SupplierTable::class)->name('suppliers.index');
        Route::get('pmu/pmr', Pmr::class)->name('pmr.index');
        Route::get('pmu/{id}', ShowData::class)->name('pmu.show');
        Route::get('pmu/{request}/print-request', ProcurementPrint::class)->name('print-pr');
        Route::get('pmu/{request}/print-order', ProcurementOrderPrint::class)->name('print-po');
    });

    // user management
    Route::middleware('permission:manage-users')->group(function () {
        Route::get('user-management', UserManagement::class)->name('user-management.index');
        Route::get('user', UserTable::class)->name('user.index');
    });
});

require __DIR__ . '/auth.php';
