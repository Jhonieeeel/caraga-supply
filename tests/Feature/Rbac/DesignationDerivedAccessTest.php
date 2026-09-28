<?php

use App\Models\Employee;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function makeUserRoleEmployee(?string $unitName): User
{
    $user = User::factory()->create();
    $userRole = Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']);
    // Mirrors what the RBAC migration grants the (already-seeded, in production) `User` role.
    $userRole->syncPermissions(['create-requisition', 'view-dashboard']);
    $user->assignRole($userRole);

    $section = Section::firstOrCreate(['name' => 'AFMS']);
    $unit = $unitName
        ? Unit::firstOrCreate(['name' => $unitName, 'section_id' => $section->id])
        : null;

    Employee::create([
        'user_id' => $user->id,
        'section_id' => $section->id,
        'unit_id' => $unit?->id,
    ]);

    return $user->refresh();
}

test('a User-role employee designated GASU still only gets the shared base access, not supply/stock', function () {
    $user = makeUserRoleEmployee('GASU');

    expect($user->can('manage-supply'))->toBeFalse();
    expect($user->can('manage-stock'))->toBeFalse();
    expect($user->can('manage-procurement'))->toBeFalse();
    expect($user->can('manage-users'))->toBeFalse();
    expect($user->can('create-requisition'))->toBeTrue();

    $this->actingAs($user)->get(route('supply.index'))->assertForbidden();
    $this->actingAs($user)->get(route('stock.index'))->assertForbidden();
    $this->actingAs($user)->get(route('requisition.index'))->assertOk();
});

test('a User-role employee designated PMU gets procurement access', function () {
    $user = makeUserRoleEmployee('PMU');

    expect($user->can('manage-procurement'))->toBeTrue();
    expect($user->can('manage-supply'))->toBeFalse();

    $this->actingAs($user)->get(route('pmu.index'))->assertOk();
    $this->actingAs($user)->get(route('supply.index'))->assertForbidden();
});

test('a User-role employee designated HRMU gets user management access', function () {
    $user = makeUserRoleEmployee('HRMU');

    expect($user->can('manage-users'))->toBeTrue();
    expect($user->can('manage-procurement'))->toBeFalse();

    $this->actingAs($user)->get(route('user-management.index'))->assertOk();
});

test('a User-role employee with no matching designation only gets the shared base access', function () {
    $user = makeUserRoleEmployee('RMU');

    expect($user->can('create-requisition'))->toBeTrue();
    expect($user->can('view-dashboard'))->toBeTrue();
    expect($user->can('manage-supply'))->toBeFalse();
    expect($user->can('manage-procurement'))->toBeFalse();
    expect($user->can('manage-users'))->toBeFalse();

    $this->actingAs($user)->get(route('supply.index'))->assertForbidden();
    $this->actingAs($user)->get(route('pmu.index'))->assertForbidden();
    $this->actingAs($user)->get(route('user-management.index'))->assertForbidden();
});

test('a User-role employee with no employee record at all gets only base access, no crash', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web']));

    expect($user->can('manage-supply'))->toBeFalse();
    expect($user->can('manage-procurement'))->toBeFalse();
    expect($user->can('manage-users'))->toBeFalse();
});

test('unit-derived access never applies to a promoted role, it is additive to User only', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::firstOrCreate(['name' => 'PMU', 'guard_name' => 'web']));

    $section = Section::firstOrCreate(['name' => 'AFMS']);
    $unit = Unit::firstOrCreate(['name' => 'GASU', 'section_id' => $section->id]);
    Employee::create(['user_id' => $user->id, 'section_id' => $section->id, 'unit_id' => $unit->id]);
    $user->refresh();

    // Real role permission (PMU) still applies...
    expect($user->can('manage-procurement'))->toBeTrue();
    // ...but GASU's unit does NOT leak extra permissions to a non-`User`-role holder.
    expect($user->can('manage-supply'))->toBeFalse();
});
