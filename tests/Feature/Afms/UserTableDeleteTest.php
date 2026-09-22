<?php

use App\Livewire\Pages\Afms\UserTable;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('super admin can delete another user', function () {
    $admin = User::factory()->create();
    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserTable::class)
        ->call('deleteUser', $target->id)
        ->call('confirmed', ['message' => 'User Deleted', 'id' => $target->id]);

    $this->assertDatabaseMissing('users', ['id' => $target->id]);
});

test('a user cannot delete their own account', function () {
    $admin = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(UserTable::class)
        ->call('deleteUser', $admin->id);

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('a user with related requisitions cannot be deleted', function () {
    $admin = User::factory()->create();
    $target = User::factory()->create();

    Requisition::create([
        'user_id' => $target->id,
        'completed' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(UserTable::class)
        ->call('deleteUser', $target->id);

    $this->assertDatabaseHas('users', ['id' => $target->id]);
});
