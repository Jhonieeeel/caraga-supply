<?php

use App\Livewire\Holiday;
use App\Livewire\Managedtr;
use App\Livewire\Pages\Afms\Components\UserDetail;
use App\Livewire\Pages\Afms\UserManagement;
use App\Livewire\Pages\Afms\UserTable;
use App\Livewire\Rectification;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

/*
 * Regression tests for user-management / RBAC security and crash fixes.
 */

function usRole(string $name): Role
{
    $role = Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);

    if (in_array($name, ['SUPER-ADMIN', 'ADMIN'], true)) {
        $role->givePermissionTo('manage-users');
    }

    return $role;
}

function usUserWithRole(string $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);
    $user->assignRole(usRole($role));

    return $user;
}

function usCreatePayload($component)
{
    return $component
        ->set('userForm.name', 'Jane Doe')
        ->set('userForm.email', 'jane@example.com')
        ->set('userForm.dtr_number', '1234')
        ->set('userForm.gender', 'female')
        ->set('userForm.designation', 'Staff')
        ->set('userForm.office_position', 'Clerk')
        ->set('userForm.password', 'password123')
        ->set('userForm.password_confirmation', 'password123')
        ->set('sectionName', 'AFMS')
        ->set('unitName', 'GASU')
        ->set('divisionName', 'Division');
}

// #1 Rectification deleteUser authorization

test('#1 a user without a role cannot delete another user via Rectification', function () {
    $nobody = User::factory()->create();
    $target = User::factory()->create();

    Livewire::actingAs($nobody)
        ->test(Rectification::class)
        ->call('deleteUser', $target->id)
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $target->id]);
});

test('#1 an admin cannot delete a super admin via Rectification', function () {
    $admin = usUserWithRole('ADMIN');
    $super = usUserWithRole('SUPER-ADMIN');

    Livewire::actingAs($admin)
        ->test(Rectification::class)
        ->call('deleteUser', $super->id)
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $super->id]);
});

test('#1 a super admin can still delete a plain user via Rectification', function () {
    $super = usUserWithRole('SUPER-ADMIN');
    $target = usUserWithRole('User');

    Livewire::actingAs($super)
        ->test(Rectification::class)
        ->call('deleteUser', $target->id);

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('#1 the Rectification delete button is hidden from users who cannot delete', function () {
    $nobody = User::factory()->create();
    User::factory()->create();

    Livewire::actingAs($nobody)
        ->test(Rectification::class)
        ->assertOk()
        ->assertDontSeeHtml('wire:click="deleteUser(');

    $super = usUserWithRole('SUPER-ADMIN');

    Livewire::actingAs($super)
        ->test(Rectification::class)
        ->assertSeeHtml('wire:click="deleteUser(');
});

// #2 password reset

test('#2 an admin cannot reset a super admin password', function () {
    $admin = usUserWithRole('ADMIN');
    $super = usUserWithRole('SUPER-ADMIN');

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $super->id)
        ->set('userForm.current_password', 'password')
        ->set('userForm.new_password', 'hijacked-pass-123')
        ->set('userForm.new_password_confirmation', 'hijacked-pass-123')
        ->call('updatePassword')
        ->assertForbidden();

    expect(Hash::check('hijacked-pass-123', $super->fresh()->password))->toBeFalse();
});

test('#2 a new password must be at least 8 characters', function () {
    $super = usUserWithRole('SUPER-ADMIN');
    $target = usUserWithRole('User');

    Livewire::actingAs($super)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->set('userForm.current_password', 'password')
        ->set('userForm.new_password', 'abc')
        ->set('userForm.new_password_confirmation', 'abc')
        ->call('updatePassword')
        ->assertHasErrors('userForm.new_password');

    expect(Hash::check('abc', $target->fresh()->password))->toBeFalse();
});

test('#2 an operator can reset a plain user password after re-entering their own password', function () {
    $admin = usUserWithRole('ADMIN');
    $target = usUserWithRole('User');

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->set('userForm.current_password', 'password')
        ->set('userForm.new_password', 'new-secret-123')
        ->set('userForm.new_password_confirmation', 'new-secret-123')
        ->call('updatePassword')
        ->assertHasNoErrors();

    expect(Hash::check('new-secret-123', $target->fresh()->password))->toBeTrue();
});

// #3 policy: privileged targets

test('#3 an admin cannot demote another admin', function () {
    $admin = usUserWithRole('ADMIN');
    $otherAdmin = usUserWithRole('ADMIN');
    $userRole = usRole('User');

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $otherAdmin->id)
        ->set('role_id', $userRole->id)
        ->call('updateRole')
        ->assertForbidden();

    expect($otherAdmin->fresh()->hasRole('ADMIN'))->toBeTrue();
});

