<?php

use App\Livewire\Pages\Afms\Components\UserDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('super admin can promote a user to admin', function () {
    $superAdminRole = Role::create(['name' => 'Super Admin']);
    $adminRole = Role::create(['name' => 'Admin']);
    $userRole = Role::create(['name' => 'User']);

    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    $target = User::factory()->create();
    $target->assignRole($userRole);

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->set('role_id', $adminRole->id)
        ->call('updateRole');

    expect($target->fresh()->hasRole('Admin'))->toBeTrue();
    expect($target->fresh()->hasRole('User'))->toBeFalse();
});

test('super admin can promote a user to super admin', function () {
    $superAdminRole = Role::create(['name' => 'Super Admin']);
    $userRole = Role::create(['name' => 'User']);

    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    $target = User::factory()->create();
    $target->assignRole($userRole);

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->set('role_id', $superAdminRole->id)
        ->call('updateRole');

    expect($target->fresh()->hasRole('Super Admin'))->toBeTrue();
});

test('a user cannot change their own role', function () {
    $superAdminRole = Role::create(['name' => 'Super Admin']);
    $userRole = Role::create(['name' => 'User']);

    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $admin->id)
        ->set('role_id', $userRole->id)
        ->call('updateRole');

    expect($admin->fresh()->hasRole('Super Admin'))->toBeTrue();
});
