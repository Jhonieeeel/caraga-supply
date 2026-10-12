<?php

use App\Livewire\Pages\Afms\Components\UserDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('super admin can promote a user to admin', function () {
    $superAdminRole = Role::create(['name' => 'SUPER-ADMIN']);
    $superAdminRole->givePermissionTo('manage-users');
    $adminRole = Role::create(['name' => 'ADMIN']);
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

    expect($target->fresh()->hasRole('ADMIN'))->toBeTrue();
    expect($target->fresh()->hasRole('User'))->toBeFalse();
});

test('super admin can promote a user to super admin', function () {
    $superAdminRole = Role::create(['name' => 'SUPER-ADMIN']);
    $superAdminRole->givePermissionTo('manage-users');
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

    expect($target->fresh()->hasRole('SUPER-ADMIN'))->toBeTrue();
});

test('a user cannot change their own role', function () {
    $superAdminRole = Role::create(['name' => 'SUPER-ADMIN']);
    $superAdminRole->givePermissionTo('manage-users');
    $userRole = Role::create(['name' => 'User']);

    $admin = User::factory()->create();
    $admin->assignRole($superAdminRole);

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $admin->id)
        ->set('role_id', $userRole->id)
        ->call('updateRole');

    expect($admin->fresh()->hasRole('SUPER-ADMIN'))->toBeTrue();
});

test('an admin cannot promote another user to super admin', function () {
    $adminRole = Role::create(['name' => 'ADMIN']);
    $adminRole->givePermissionTo('manage-users');
    $superAdminRole = Role::create(['name' => 'SUPER-ADMIN']);
    $userRole = Role::create(['name' => 'User']);

    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $target = User::factory()->create();
    $target->assignRole($userRole);

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->set('role_id', $superAdminRole->id)
        ->call('updateRole')
        ->assertForbidden();

    expect($target->fresh()->hasRole('SUPER-ADMIN'))->toBeFalse();
});