test('#3 an admin cannot demote a super admin', function () {
    $admin = usUserWithRole('ADMIN');
    $super = usUserWithRole('SUPER-ADMIN');

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $super->id)
        ->set('role_id', usRole('User')->id)
        ->call('updateRole')
        ->assertForbidden();

    expect($super->fresh()->hasRole('SUPER-ADMIN'))->toBeTrue();
});

test('#3 an admin cannot edit info of a super admin', function () {
    $admin = usUserWithRole('ADMIN');
    $super = usUserWithRole('SUPER-ADMIN', ['email' => 'super@example.com']);

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $super->id)
        ->set('userForm.email', 'attacker@example.com')
        ->call('updateUserInfo')
        ->assertForbidden();

    expect($super->fresh()->email)->toBe('super@example.com');
});

test('#3 an admin cannot change the assignment of a super admin', function () {
    $admin = usUserWithRole('ADMIN');
    $super = usUserWithRole('SUPER-ADMIN');

    Livewire::actingAs($admin)
        ->test(UserDetail::class)
        ->call('userDetail', $super->id)
        ->set('sectionName', 'AFMS')
        ->set('unitName', 'GASU')
        ->call('updateAssignment')
        ->assertForbidden();
});

test('#3 an admin cannot delete another admin via the user table', function () {
    $admin = usUserWithRole('ADMIN');
    $otherAdmin = usUserWithRole('ADMIN');

    Livewire::actingAs($admin)
        ->test(UserTable::class)
        ->call('deleteUser', $otherAdmin->id)
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $otherAdmin->id]);
});

test('#3 a super admin can delete an admin', function () {
    $super = usUserWithRole('SUPER-ADMIN');
    $admin = usUserWithRole('ADMIN');

    Livewire::actingAs($super)
        ->test(UserTable::class)
        ->call('confirmed', ['message' => 'User Deleted', 'id' => $admin->id]);

    $this->assertDatabaseMissing('users', ['id' => $admin->id]);
});

// #4 create with role

test('#4 an admin cannot create a super admin', function () {
    $admin = usUserWithRole('ADMIN');
    $superRole = usRole('SUPER-ADMIN');

    usCreatePayload(Livewire::actingAs($admin)->test(UserManagement::class))
        ->set('role_id', $superRole->id)
        ->call('create')
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
});

test('#4 an HRMU-designated user cannot create an admin', function () {
    $hrmu = usUserWithRole('User');
    $section = \App\Models\Section::create(['name' => 'AFMS']);
    $unit = \App\Models\Unit::create(['name' => 'HRMU', 'section_id' => $section->id]);
    \App\Models\Employee::create(['user_id' => $hrmu->id, 'section_id' => $section->id, 'unit_id' => $unit->id]);

    usCreatePayload(Livewire::actingAs($hrmu)->test(UserManagement::class))
        ->set('role_id', usRole('ADMIN')->id)
        ->call('create')
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
});

test('#4 role_id is required and must exist', function () {
    $admin = usUserWithRole('ADMIN');

    usCreatePayload(Livewire::actingAs($admin)->test(UserManagement::class))
        ->set('role_id', null)
        ->call('create')
        ->assertHasErrors('role_id');

    usCreatePayload(Livewire::actingAs($admin)->test(UserManagement::class))
        ->set('role_id', 999999)
        ->call('create')
        ->assertHasErrors('role_id');

    $this->assertDatabaseMissing('users', ['email' => 'jane@example.com']);
});

