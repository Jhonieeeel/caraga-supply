<?php

use App\Livewire\Pages\Afms\Components\RequestRIS;
use App\Models\Requisition;
use App\Models\User;
use App\Services\Afms\ConvertRisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('next button stays disabled and an error is shown when RIS generation fails', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'ris' => 'RIS-TEST-0001',
        'status' => 'approved',
        'completed' => false,
    ]);

    // Simulate a failed conversion (e.g. LibreOffice missing or erroring) without
    // depending on the actual environment's LibreOffice install state.
    $this->app->bind(ConvertRisService::class, fn () => new class extends ConvertRisService
    {
        public function handle($filepath, $requisition)
        {
            return false;
        }
    });

    Livewire::actingAs($user)
        ->test(RequestRIS::class)
        ->call('currentData', $requisition->id)
        ->call('getRIS');

    expect($requisition->fresh()->pdf)->toBeNull();
});

test('next button becomes available once the RIS pdf is actually generated', function () {
    $user = User::factory()->create();
    $requisition = Requisition::create([
        'user_id' => $user->id,
        'ris' => 'RIS-TEST-0002',
        'status' => 'approved',
        'completed' => false,
    ]);

    // Simulate a successful LibreOffice conversion without depending on it being installed.
    $this->app->bind(ConvertRisService::class, fn () => new class extends ConvertRisService
    {
        public function handle($filepath, $requisition)
        {
            $requisition->pdf = 'ris/fake.pdf';
            $requisition->save();
        }
    });

    $component = Livewire::actingAs($user)
        ->test(RequestRIS::class)
        ->call('currentData', $requisition->id)
        ->call('getRIS');

    expect($requisition->fresh()->pdf)->toBe('ris/fake.pdf');
    expect($component->get('step'))->toBe(2);
});
