<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequisition;
use App\Models\User;
use App\Services\Settlement\TripExpenseSettlementService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('trip expense settlement accurately calculates advance vs actual expense balance', function () {
    $company = Company::create(['name' => 'Knit Group', 'code' => 'KG']);
    $user = User::create([
        'name' => 'Requester User',
        'email' => 'req@example.com',
        'password' => bcrypt('password'),
    ]);
    $cashier = User::create([
        'name' => 'Cashier User',
        'email' => 'cashier@example.com',
        'password' => bcrypt('password'),
    ]);

    $driver = Driver::create([
        'company_id' => $company->id,
        'name' => 'Driver Anwar',
        'license_number' => 'DL-998877',
        'license_expiry_date' => now()->addYear(),
        'phone' => '01912000000',
    ]);

    $trip = TripRequisition::create([
        'requisition_no' => 'REQ-SETTLE-001',
        'company_id' => $company->id,
        'requester_id' => $user->id,
        'driver_id' => $driver->id,
        'origin_name' => 'Dhaka',
        'destination_name' => 'Chittagong Port',
        'scheduled_start_time' => now(),
    ]);

    // Driver received BDT 10,000 cash advance
    $settlement = TripExpenseSettlement::create([
        'trip_requisition_id' => $trip->id,
        'driver_id' => $driver->id,
        'advance_cash_received' => 10000.0,
        'advance_received_from_user_id' => $cashier->id,
        'advance_disbursed_at' => now()->subDay(),
        'total_fuel_expense' => 5500.0,
        'total_toll_expense' => 1200.0,
        'total_parking_expense' => 200.0,
        'total_driver_food_allowance' => 800.0,
        'total_emergency_repair_expense' => 500.0,
        'total_other_expense' => 0.0,
    ]);

    $service = new TripExpenseSettlementService;
    $service->syncAndCalculate($settlement);

    // Total actual = 5500 + 1200 + 200 + 800 + 500 = 8200
    // Balance = 10000 - 8200 = 1800 (Driver returns BDT 1800 to company)
    expect((float) $settlement->total_actual_expense)->toEqual(8200.0)
        ->and((float) $settlement->balance_amount)->toEqual(1800.0);

    // Cashier settles it
    $service->settleByCashier($settlement, $cashier->id);

    expect($settlement->status)->toBe('SETTLED_BY_CASHIER')
        ->and($settlement->cashier_settled_by)->toBe($cashier->id)
        ->and($settlement->settled_at)->not->toBeNull();
});
