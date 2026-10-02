<?php

use App\Livewire\Pages\Afms\UserManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('an admin can create a user via user management', function () {
    Role::create(['name' => 'ADMIN'])->givePermissionTo('manage-users');

    $admin = User::factory()->create();
    $admin->assignRole('ADMIN');

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->set('userForm.name', 'Jane Doe')
        ->set('userForm.email', 'jane@example.com')
        ->set('userForm.dtr_number', '1234')
        ->set('userForm.gender', 'female')
        ->set('userForm.designation', 'Staff')
        ->set('userForm.office_position', 'Clerk')
        ->set('userForm.password', 'password123')
        ->set('userForm.password_confirmation', 'password123')
        ->set('role_id', Role::firstOrCreate(['name' => 'User', 'guard_name' => 'web'])->id) // granting ADMIN requires SUPER-ADMIN
        ->set('sectionName', 'AFMS')
        ->set('unitName', 'GASU')
        ->set('divisionName', 'Division')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
});

test('a GASU user cannot access user management', function () {
    $gasu = User::factory()->create();
    $gasu->assignRole(Role::findByName('GASU'));

    Livewire::actingAs($gasu)
        ->test(UserManagement::class)
        ->call('create')
        ->assertForbidden();
});

// The role-escalation guard itself (an ADMIN cannot promote someone to
// SUPER-ADMIN/ADMIN) is covered by tests/Feature/Afms/UserDetailRoleTest.php.
