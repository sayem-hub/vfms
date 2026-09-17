<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Vehicle;
use App\Services\Audit\FuelEfficiencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('fuel efficiency service flags anomaly when KM/L drops significantly below baseline', function () {
    $company = Company::create(['name' => 'Textile Group', 'code' => 'TG']);
    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'registration_no' => 'DHAKA-METRO-GA-11-2233',
        'current_odometer' => 50000,
        'expected_km_per_liter' => 10.0, // Baseline: 10 KM/L
    ]);

    $driver = Driver::create([
        'company_id' => $company->id,
        'name' => 'Driver Karim',
        'license_number' => 'DL-112233',
        'license_expiry_date' => now()->addYear(),
        'phone' => '01712000000',
    ]);

    // First refill at 50,000 KM
    FuelLog::create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'fuel_type' => 'OCTANE',
        'refill_date' => now()->subDays(2),
        'station_name' => 'Trust Filling Station',
        'odometer_reading' => 50000,
        'fuel_quantity' => 40,
        'unit_price' => 125.0,
        'total_cost' => 5000.0,
    ]);

    // Second refill at 50,200 KM with 40 liters (200 KM / 40 L = 5.0 KM/L; a 50% drop from 10.0 baseline!)
    $currentRefill = new FuelLog([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'fuel_type' => 'OCTANE',
        'refill_date' => now(),
        'station_name' => 'Padma Oil Pump',
        'odometer_reading' => 50200,
        'fuel_quantity' => 40,
        'unit_price' => 125.0,
        'total_cost' => 5000.0,
    ]);

    $service = new FuelEfficiencyService;
    $audited = $service->auditFuelLog($currentRefill);

    expect($audited->km_since_last_refill)->toBe(200)
        ->and((float) $audited->calculated_km_per_liter)->toBe(5.0)
        ->and($audited->is_efficiency_anomaly)->toBeTrue()
        ->and($vehicle->fresh()->current_odometer)->toBe(50200);
});

test('fuel efficiency service confirms normal consumption when within benchmark', function () {
    $company = Company::create(['name' => 'Textile Group', 'code' => 'TG2']);
    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'registration_no' => 'DHAKA-METRO-GA-44-5566',
        'current_odometer' => 30000,
        'expected_km_per_liter' => 10.0,
    ]);

    $driver = Driver::create([
        'company_id' => $company->id,
        'name' => 'Driver Rahim',
        'license_number' => 'DL-445566',
        'license_expiry_date' => now()->addYear(),
        'phone' => '01812000000',
    ]);

    FuelLog::create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'fuel_type' => 'OCTANE',
        'refill_date' => now()->subDays(2),
        'station_name' => 'Trust Filling Station',
        'odometer_reading' => 30000,
        'fuel_quantity' => 40,
        'unit_price' => 125.0,
        'total_cost' => 5000.0,
    ]);

    // Second refill at 30,380 KM with 40 liters (380 KM / 40 L = 9.5 KM/L; only 5% drop from 10.0 baseline)
    $currentRefill = new FuelLog([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'fuel_type' => 'OCTANE',
        'refill_date' => now(),
        'station_name' => 'Padma Oil Pump',
        'odometer_reading' => 30380,
        'fuel_quantity' => 40,
        'unit_price' => 125.0,
        'total_cost' => 5000.0,
    ]);

    $service = new FuelEfficiencyService;
    $audited = $service->auditFuelLog($currentRefill);

    expect($audited->km_since_last_refill)->toBe(380)
        ->and((float) $audited->calculated_km_per_liter)->toBe(9.5)
        ->and($audited->is_efficiency_anomaly)->toBeFalse();
});
