<?php

use App\Livewire\Pages\Afms\Components\UserRoles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function superAdmin(): User
{
    $role = Role::firstOrCreate(['name' => 'SUPER-ADMIN', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function plainAdmin(): User
{
    $role = Role::firstOrCreate(['name' => 'ADMIN', 'guard_name' => 'web']);
    $role->syncPermissions(Permission::all());

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('a PMU user can open both Requisition and PMU', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    $this->actingAs($user)->get(route('requisition.index'))->assertOk();
    $this->actingAs($user)->get(route('pmu.index'))->assertOk();
});

test('the super admin sees the Roles tab with every role and user', function () {
    $pmu = User::factory()->create(['name' => 'Pmu Person']);
    $pmu->assignRole(Role::findByName('PMU'));

    $this->actingAs(superAdmin())->get(route('user-management.index'))
        ->assertOk()
        ->assertSee('User Access')
        ->assertSee('User Access')
        ->assertSee('GASU')
        ->assertSee('PMU')
        ->assertSee('Pmu Person');
});

test('an ADMIN does not get the Roles tab and cannot load it directly', function () {
    $admin = plainAdmin();

    $this->actingAs($admin)->get(route('user-management.index'))
        ->assertOk()
        ->assertDontSee('User Access');

    Livewire::actingAs($admin)->test(UserRoles::class)->assertForbidden();
});

test('the super admin can give one user extra access without changing others in the role', function () {
    $gasu = User::factory()->create();
    $gasu->assignRole(Role::findByName('GASU'));
    $otherGasu = User::factory()->create();
    $otherGasu->assignRole(Role::findByName('GASU'));

    $this->actingAs($gasu)->get(route('rsmi.index'))->assertForbidden();

    Livewire::actingAs(superAdmin())
        ->test(UserRoles::class)
        ->call('togglePermission', $gasu->id, 'approve-requisition');

    $this->actingAs($gasu->fresh())->get(route('rsmi.index'))->assertOk();
    expect($otherGasu->fresh()->can('approve-requisition'))->toBeFalse()
        ->and(Role::findByName('GASU')->hasPermissionTo('approve-requisition'))->toBeFalse();

    Livewire::actingAs(superAdmin())
        ->test(UserRoles::class)
        ->call('togglePermission', $gasu->id, 'approve-requisition');

    expect($gasu->fresh()->can('approve-requisition'))->toBeFalse();
});

test('the super admin can take away access that a user gets from their role', function () {
    $pmu = User::factory()->create();
    $pmu->assignRole(Role::findByName('PMU'));
    $otherPmu = User::factory()->create();
    $otherPmu->assignRole(Role::findByName('PMU'));

    Livewire::actingAs(superAdmin())
        ->test(UserRoles::class)
        ->call('togglePermission', $pmu->id, 'create-requisition');

    $this->actingAs($pmu->fresh())->get(route('requisition.index'))->assertForbidden();
    $this->actingAs($pmu->fresh())->get(route('pmu.index'))->assertOk();
    $this->actingAs($otherPmu)->get(route('requisition.index'))->assertOk();

    // Ticking it again restores it.
    Livewire::actingAs(superAdmin())
        ->test(UserRoles::class)
        ->call('togglePermission', $pmu->id, 'create-requisition');

    $this->actingAs($pmu->fresh())->get(route('requisition.index'))->assertOk();
});

test('access from a unit (designation) can be taken away per user', function () {
    $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
    $userRole->syncPermissions(['create-requisition', 'view-dashboard']);
    $user = User::factory()->create();
    $user->assignRole($userRole);

    $section = \App\Models\Section::firstOrCreate(['name' => 'AFMS']);
    $unit = \App\Models\Unit::firstOrCreate(['name' => 'PMU', 'section_id' => $section->id]);
    \App\Models\Employee::create(['user_id' => $user->id, 'section_id' => $section->id, 'unit_id' => $unit->id]);

    expect($user->fresh()->can('manage-procurement'))->toBeTrue();

    Livewire::actingAs(superAdmin())
        ->test(UserRoles::class)
        ->call('togglePermission', $user->id, 'manage-procurement');

    expect($user->fresh()->can('manage-procurement'))->toBeFalse();
});

test('reset puts a user back to their role default', function () {
    $gasu = User::factory()->create();
    $gasu->assignRole(Role::findByName('GASU'));

    $component = Livewire::actingAs(superAdmin())->test(UserRoles::class);
    $component->call('togglePermission', $gasu->id, 'manage-procurement');
    $component->call('togglePermission', $gasu->id, 'manage-supply');

    expect($gasu->fresh()->can('manage-procurement'))->toBeTrue()
        ->and($gasu->fresh()->can('manage-supply'))->toBeFalse();

    $component->call('resetToRole', $gasu->id);

    expect($gasu->fresh()->can('manage-procurement'))->toBeFalse()
        ->and($gasu->fresh()->can('manage-supply'))->toBeTrue();
});

test('a SUPER-ADMIN account cannot be edited', function () {
    $super = superAdmin();
    $otherSuper = superAdmin();

    Livewire::actingAs($super)
        ->test(UserRoles::class)
        ->call('togglePermission', $otherSuper->id, 'manage-users');

    expect($otherSuper->fresh()->can('manage-users'))->toBeTrue()
        ->and($otherSuper->fresh()->revoked_permissions)->toBeNull();
});

test('unknown permissions are rejected', function () {
    $pmu = User::factory()->create();
    $pmu->assignRole(Role::findByName('PMU'));

    Livewire::actingAs(superAdmin())
        ->test(UserRoles::class)
        ->call('togglePermission', $pmu->id, 'delete-everything')
        ->assertStatus(422);
});
