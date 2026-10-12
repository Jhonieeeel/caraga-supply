<?php

use App\Livewire\Pages\Afms\SupplierTable;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function pmuUser(): User
{
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    return $user;
}

test('a PMU user can create a supplier', function () {
    $user = pmuUser();

    Livewire::actingAs($user)
        ->test(SupplierTable::class)
        ->set('supplierForm.business_name', 'Acme Trading')
        ->set('supplierForm.tin', '123-456-789')
        ->set('supplierForm.philgeps_membership', 'Red')
        ->call('create')
        ->assertHasNoErrors();

    expect(Supplier::where('business_name', 'Acme Trading')->first())
        ->not->toBeNull()
        ->is_eligible->toBeFalse();
});

test('a duplicate TIN is rejected', function () {
    $user = pmuUser();
    Supplier::create(['business_name' => 'Existing Co', 'tin' => '111-111-111']);

    Livewire::actingAs($user)
        ->test(SupplierTable::class)
        ->set('supplierForm.business_name', 'New Co')
        ->set('supplierForm.tin', '111-111-111')
        ->call('create')
        ->assertHasErrors(['supplierForm.tin']);
});
