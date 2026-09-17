<?php

use App\Livewire\Portal\GatePassTerminal;
use App\Models\Company;
use App\Models\Driver;
use App\Models\TripRequisition;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('gate pass terminal renders successfully', function () {
    app()->setLocale('bn');
    $response = $this->get('/portal/gate-pass');
    $response->assertOk();
    $response->assertSee('সিকিউরিটি গেট পাস টার্মিনাল');
});

test('security guard can record gate out and gate in', function () {
    $company = Company::create(['name' => 'Apex Knit', 'code' => 'AKC']);
    $user = User::create(['name' => 'Guard', 'email' => 'guard@example.com', 'password' => bcrypt('password')]);
    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'registration_no' => 'DHAKA-METRO-CHA-99-8877',
        'current_odometer' => 50000,
        'expected_km_per_liter' => 8.0,
    ]);
    $driver = Driver::create([
        'company_id' => $company->id,
        'name' => 'Driver Jamil',
        'license_number' => 'DL-9911',
        'license_expiry_date' => now()->addYear(),
        'phone' => '01719999999',
    ]);

    $trip = TripRequisition::create([
        'requisition_no' => 'REQ-GATE-001',
        'company_id' => $company->id,
        'requester_id' => $user->id,
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'origin_name' => 'HO',
        'destination_name' => 'Factory',
        'scheduled_start_time' => now(),
        'status' => 'DISPATCHED',
    ]);

    // 1. Test Gate Out
    Livewire::test(GatePassTerminal::class)
        ->call('selectTrip', $trip->id)
        ->set('start_odometer', 50000)
        ->call('recordGateOut')
        ->assertSet('feedbackType', 'success');

    expect($trip->fresh()->status)->toBe('GATE_OUT')
        ->and($trip->fresh()->start_odometer)->toBe(50000);

    // 2. Test Gate In
    Livewire::test(GatePassTerminal::class)
        ->call('selectTrip', $trip->id)
        ->set('end_odometer', 50075) // 75 KM traveled
        ->call('recordGateIn')
        ->assertSet('feedbackType', 'success');

    expect($trip->fresh()->status)->toBe('COMPLETED')
        ->and($trip->fresh()->end_odometer)->toBe(50075)
        ->and((float) $trip->fresh()->claimed_distance_km)->toEqual(75.0)
        ->and($vehicle->fresh()->current_odometer)->toBe(50075);
});
