<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('an admin can open the RSMI and RPCI pages', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::all()); // same as the ADMIN role

    $this->actingAs($user)->get(route('rsmi.index'))
        ->assertOk()
        ->assertSee('Report of Supplies and Materials Issued');

    $this->actingAs($user)->get(route('rpci.index'))
        ->assertOk()
        ->assertSee('Report on the Physical count of Inventories', false);
});

test('users without approve-requisition cannot open the RSMI and RPCI pages', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName($role));

    $this->actingAs($user)->get(route('rsmi.index'))->assertForbidden();
    $this->actingAs($user)->get(route('rpci.index'))->assertForbidden();
})->with(['GASU', 'PMU']);

test('RSMI and RPCI are listed under GASU after Supply and Stock', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::all()); // same as the ADMIN role

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['GASU', route('supply.index'), route('stock.index'), route('rsmi.index'), route('rpci.index'), 'Requisition'], false);
});

test('the requisition page no longer has RSMI and RPCI tabs', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::all()); // same as the ADMIN role

    $this->actingAs($user)->get(route('requisition.index'))
        ->assertOk()
        ->assertDontSee('Report of Supplies and Materials Issued')
        ->assertDontSee('Physical count of Inventories');
});

test('a GASU user sees Supply and Stock but not the RSMI and RPCI links', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('GASU'));

    $this->actingAs($user)->get(route('supply.index'))
        ->assertOk()
        ->assertSee(route('stock.index'), false)
        ->assertDontSee(route('rsmi.index'), false)
        ->assertDontSee(route('rpci.index'), false);
});
