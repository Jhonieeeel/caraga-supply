<?php

use App\Livewire\Pages\Afms\Components\RequestDetail;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('received date cannot be earlier than requested date', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'completed' => false,
    ]);

    Livewire::actingAs($user)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.requested_date', '2026-09-20')
        ->set('requestForm.received_date', '2026-09-15')
        ->call('update')
        ->assertHasErrors(['requestForm.received_date' => 'after_or_equal']);
});

test('received date cannot be earlier than requested date even after the RIS has already been generated', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'ris' => 'RIS-2026-09-0001',
        'completed' => false,
    ]);

    Livewire::actingAs($user)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.requested_date', '2026-09-20')
        ->set('requestForm.received_date', '2026-09-15')
        ->call('update')
        ->assertHasErrors(['requestForm.received_date' => 'after_or_equal']);

    expect($requisition->fresh()->received_date)->toBeNull();
});

test('received date equal to or later than requested date passes validation', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'completed' => false,
    ]);

    Livewire::actingAs($user)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.requested_date', '2026-09-20')
        ->set('requestForm.received_date', '2026-09-20')
        ->call('update')
        ->assertHasNoErrors();
});

test('approved date cannot be earlier than requested date', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'completed' => false,
    ]);

    Livewire::actingAs($user)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.requested_date', '2026-09-20')
        ->set('requestForm.approved_date', '2026-09-15')
        ->call('update')
        ->assertHasErrors(['requestForm.approved_date' => 'after_or_equal']);
});

test('issued date cannot be earlier than requested date', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'completed' => false,
    ]);

    Livewire::actingAs($user)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.requested_date', '2026-09-20')
        ->set('requestForm.issued_date', '2026-09-15')
        ->call('update')
        ->assertHasErrors(['requestForm.issued_date' => 'after_or_equal']);
});

test('approved and issued dates cannot be earlier than requested date even after the RIS has already been generated', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'ris' => 'RIS-2026-09-0002',
        'completed' => false,
    ]);

    Livewire::actingAs($user)
        ->test(RequestDetail::class)
        ->call('view', $requisition->id)
        ->set('requestForm.requested_date', '2026-09-20')
        ->set('requestForm.approved_date', '2026-09-10')
        ->set('requestForm.issued_date', '2026-09-11')
        ->call('update')
        ->assertHasErrors([
            'requestForm.approved_date' => 'after_or_equal',
            'requestForm.issued_date' => 'after_or_equal',
        ]);

    expect($requisition->fresh())
        ->approved_date->toBeNull()
        ->issued_date->toBeNull();
});