// #5 password hash leak

test('#5 the target password hash is not put into the form', function () {
    $super = usUserWithRole('SUPER-ADMIN');
    $target = usUserWithRole('User');

    Livewire::actingAs($super)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->assertSet('userForm.password', null);
});

// #6 confirmed() re-checks

test('#6 confirmed() cannot be called directly by a user without permission', function () {
    $nobody = User::factory()->create();
    $target = User::factory()->create();

    Livewire::actingAs($nobody)
        ->test(UserTable::class)
        ->call('confirmed', ['message' => 'x', 'id' => $target->id])
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $target->id]);
});

test('#6 confirmed() refuses self-delete and users with requisitions', function () {
    $super = usUserWithRole('SUPER-ADMIN');
    $target = usUserWithRole('User');
    Requisition::create(['user_id' => $target->id, 'completed' => false]);

    Livewire::actingAs($super)
        ->test(UserTable::class)
        ->call('confirmed', ['message' => 'x', 'id' => $super->id])
        ->call('confirmed', ['message' => 'x', 'id' => $target->id])
        ->call('confirmed', ['message' => 'x', 'id' => 999999]);

    $this->assertDatabaseHas('users', ['id' => $super->id]);
    $this->assertDatabaseHas('users', ['id' => $target->id]);
});

// #7 guests

test('#7 createGuest requires manage-users', function () {
    $nobody = User::factory()->create();

    Livewire::actingAs($nobody)
        ->test(UserManagement::class)
        ->set('userForm.name', 'Guest Person')
        ->set('userForm.email', 'guest@example.com')
        ->set('agency', 'NDRRMC')
        ->call('createGuest')
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'guest@example.com']);
});

test('#7 guests get a random password shown once to the operator, even if the Guest role is missing', function () {
    expect(Role::where('name', 'Guest')->exists())->toBeFalse();

    $admin = usUserWithRole('ADMIN');
    $shown = null;

    Livewire::actingAs($admin)
        ->test(UserManagement::class)
        ->set('userForm.name', 'Guest Person')
        ->set('userForm.email', 'guest@example.com')
        ->set('agency', 'NDRRMC')
        ->call('createGuest')
        ->assertHasNoErrors()
        ->assertDispatched('tallstackui:dialog', function ($name, $params) use (&$shown) {
            if (preg_match('/password for guest@example\.com: (\S+)/', $params['description'] ?? '', $m)) {
                $shown = $m[1];
            }

            return $shown !== null;
        });

    $guest = User::where('email', 'guest@example.com')->firstOrFail();

    expect($guest->hasRole('Guest'))->toBeTrue();
    expect(Hash::check('password', $guest->password))->toBeFalse();
    expect(Hash::check($shown, $guest->password))->toBeTrue();
    $this->assertDatabaseHas('guests', ['user_id' => $guest->id, 'agency' => 'NDRRMC']);
});

// #8 updateInfo validation

test('#8 updateInfo validates email uniqueness and format', function () {
    $super = usUserWithRole('SUPER-ADMIN');
    $target = usUserWithRole('User', ['email' => 'target@example.com']);
    User::factory()->create(['email' => 'taken@example.com']);

    $component = Livewire::actingAs($super)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->set('userForm.email', 'taken@example.com')
        ->call('updateUserInfo')
        ->assertHasErrors('userForm.email')
        ->set('userForm.email', 'not-an-email')
        ->call('updateUserInfo')
        ->assertHasErrors('userForm.email')
        ->set('userForm.name', '')
        ->set('userForm.email', 'target@example.com')
        ->call('updateUserInfo')
        ->assertHasErrors('userForm.name');

    expect($target->fresh()->email)->toBe('target@example.com');

    // keeping the same email (ignored for the target) is fine
    $component->set('userForm.name', 'Renamed')
        ->call('updateUserInfo')
        ->assertHasNoErrors();

    expect($target->fresh()->name)->toBe('Renamed');
});

