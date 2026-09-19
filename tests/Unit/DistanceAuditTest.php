<?php

use App\Models\Company;
use App\Models\TripRequest;
use App\Models\User;
use App\Services\Audit\DistanceAuditService;
use App\Services\Routing\RouteResult;
use App\Services\Routing\RoutingManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('distance audit flags trip anomaly when claimed exceeds expected by more than 15%', function () {
    $company = Company::create(['name' => 'Textile Group', 'code' => 'TG']);
    $user = User::create([
        'name' => 'Transport Officer',
        'email' => 'transport@example.com',
        'password' => bcrypt('secret'),
    ]);

    $trip = TripRequest::create([
        'request_no' => 'TR-TEST-001',
        'company_id' => $company->id,
        'requester_id' => $user->id,
        'origin_name' => 'Head Office Dhaka',
        'destination_name' => 'Mawna Factory Gazipur',
        'origin_latitude' => 23.7925,
        'origin_longitude' => 90.4078,
        'destination_latitude' => 24.1850,
        'destination_longitude' => 90.4320,
        'scheduled_start_time' => now(),
        'start_odometer' => 10000,
        'end_odometer' => 10100, // Claimed: 100 KM
    ]);

    $mockRoutingManager = Mockery::mock(RoutingManager::class);
    $mockRoutingManager->shouldReceive('calculateDistanceAndDuration')
        ->once()
        ->andReturn(RouteResult::success(65.0, 95.0, 'mock'));

    $auditService = new DistanceAuditService($mockRoutingManager);
    $auditedTrip = $auditService->auditTrip($trip);

    expect($auditedTrip->claimed_distance_km)->toEqual(100.0)
        ->and($auditedTrip->expected_distance_km)->toEqual(65.0)
        ->and((float) $auditedTrip->distance_variance_percentage)->toBeGreaterThan(50.0)
        ->and($auditedTrip->is_distance_anomaly)->toBeTrue();
});

test('distance audit approves normal trip within 15% variance', function () {
    $company = Company::create(['name' => 'Textile Group', 'code' => 'TG2']);
    $user = User::create([
        'name' => 'Transport Officer 2',
        'email' => 'transport2@example.com',
        'password' => bcrypt('secret'),
    ]);

    $trip = TripRequest::create([
        'request_no' => 'TR-TEST-002',
        'company_id' => $company->id,
        'requester_id' => $user->id,
        'origin_name' => 'Head Office Dhaka',
        'destination_name' => 'Mawna Factory Gazipur',
        'origin_latitude' => 23.7925,
        'origin_longitude' => 90.4078,
        'destination_latitude' => 24.1850,
        'destination_longitude' => 90.4320,
        'scheduled_start_time' => now(),
        'start_odometer' => 10000,
        'end_odometer' => 10070, // Claimed: 70 KM vs Expected 65 KM (7.69% variance)
    ]);

    $mockRoutingManager = Mockery::mock(RoutingManager::class);
    $mockRoutingManager->shouldReceive('calculateDistanceAndDuration')
        ->once()
        ->andReturn(RouteResult::success(65.0, 95.0, 'mock'));

    $auditService = new DistanceAuditService($mockRoutingManager);
    $auditedTrip = $auditService->auditTrip($trip);

    expect($auditedTrip->claimed_distance_km)->toEqual(70.0)
        ->and($auditedTrip->expected_distance_km)->toEqual(65.0)
        ->and((float) $auditedTrip->distance_variance_percentage)->toBeLessThan(15.0)
        ->and($auditedTrip->is_distance_anomaly)->toBeFalse();
});
