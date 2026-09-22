<?php

use App\Livewire\Pages\Afms\UserManagement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('add user form creates user, section, unit, and employee', function () {
    $role = Role::create(['name' => 'Super Admin']);
    $actingUser = User::factory()->create();

    Livewire::actingAs($actingUser)
        ->test(UserManagement::class)
        ->set('userForm.name', 'Jane Doe')
        ->set('userForm.email', 'jane@example.com')
        ->set('userForm.dtr_number', '1234')
        ->set('userForm.gender', 'female')
        ->set('userForm.designation', 'Staff')
        ->set('userForm.office_position', 'Clerk')
        ->set('userForm.password', 'password123')
        ->set('userForm.password_confirmation', 'password123')
        ->set('role_id', $role->id)
        ->set('sectionName', 'New Test Section')
        ->set('unitName', 'New Test Unit')
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    $this->assertDatabaseHas('sections', ['name' => 'New Test Section']);
    $this->assertDatabaseHas('units', ['name' => 'New Test Unit']);
    $this->assertDatabaseHas('employees', ['user_id' => User::where('email', 'jane@example.com')->first()->id]);
});
