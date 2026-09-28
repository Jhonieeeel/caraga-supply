<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('a GASU user is denied access to the PMU route', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('GASU'));

    $this->actingAs($user)
        ->get(route('pmu.index'))
        ->assertForbidden();
});

test('a PMU user is granted access to the PMU route', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    $this->actingAs($user)
        ->get(route('pmu.index'))
        ->assertOk();
});

test('a GASU user is granted access to the supply route', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('GASU'));

    $this->actingAs($user)
        ->get(route('supply.index'))
        ->assertOk();
});

test('a PMU user is denied access to the supply route', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findByName('PMU'));

    $this->actingAs($user)
        ->get(route('supply.index'))
        ->assertForbidden();
});
