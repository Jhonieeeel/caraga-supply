<?php

use App\Livewire\Pages\Afms\SupplierTable;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('create and update can never set is_eligible true, only toggleEligibility can', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    $component = Livewire::actingAs($user)
        ->test(SupplierTable::class)
        ->set('supplierForm.business_name', 'Acme Trading')
        ->set('supplierForm.tin', '999-999-999')
        ->call('create');

    $supplier = Supplier::where('tin', '999-999-999')->firstOrFail();
    expect($supplier->is_eligible)->toBeFalse();

    // Editing via the form (which excludes is_eligible entirely) still cannot flip it.
    $component->call('edit', $supplier->id)
        ->set('supplierForm.business_name', 'Acme Trading Updated')
        ->call('update');

    expect($supplier->fresh()->is_eligible)->toBeFalse();

    // Only the dedicated toggle action can.
    $component->call('toggleEligibility', $supplier->id);

    expect($supplier->fresh())
        ->is_eligible->toBeTrue()
        ->eligibility_checked_at->not->toBeNull();
});
