<?php

use App\Livewire\Pages\Afms\RequisitionTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('users with any of the four roles can reach the requisition create action', function (string $roleName) {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName($roleName));

    Livewire::actingAs($user)
        ->test(RequisitionTable::class)
        ->call('create')
        ->assertHasErrors(['itemForm.requestedItems']);
})->with(['GASU', 'PMU']);

test('a super admin and admin can also reach the requisition create action', function () {
    Role::create(['name' => 'SUPER-ADMIN'])->givePermissionTo('create-requisition');
    Role::create(['name' => 'ADMIN'])->givePermissionTo('create-requisition');

    foreach (['SUPER-ADMIN', 'ADMIN'] as $roleName) {
        $user = User::factory()->create();
        $user->assignRole($roleName);

        Livewire::actingAs($user)
            ->test(RequisitionTable::class)
            ->call('create')
            ->assertHasErrors(['itemForm.requestedItems']);
    }
});

test('a user with no requisition permission is denied the requisition create action', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(RequisitionTable::class)
        ->call('create')
        ->assertForbidden();
});
