<?php

use App\Livewire\Portal\GatePassTerminal;
use App\Models\Company;
use App\Models\Driver;
use App\Models\FactoryUnit;
use App\Models\FixedRoute;
use App\Models\FuelLog;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleGateLog;
use Livewire\Livewire;

beforeEach(function () {
    $this->company = Company::firstOrCreate(
        ['code' => 'NAZ-TEST'],
        ['name' => 'NAZ Test Corp', 'phone' => '01700000000', 'email' => 'test@nz.com']
    );

    $this->factoryUnit = FactoryUnit::firstOrCreate(
        ['location_code' => 'BKBARI-T'],
        ['company_id' => $this->company->id, 'name' => 'BK Bari Unit Test', 'latitude' => 24.0, 'longitude' => 90.0]
    );

    $this->user = User::factory()->create();
});

test('can create fixed route with assigned bus and driver', function () {
    $bus = Vehicle::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->factoryUnit->id,
        'registration_no' => 'DHK-METRO-BA-99-0001',
        'vehicle_type' => 'STAFF_BUS',
        'usage_category' => 'STAFF_COMMUTE_BUS',
        'fuel_type' => 'DIESEL',
        'fuel_payer' => 'COMPANY',
        'fuel_capacity_liters' => 150,
        'expected_km_per_liter' => 4.0,
        'current_odometer' => 50000,
        'status' => 'AVAILABLE',
    ]);

    $driver = Driver::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->factoryUnit->id,
        'name' => 'Test Bus Driver',
        'phone' => '01799887766',
        'license_number' => 'DL-TEST-990001',
        'license_expiry_date' => now()->addYear(),
        'current_vehicle_id' => $bus->id,
    ]);

    $route = FixedRoute::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->factoryUnit->id,
        'route_name' => 'Joydebpur ➔ BK Bari Staff Commute',
        'route_code' => 'R-TEST-01',
        'origin_name' => 'Joydebpur Chowrasta',
        'destination_name' => 'BK Bari Plant',
        'standard_distance_km' => 38.5,
        'scheduled_departure_time' => '06:30 AM',
        'scheduled_return_time' => '06:30 PM',
        'shift_name' => 'General Shift',
        'assigned_vehicle_id' => $bus->id,
        'assigned_driver_id' => $driver->id,
        'is_active' => true,
    ]);

    expect($route->assignedVehicle->registration_no)->toBe('DHK-METRO-BA-99-0001')
        ->and($route->assignedDriver->name)->toBe('Test Bus Driver')
        ->and((float) $route->standard_distance_km)->toBe(38.5)
        ->and($bus->fixedRoutes()->count())->toBe(1);
});

test('can track vehicle monthly fuel quota and usage percentage', function () {
    $car = Vehicle::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->factoryUnit->id,
        'registration_no' => 'DHK-METRO-GA-88-0002',
        'vehicle_type' => 'SEDAN_CAR',
        'usage_category' => 'DEDICATED_MANAGEMENT',
        'dedicated_to_official' => 'Managing Director',
        'fuel_type' => 'OCTANE',
        'fuel_payer' => 'MONTHLY_QUOTA',
        'monthly_fuel_quota_liters' => 200.0,
        'current_odometer' => 20000,
        'status' => 'AVAILABLE',
    ]);

    $driver = Driver::create([
        'company_id' => $this->company->id,
        'name' => 'MD Driver',
        'phone' => '01711224466',
        'license_number' => 'DL-TEST-880002',
        'license_expiry_date' => now()->addYear(),
        'current_vehicle_id' => $car->id,
    ]);

    expect($car->isDedicated())->toBeTrue()
        ->and($car->monthlyFuelQuotaRemaining())->toBe(200.0)
        ->and($car->monthlyFuelQuotaUsagePercent())->toBe(0.0);

    // Refill 80 Liters this month
    FuelLog::create([
        'vehicle_id' => $car->id,
        'driver_id' => $driver->id,
        'trip_request_id' => null,
        'fuel_type' => 'OCTANE',
        'refill_date' => now(),
        'station_name' => 'Trust Filling Station',
        'odometer_reading' => 20000,
        'fuel_quantity' => 80.0,
        'unit_price' => 125.0,
        'total_cost' => 10000.0,
        'calculated_km_per_liter' => 10.0,
        'is_efficiency_anomaly' => false,
        'payment_method' => 'COMPANY_CREDIT_VOUCHER',
    ]);

    expect($car->monthlyFuelConsumedLiters())->toBe(80.0)
        ->and($car->monthlyFuelQuotaRemaining())->toBe(120.0)
        ->and($car->monthlyFuelQuotaUsagePercent())->toBe(40.0);
});

test('gate pass terminal can switch to fixed vehicles and punch gate out and gate in', function () {
    $car = Vehicle::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->factoryUnit->id,
        'registration_no' => 'DHK-METRO-GA-77-0003',
        'vehicle_type' => 'SEDAN_CAR',
        'usage_category' => 'DEDICATED_MANAGEMENT',
        'dedicated_to_official' => 'Director Operations',
        'fuel_type' => 'OCTANE',
        'current_odometer' => 10000,
        'status' => 'AVAILABLE',
    ]);

    $driver = Driver::create([
        'company_id' => $this->company->id,
        'name' => 'Director Driver',
        'phone' => '01899112233',
        'license_number' => 'DL-TEST-770003',
        'license_expiry_date' => now()->addYear(),
        'current_vehicle_id' => $car->id,
    ]);

    $guard = User::factory()->create();

    // 1. Mount terminal & switch to fixed tab
    Livewire::actingAs($guard)
        ->test(GatePassTerminal::class)
        ->call('switchTab', 'fixed')
        ->assertSet('activeTab', 'fixed')
        ->call('selectFixedVehicle', $car->id)
        ->assertSet('selectedFixedVehicleId', $car->id)
        ->assertSet('fixedOutOdometer', 10000)
        // 2. Punch Gate Out
        ->set('fixedDestination', 'Baridhara HO')
        ->set('fixedPurpose', 'Management Meeting')
        ->call('recordFixedGateOut')
        ->assertHasNoErrors();

    // Verify Gate Log was created and vehicle updated
    $car->refresh();
    expect($car->status)->toBe('ON_TRIP')
        ->and($car->current_odometer)->toBe(10000);

    $log = VehicleGateLog::where('vehicle_id', $car->id)->latest('id')->first();
    expect($log)->not->toBeNull()
        ->and($log->status)->toBe('OUT')
        ->and($log->out_odometer)->toBe(10000)
        ->and($log->official_name)->toBe('Director Operations')
        ->and($log->destination)->toBe('Baridhara HO');

    // 3. Punch Gate In with return odometer
    Livewire::actingAs($guard)
        ->test(GatePassTerminal::class)
        ->call('switchTab', 'fixed')
        ->call('selectFixedVehicle', $car->id)
        ->assertSet('activeGateLogId', $log->id)
        ->set('fixedInOdometer', 10065)
        ->call('recordFixedGateIn')
        ->assertHasNoErrors();

    // Verify Gate Log completed and total KM computed
    $log->refresh();
    $car->refresh();

    expect($log->status)->toBe('COMPLETED')
        ->and($log->in_odometer)->toBe(10065)
        ->and((float) $log->total_km)->toBe(65.0)
        ->and($car->status)->toBe('AVAILABLE')
        ->and($car->current_odometer)->toBe(10065);
});
