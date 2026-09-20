<?php

use App\Filament\Pages\FinancialAnalytics;
use App\Filament\Pages\InterCompanyAllocationPage;
use App\Filament\Widgets\CostPerKmChartWidget;
use App\Filament\Widgets\FleetCostBreakdownChartWidget;
use App\Filament\Widgets\FleetFinancialStatsWidget;
use App\Filament\Widgets\ManagementFuelQuotaWidget;
use App\Models\Company;
use App\Models\CostAllocation;
use App\Models\Driver;
use App\Models\FactoryUnit;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Financial\CostAllocationService;
use App\Services\Financial\CostPerKmService;
use App\Services\Financial\TotalCostOfOwnershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->companyNaz = Company::create([
        'name' => 'N.A.Z. Bangladesh Ltd',
        'code' => 'NAZ',
        'phone' => '+8801711001122',
        'email' => 'naz@nz-bd.com',
    ]);

    $this->companyCakl = Company::create([
        'name' => 'CA Knitwear Ltd',
        'code' => 'CAKL',
        'phone' => '+8801711334455',
        'email' => 'cakl@nz-bd.com',
    ]);

    $this->unitGazipur = FactoryUnit::create([
        'company_id' => $this->companyNaz->id,
        'location_code' => 'BKBARI',
        'name' => 'BK Bari Plant',
        'latitude' => 24.0400,
        'longitude' => 90.3950,
        'address' => 'BK Bari, Gazipur',
    ]);

    $this->admin = User::create([
        'name' => 'Admin Manager',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
    ]);
});

test('cost per km service calculates vehicle and fleet metrics accurately', function () {
    $vehicle = Vehicle::create([
        'company_id' => $this->companyNaz->id,
        'factory_unit_id' => $this->unitGazipur->id,
        'registration_no' => 'DHA-METRO-GA-16-0158',
        'vehicle_type' => 'SEDAN_CAR',
        'usage_category' => 'DEDICATED_MANAGEMENT',
        'dedicated_to_official' => 'Executive Director',
        'fuel_type' => 'OCTANE',
        'monthly_fuel_quota_liters' => 150.0,
        'current_odometer' => 50000,
        'purchase_price' => 3500000.00,
        'purchase_date' => '2022-01-15',
        'status' => 'AVAILABLE',
    ]);

    $driver = Driver::create([
        'company_id' => $this->companyNaz->id,
        'factory_unit_id' => $this->unitGazipur->id,
        'name' => 'Md. Robiul',
        'phone' => '01917577119',
        'license_number' => 'DL-160158-99',
        'license_expiry_date' => now()->addYear(),
        'salary' => 25000.00,
        'current_vehicle_id' => $vehicle->id,
    ]);

    // Add Fuel Log
    FuelLog::create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'refill_date' => now(),
        'fuel_type' => 'OCTANE',
        'station_name' => 'Trust Filling Station',
        'fuel_quantity' => 50.0,
        'unit_price' => 125.0,
        'total_cost' => 6250.00,
        'odometer_reading' => 50000,
    ]);

    // Add Maintenance Record
    MaintenanceRecord::create([
        'work_order_no' => 'WO-TEST-001',
        'vehicle_id' => $vehicle->id,
        'transport_requester_id' => $this->admin->id,
        'maintenance_type' => 'SCHEDULED_PREVENTIVE',
        'workshop_type' => 'FACTORY_IN_HOUSE_WORKSHOP',
        'odometer_at_service' => 50000,
        'service_date' => now(),
        'service_description' => 'Engine oil and brake pads replacement',
        'parts_total_cost' => 3000.00,
        'labor_total_cost' => 1000.00,
        'grand_total_cost' => 4000.00,
        'payment_status' => 'PAID',
    ]);

    // Add Trip Request and Settlement
    $trip = TripRequest::create([
        'request_no' => 'TR-TEST-001',
        'company_id' => $this->companyNaz->id,
        'factory_unit_id' => $this->unitGazipur->id,
        'requester_id' => $this->admin->id,
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'trip_type' => 'OFFICIAL_DUTY',
        'origin_name' => 'HO Baridhara',
        'destination_name' => 'BK Bari Plant',
        'scheduled_start_time' => now()->subHours(5),
        'claimed_distance_km' => 100.0,
        'status' => 'COMPLETED',
    ]);

    TripExpenseSettlement::create([
        'trip_request_id' => $trip->id,
        'driver_id' => $driver->id,
        'total_toll_expense' => 500.00,
        'total_parking_expense' => 100.00,
        'total_driver_food_allowance' => 200.00,
        'total_actual_expense' => 800.00,
        'status' => 'SETTLED_BY_CASHIER',
    ]);

    $service = app(CostPerKmService::class);
    $vehicleCpk = $service->calculateVehicleCpk($vehicle);

    expect($vehicleCpk['total_km'])->toBe(100.0)
        ->and($vehicleCpk['fuel_expense'])->toBe(6250.00)
        ->and($vehicleCpk['maintenance_expense'])->toBe(4000.00)
        ->and($vehicleCpk['operating_expense'])->toBe(800.00)
        ->and($vehicleCpk['driver_salary'])->toBe(25000.00)
        ->and($vehicleCpk['grand_total_cost'])->toBe(36050.00)
        ->and($vehicleCpk['cpk'])->toBe(360.5);

    // Fleet-wide calculation
    $fleetCpk = $service->calculateFleetCpk();
    expect($fleetCpk['active_vehicles_count'])->toBe(1)
        ->and($fleetCpk['total_fleet_km'])->toBe(100.0)
        ->and($fleetCpk['grand_total_fleet_spend'])->toBe(36050.00);

    // Category benchmark
    $benchmarks = $service->calculateCategoryBenchmark();
    expect($benchmarks)->toHaveKey('SEDAN_CAR')
        ->and($benchmarks['SEDAN_CAR']['count'])->toBe(1)
        ->and($benchmarks['SEDAN_CAR']['total_km'])->toBe(100.0);
});