// #9 self-delete with requisitions

test('#9 self-deleting an account with requisitions shows an error instead of crashing', function () {
    $user = User::factory()->create();
    Requisition::create(['user_id' => $user->id, 'completed' => false]);

    $this->actingAs($user);

    Volt::test('profile.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser')
        ->assertHasErrors('password')
        ->assertNoRedirect();

    $this->assertDatabaseHas('users', ['id' => $user->id]);
    $this->assertAuthenticatedAs($user);
});

// #10 missing methods

test('#10 Holiday remove() removes a row', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Holiday::class)
        ->set('newHoliday', 'New Year')
        ->set('newDate', '2026-01-01')
        ->call('addHoliday')
        ->set('newHoliday', 'Christmas')
        ->set('newDate', '2026-12-25')
        ->call('addHoliday')
        ->call('remove', 0)
        ->assertSet('rows', [['holiday' => 'Christmas', 'date' => '2026-12-25']]);
});

test('#10 Rectification signatory and CSC print options do not crash', function () {
    Livewire::actingAs(User::factory()->create())
        ->test(Rectification::class)
        ->call('option2', 'signatory')
        ->assertSet('selectedSignatory', 'Lorene Sia-Cathedral')
        ->call('option3', 'print')
        ->assertSet('printRange', '16-31')
        ->call('option1', 'signatory')
        ->assertSet('selectedSignatory', 'Blank')
        ->assertSet('printRange', '16-31');
});

// #11 Managedtr

test('#11 the Managedtr page renders and its actions work', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('Managedtr'))->assertOk();

    Livewire::actingAs($user)
        ->test(Managedtr::class)
        ->set('newHoliday', 'New Year')
        ->set('newDate', '2026-01-01')
        ->call('addHoliday')
        ->assertHasNoErrors()
        ->assertSet('rows', [['holiday' => 'New Year', 'date' => '2026-01-01']])
        ->call('remove', 0)
        ->assertSet('rows', [])
        ->set('signatoryName', 'Jane')
        ->set('signatoryPosition', 'Chief')
        ->call('AddSignatory')
        ->assertHasNoErrors()
        ->assertSet('signatories', [['Name' => 'Jane', 'Designation' => 'Chief']])
        ->call('removeSignatory', 0)
        ->assertSet('signatories', []);
});

// #12 gender prefer_not

test('#12 a user can be created with gender prefer_not', function () {
    $admin = usUserWithRole('ADMIN');

    usCreatePayload(Livewire::actingAs($admin)->test(UserManagement::class))
        ->set('userForm.gender', 'prefer_not')
        ->set('role_id', usRole('User')->id)
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'gender' => 'prefer_not']);
});

// #13 seeder

test('#13 the seeded super admin can reach supply, pmu and user management', function () {
    $this->seed();

    $dave = User::where('email', 'dave@example.com')->firstOrFail();

    expect($dave->hasRole('SUPER-ADMIN'))->toBeTrue();
    expect(Role::findByName('ADMIN')->hasPermissionTo('manage-users'))->toBeTrue();

    $this->actingAs($dave)->get(route('supply.index'))->assertOk();
    $this->actingAs($dave)->get(route('pmu.index'))->assertOk();
    $this->actingAs($dave)->get(route('user.index'))->assertOk();
});

// #14 avatar + dtr number

test('#14 user detail shows the male avatar for lowercase gender and fills the dtr number', function () {
    $super = usUserWithRole('SUPER-ADMIN');
    $target = usUserWithRole('User', ['gender' => 'male', 'dtr_number' => '777']);

    Livewire::actingAs($super)
        ->test(UserDetail::class)
        ->call('userDetail', $target->id)
        ->assertSet('userForm.dtr_number', '777')
        ->assertSeeHtml('illustrators/male_avatar.svg');
});
