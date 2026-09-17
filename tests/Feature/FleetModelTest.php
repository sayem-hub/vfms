<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\FactoryUnit;
use App\Models\Vehicle;
use App\Models\VehicleCompliance;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create company, factory unit, vehicle and driver with requested fields', function () {
    $company = Company::create([
        'name' => 'Apex Knit Composite Ltd',
        'code' => 'AKCL',
        'address' => 'Mawna, Gazipur',
    ]);

    $factoryUnit = FactoryUnit::create([
        'company_id' => $company->id,
        'name' => 'Unit 1 - Dyeing & Knitting',
        'location_code' => 'GZP-01',
        'latitude' => 24.0950,
        'longitude' => 90.4125,
    ]);

    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'factory_unit_id' => $factoryUnit->id,
        'registration_no' => 'DHAKA METRO-CHA-11-2345',
        'vehicle_type' => 'MICROBUS',
        'ownership_type' => 'COMPANY_OWNED',
        'fuel_type' => 'OCTANE',
        'current_odometer' => 45000,
        'fuel_capacity_liters' => 65,
        'expected_km_per_liter' => 8.50,
    ]);

    $driver = Driver::create([
        'company_id' => $company->id,
        'factory_unit_id' => $factoryUnit->id,
        'name' => 'Md. Rafiqul Islam',
        'office_id_card' => 'EMP-DRV-1042',
        'phone' => '01711000000',
        'photo' => 'drivers/photos/rafiqul.jpg',
        'license_number' => 'DL-987654321',
        'license_type' => 'MEDIUM',
        'license_expiry_date' => now()->addYear(),
        'employment_type' => 'COMPANY_PAYROLL',
        'current_vehicle_id' => $vehicle->id,
    ]);

    expect($driver->office_id_card)->toBe('EMP-DRV-1042')
        ->and($driver->photo)->toBe('drivers/photos/rafiqul.jpg')
        ->and($driver->factory_unit_id)->toBe($factoryUnit->id)
        ->and($driver->factoryUnit->name)->toBe('Unit 1 - Dyeing & Knitting')
        ->and($driver->currentVehicle->registration_no)->toBe('DHAKA METRO-CHA-11-2345');
});

test('vehicle compliance tracks expiry and days remaining', function () {
    $company = Company::create(['name' => 'Echo Spinning', 'code' => 'ESM']);
    $vehicle = Vehicle::create([
        'company_id' => $company->id,
        'registration_no' => 'DHAKA METRO-TA-14-9988',
        'expected_km_per_liter' => 4.0,
    ]);

    $compliance = VehicleCompliance::create([
        'vehicle_id' => $vehicle->id,
        'document_type' => 'FITNESS_CERTIFICATE',
        'certificate_number' => 'BRTA-FIT-2026-991',
        'issue_date' => now()->subMonths(11),
        'expiry_date' => now()->addDays(20),
    ]);

    expect($compliance->isExpired())->toBeFalse()
        ->and($compliance->daysRemaining())->toBeGreaterThan(0);
});