test('total cost of ownership service generates ledger and repair vs replace advisory', function () {
    // 1. Economical Vehicle
    $carEconomical = Vehicle::create([
        'company_id' => $this->companyNaz->id,
        'registration_no' => 'DHA-METRO-GA-27-6387',
        'vehicle_type' => 'SEDAN_CAR',
        'dedicated_to_official' => 'Md. Harun Sir',
        'current_odometer' => 38000,
        'purchase_price' => 3200000.00,
        'purchase_date' => '2023-03-10',
        'status' => 'AVAILABLE',
    ]);

    // 2. Watchlist Vehicle (maintenance 30% of capital)
    $vanWatchlist = Vehicle::create([
        'company_id' => $this->companyNaz->id,
        'registration_no' => 'DHA-METRO-TA-11-3128',
        'vehicle_type' => 'COVERED_VAN_5T',
        'current_odometer' => 120000,
        'purchase_price' => 1000000.00,
        'purchase_date' => '2019-01-01',
        'status' => 'AVAILABLE',
    ]);

    MaintenanceRecord::create([
        'work_order_no' => 'WO-WATCHLIST-01',
        'vehicle_id' => $vanWatchlist->id,
        'transport_requester_id' => $this->admin->id,
        'odometer_at_service' => 120000,
        'service_date' => now()->subMonths(2),
        'service_description' => 'Major engine overhaul',
        'grand_total_cost' => 300000.00, // 30% of 1M
        'payment_status' => 'PAID',
    ]);

    // 3. Replace Vehicle (maintenance > 40% or odometer > 300,000)
    $busReplace = Vehicle::create([
        'company_id' => $this->companyNaz->id,
        'registration_no' => 'DHA-METRO-BA-11-0957',
        'vehicle_type' => 'STAFF_BUS',
        'current_odometer' => 320000, // > 300,000 km triggers replace recommendation!
        'purchase_price' => 4500000.00,
        'purchase_date' => '2015-05-15',
        'status' => 'AVAILABLE',
    ]);

    $tcoService = app(TotalCostOfOwnershipService::class);
    $recommendations = $tcoService->getRepairVsReplaceRecommendations();

    expect($recommendations['total_analyzed'])->toBe(3)
        ->and(count($recommendations['recommend_replace']))->toBe(1)
        ->and($recommendations['recommend_replace'][0]['registration_no'])->toBe('DHA-METRO-BA-11-0957')
        ->and(count($recommendations['watchlist']))->toBe(1)
        ->and($recommendations['watchlist'][0]['registration_no'])->toBe('DHA-METRO-TA-11-3128')
        ->and(count($recommendations['economical']))->toBe(1)
        ->and($recommendations['economical'][0]['registration_no'])->toBe('DHA-METRO-GA-27-6387');

    $summary = $tcoService->getFleetTcoSummary();
    expect($summary['total_vehicles'])->toBe(3)
        ->and($summary['total_capital_value'])->toBe(8700000.00)
        ->and($summary['total_lifetime_maintenance'])->toBe(300000.00);
});

