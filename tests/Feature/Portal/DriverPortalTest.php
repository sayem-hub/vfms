<?php

use App\Livewire\Portal\DriverPortal;
use App\Models\Company;
use App\Models\Driver;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequisition;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('driver portal renders successfully', function () {
    app()->setLocale('bn');
    $company = Company::create(['name' => 'Apex Knit', 'code' => 'AKC']);
    $driver = Driver::create([
        'company_id' => $company->id,
        'name' => 'Driver Salam',
        'license_number' => 'DL-SALAM-01',
        'license_expiry_date' => now()->addYear(),
        'phone' => '01712345678',
    ]);

    $response = $this->get('/portal/driver');
    $response->assertOk();
    $response->assertSee('জ্বালানী ও ট্রিপ খরচ এন্ট্রি');
});

test('driver can submit fuel refill log and recalculate settlement', function () {
    $company = Company::create(['name' => 'Apex Knit', 'code' => 'AKC']);
    $user = User::create(['name' => 'Manager', 'email' => 'mgr@example.com', 'password' => bcrypt('password')]);
    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'registration_no' => 'DHAKA-METRO-GA-11-2233',
        'current_odometer' => 40000,
        'expected_km_per_liter' => 8.5,
    ]);
    $driver = Driver::create([
        'company_id' => $company->id,
        'name' => 'Driver Salam',
        'license_number' => 'DL-SALAM-02',
        'license_expiry_date' => now()->addYear(),
        'phone' => '01712345679',
        'current_vehicle_id' => $vehicle->id,
    ]);

    $trip = TripRequisition::create([
        'requisition_no' => 'REQ-TRIP-SALAM',
        'company_id' => $company->id,
        'requester_id' => $user->id,
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'origin_name' => 'HO',
        'destination_name' => 'Factory',
        'scheduled_start_time' => now(),
        'status' => 'IN_TRIP',
    ]);

    // Initial cash advance
    TripExpenseSettlement::create([
        'trip_requisition_id' => $trip->id,
        'driver_id' => $driver->id,
        'advance_cash_received' => 5000.0,
    ]);

    Livewire::test(DriverPortal::class)
        ->call('selectDriver', $driver->id)
        ->set('station_name', 'Padma Filling Station')
        ->set('fuel_odometer', 40200)
        ->set('fuel_quantity', 25.0)
        ->set('fuel_unit_price', 125.0) // 25 * 125 = 3125 BDT
        ->call('submitFuelLog')
        ->assertSet('feedbackType', 'success');

    $this->assertDatabaseHas('fuel_logs', [
        'station_name' => 'Padma Filling Station',
        'odometer_reading' => 40200,
        'total_cost' => 3125.0,
    ]);

    // Now test submitting settlement expenses
    Livewire::test(DriverPortal::class)
        ->call('selectDriver', $driver->id)
        ->set('toll_expense', 400.0)
        ->set('food_allowance', 300.0)
        ->call('submitSettlement')
        ->assertSet('feedbackType', 'success');

    $settlement = TripExpenseSettlement::where('trip_requisition_id', $trip->id)->first();
    expect((float) $settlement->total_fuel_expense)->toEqual(3125.0)
        ->and((float) $settlement->total_toll_expense)->toEqual(400.0)
        ->and((float) $settlement->total_driver_food_allowance)->toEqual(300.0)
        ->and((float) $settlement->total_actual_expense)->toEqual(3825.0)
        ->and((float) $settlement->balance_amount)->toEqual(1175.0); // 5000 - 3825 = 1175 BDT
});