test('cost allocation service generates monthly matrix and creates finalized journal vouchers', function () {
    $vanCakl = Vehicle::create([
        'company_id' => $this->companyCakl->id,
        'registration_no' => 'DHA-METRO-TA-11-9999',
        'vehicle_type' => 'COVERED_VAN_5T',
        'current_odometer' => 40000,
        'status' => 'AVAILABLE',
    ]);

    $driverCakl = Driver::create([
        'company_id' => $this->companyCakl->id,
        'name' => 'CAKL Driver',
        'phone' => '01800112233',
        'license_number' => 'DL-CAKL-01',
        'license_expiry_date' => now()->addYear(),
        'salary' => 22000.00,
        'current_vehicle_id' => $vanCakl->id,
    ]);

    FuelLog::create([
        'vehicle_id' => $vanCakl->id,
        'driver_id' => $driverCakl->id,
        'refill_date' => now(),
        'fuel_type' => 'DIESEL',
        'station_name' => 'CAKL Local Station',
        'fuel_quantity' => 100.0,
        'unit_price' => 105.0,
        'total_cost' => 10500.00,
        'odometer_reading' => 40000,
    ]);

    $allocationService = app(CostAllocationService::class);
    $period = now()->format('Y-m');

    $matrix = $allocationService->calculateMonthlyAllocationMatrix($period);
    expect($matrix)->toBeArray()
        ->and(count($matrix))->toBe(2);

    $caklRow = collect($matrix)->firstWhere('company_code', 'CAKL');
    expect($caklRow)->not->toBeNull()
        ->and($caklRow['fuel_cost'])->toBe(10500.00)
        ->and($caklRow['driver_cost'])->toBe(22000.00)
        ->and($caklRow['grand_total'])->toBe(32500.00);

    // Generate Journal Voucher
    $journal = $allocationService->generateAndSaveJournal(
        $this->companyCakl->id,
        $period,
        null,
        $this->admin->id,
        'Test monthly allocation'
    );

    expect($journal)->toBeInstanceOf(CostAllocation::class)
        ->and($journal->company_id)->toBe($this->companyCakl->id)
        ->and($journal->allocation_period)->toBe($period)
        ->and($journal->status)->toBe('FINALIZED')
        ->and($journal->journal_reference_no)->toContain('JRN-')
        ->and((float) $journal->total_fuel_cost)->toBe(10500.00)
        ->and((float) $journal->total_driver_cost)->toBe(22000.00)
        ->and((float) $journal->grand_total_allocated)->toBe(32500.00)
        ->and($journal->items()->count())->toBe(1);

    $item = $journal->items()->first();
    expect($item->vehicle_id)->toBe($vanCakl->id)
        ->and((float) $item->fuel_cost)->toBe(10500.00)
        ->and((float) $item->total_allocated_cost)->toBe(10500.00);
});

test('filament financial analytics and inter-company allocation pages render and execute actions', function () {
    $this->actingAs($this->admin);

    // 1. Financial Analytics Page
    Livewire::test(FinancialAnalytics::class)
        ->assertSuccessful()
        ->assertViewHas('fleetCpk')
        ->assertViewHas('fleetTco')
        ->assertViewHas('recommendations')
        ->assertViewHas('categoryBenchmarks');

    // 2. Inter-Company Allocation Page
    $period = now()->format('Y-m');
    Livewire::test(InterCompanyAllocationPage::class)
        ->assertSuccessful()
        ->assertSet('selectedPeriod', $period)
        ->call('generateJournal', $this->companyNaz->id)
        ->assertNotified('Allocation Journal Created');

    expect(CostAllocation::where('company_id', $this->companyNaz->id)->where('allocation_period', $period)->exists())->toBeTrue();

    // 3. Widgets render without error
    Livewire::test(FleetFinancialStatsWidget::class)->assertSuccessful();
    Livewire::test(FleetCostBreakdownChartWidget::class)->assertSuccessful();
    Livewire::test(CostPerKmChartWidget::class)->assertSuccessful();
    Livewire::test(ManagementFuelQuotaWidget::class)->assertSuccessful();
});
