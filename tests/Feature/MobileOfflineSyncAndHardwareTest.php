<?php

use App\Livewire\Portal\MobileTerminal;
use App\Models\Company;
use App\Models\Driver;
use App\Models\FactoryUnit;
use App\Models\FuelLog;
use App\Models\Vehicle;
use App\Models\VehicleGateLog;
use App\Models\VehicleGpsPing;
use App\Services\Mobile\GeofenceVerificationService;
use App\Services\Mobile\ImageWatermarkService;
use App\Services\Mobile\OfflineSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->company = Company::create([
        'name' => 'N.A.Z. Bangladesh Ltd',
        'code' => 'NAZ',
        'phone' => '+8801711001122',
        'email' => 'naz@nz-bd.com',
    ]);

    $this->factoryUnit = FactoryUnit::create([
        'company_id' => $this->company->id,
        'location_code' => 'BKBARI',
        'name' => 'BK Bari Plant',
        'latitude' => 24.1036,
        'longitude' => 90.3991,
        'address' => 'BK Bari, Gazipur',
    ]);

    $this->vehicle = Vehicle::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->factoryUnit->id,
        'registration_no' => 'DHA-METRO-GA-16-0158',
        'vehicle_type' => 'SEDAN_CAR',
        'usage_category' => 'DEDICATED_MANAGEMENT',
        'dedicated_to_official' => 'Executive Director',
        'fuel_type' => 'OCTANE',
        'fuel_payer' => 'MONTHLY_QUOTA',
        'monthly_fuel_quota_liters' => 150.0,
        'current_odometer' => 50000,
        'purchase_price' => 3500000.00,
        'purchase_date' => '2022-01-15',
        'status' => 'AVAILABLE',
    ]);

    $this->driver = Driver::create([
        'company_id' => $this->company->id,
        'factory_unit_id' => $this->factoryUnit->id,
        'name' => 'Md. Robiul',
        'office_id_card' => 'EMP-DRV-1011',
        'phone' => '01917577119',
        'license_number' => 'DL-160158-99',
        'license_expiry_date' => now()->addYear(),
        'salary' => 25000.00,
        'current_vehicle_id' => $this->vehicle->id,
    ]);
});

test('offline sync service processes batch outbox and guarantees idempotency', function () {
    $syncService = app(OfflineSyncService::class);
    $deviceId = 'MOB-SAMSUNG-A52-001';

    // 1. Submit Gate Out action
    $gateOutKey = 'OUT-IDEMP-001';
    $batch1 = [
        [
            'idempotency_key' => $gateOutKey,
            'action_type' => 'GATE_OUT',
            'payload' => [
                'vehicle_id' => $this->vehicle->id,
                'driver_id' => $this->driver->id,
                'out_odometer' => 50000,
                'destination' => 'Corporate Head Office, Baridhara',
                'purpose' => 'Management Transit',
            ],
            'client_recorded_at' => now()->subHours(2)->toIso8601String(),
        ],
    ];

    $res1 = $syncService->processBatchOutbox($batch1, $deviceId, $this->driver->id);
    expect($res1[0]['status'])->toBe('SYNCED');

    $this->vehicle->refresh();
    expect($this->vehicle->status)->toBe('ON_TRIP');

    $gateLog = VehicleGateLog::where('vehicle_id', $this->vehicle->id)->latest()->first();
    expect($gateLog)->not->toBeNull()
        ->and($gateLog->status)->toBe('OUT')
        ->and((int) $gateLog->out_odometer)->toBe(50000);

    // 2. Submit Fuel Refill action while in trip
    $fuelKey = 'FUEL-IDEMP-002';
    $batch2 = [
        [
            'idempotency_key' => $fuelKey,
            'action_type' => 'FUEL_REFILL',
            'payload' => [
                'vehicle_id' => $this->vehicle->id,
                'driver_id' => $this->driver->id,
                'station_name' => 'Trust Filling Station, Baridhara',
                'odometer_reading' => 50060,
                'fuel_type' => 'OCTANE',
                'fuel_quantity' => 35.0,
                'unit_price' => 125.0,
                'total_cost' => 4375.00,
            ],
            'client_recorded_at' => now()->subHour()->toIso8601String(),
        ],
    ];

    $res2 = $syncService->processBatchOutbox($batch2, $deviceId, $this->driver->id);
    expect($res2[0]['status'])->toBe('SYNCED');

    $fuelLog = FuelLog::where('vehicle_id', $this->vehicle->id)->latest()->first();
    expect($fuelLog)->not->toBeNull()
        ->and((float) $fuelLog->fuel_quantity)->toBe(35.0)
        ->and((float) $fuelLog->total_cost)->toBe(4375.00);

    // 3. Submit Gate In action (Vehicle returns)
    $gateInKey = 'IN-IDEMP-003';
    $batch3 = [
        [
            'idempotency_key' => $gateInKey,
            'action_type' => 'GATE_IN',
            'payload' => [
                'vehicle_id' => $this->vehicle->id,
                'driver_id' => $this->driver->id,
                'in_odometer' => 50120,
            ],
            'client_recorded_at' => now()->toIso8601String(),
        ],
    ];

    $res3 = $syncService->processBatchOutbox($batch3, $deviceId, $this->driver->id);
    expect($res3[0]['status'])->toBe('SYNCED');

    $this->vehicle->refresh();
    expect($this->vehicle->status)->toBe('AVAILABLE')
        ->and((int) $this->vehicle->current_odometer)->toBe(50120);

    $gateLog->refresh();
    expect($gateLog->status)->toBe('COMPLETED')
        ->and((int) $gateLog->in_odometer)->toBe(50120)
        ->and((float) $gateLog->total_km)->toBe(120.0);

    // 4. Test Idempotency (re-submitting identical idempotency key)
    $resReplay = $syncService->processBatchOutbox($batch1, $deviceId, $this->driver->id);
    expect($resReplay[0]['status'])->toBe('ALREADY_SYNCED');

    // Verify no duplicate gate log was created
    expect(VehicleGateLog::where('vehicle_id', $this->vehicle->id)->count())->toBe(1);
});

test('geofence verification service accurately checks coordinates and detects mock location', function () {
    $geofenceService = app(GeofenceVerificationService::class);

    // 1. Coordinates at BK Bari Factory Plant (lat: 24.1036, lng: 90.3991)
    $verifyGzp = $geofenceService->verifyPing(24.1036, 90.3991);
    expect($verifyGzp['is_within_geofence'])->toBeTrue()
        ->and($verifyGzp['nearest_geofence_code'])->toBe('GZP')
        ->and($verifyGzp['distance_meters'])->toBeLessThan(10);

    // 2. Coordinates at Corporate Head Office Baridhara DOHS (lat: 23.8197, lng: 90.4143)
    $verifyHo = $geofenceService->verifyPing(23.8197, 90.4143);
    expect($verifyHo['is_within_geofence'])->toBeTrue()
        ->and($verifyHo['nearest_geofence_code'])->toBe('HO');

    // 3. Coordinates at CAKL Bhobanipur (lat: 24.1483, lng: 90.4224)
    $verifyCakl = $geofenceService->verifyPing(24.1483, 90.4224);
    expect($verifyCakl['is_within_geofence'])->toBeTrue()
        ->and($verifyCakl['nearest_geofence_code'])->toBe('CGZP');

    // 4. Coordinates with mock GPS flag
    $verifyMock = $geofenceService->verifyPing(24.1036, 90.3991, isMockLocation: true);
    expect($verifyMock['is_mock_location'])->toBeTrue()
        ->and($verifyMock['is_suspicious'])->toBeTrue()
        ->and($verifyMock['warning'])->toContain('Fake/Mock GPS detected');

    // 5. Record Ping into database
    $ping = $geofenceService->recordPing(
        vehicleId: $this->vehicle->id,
        latitude: 24.1036,
        longitude: 90.3991,
        driverId: $this->driver->id,
        speedKmh: 45.5,
        batteryLevel: 88,
        isMockLocation: false
    );

    expect($ping)->toBeInstanceOf(VehicleGpsPing::class)
        ->and($ping->nearest_geofence)->toBe('GZP')
        ->and($ping->is_within_geofence)->toBeTrue()
        ->and($ping->speed_kmh)->toBe(45.5)
        ->and($ping->battery_level)->toBe(88);
});

test('image watermark service applies visual and metadata watermark onto uploaded photos', function () {
    Storage::fake('public');
    $watermarkService = app(ImageWatermarkService::class);

    // Create a dummy image using GD
    $img = imagecreatetruecolor(400, 300);
    $bg = imagecolorallocate($img, 200, 200, 200);
    imagefill($img, 0, 0, $bg);

    $tempFile = tempnam(sys_get_temp_dir(), 'test_img_').'.jpg';
    imagejpeg($img, $tempFile);
    imagedestroy($img);

    $uploadedFile = new UploadedFile($tempFile, 'receipt.jpg', 'image/jpeg', null, true);

    $storedRelativePath = $watermarkService->applyWatermark(
        imageFile: $uploadedFile,
        vehicleReg: 'DHA-METRO-GA-16-0158',
        driverName: 'Md. Robiul',
        latitude: 24.1036,
        longitude: 90.3991,
        locationName: 'BK Bari Factory Plant, Gazipur'
    );

    expect($storedRelativePath)->toBeString()
        ->and($storedRelativePath)->toContain('mobile_proofs');

    $fullPath = storage_path('app/public/'.$storedRelativePath);
    expect(file_exists($fullPath))->toBeTrue();

    // Verify metadata generation
    $metadata = $watermarkService->generateProofMetadata(
        vehicleReg: 'DHA-METRO-GA-16-0158',
        driverName: 'Md. Robiul',
        latitude: 24.1036,
        longitude: 90.3991,
        locationName: 'BK Bari Factory Plant, Gazipur'
    );

    expect($metadata)->toHaveKeys(['captured_at', 'vehicle_registration', 'driver_name', 'latitude', 'tamper_hash'])
        ->and($metadata['vehicle_registration'])->toBe('DHA-METRO-GA-16-0158')
        ->and($metadata['driver_name'])->toBe('Md. Robiul');

    @unlink($tempFile);
    @unlink($fullPath);
});

test('mobile api endpoints handle outbox push, pull, gps ping and geofences list', function () {
    // 1. GET /api/v1/mobile/geofences
    $geoResponse = $this->getJson('/api/v1/mobile/geofences');
    $geoResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('geofences.GZP.name', 'BK Bari Factory Plant, Gazipur')
        ->assertJsonPath('geofences.HO.name', 'Corporate Head Office, Baridhara DOHS, Dhaka')
        ->assertJsonPath('geofences.CGZP.name', 'CAKL, Bhobanipur, Gazipur');

    // 2. GET /api/v1/mobile/sync/pull
    $pullResponse = $this->getJson('/api/v1/mobile/sync/pull?device_id=MOB-TEST-002&driver_id='.$this->driver->id);
    $pullResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.driver.name', 'Md. Robiul')
        ->assertJsonPath('data.driver.vehicle_registration', 'DHA-METRO-GA-16-0158')
        ->assertJsonPath('data.quota.monthly_quota_liters', 150);

    // 3. POST /api/v1/mobile/sync/push
    $pushPayload = [
        'device_id' => 'MOB-TEST-002',
        'driver_id' => $this->driver->id,
        'items' => [
            [
                'idempotency_key' => 'PUSH-API-001',
                'action_type' => 'GATE_OUT',
                'payload' => [
                    'vehicle_id' => $this->vehicle->id,
                    'out_odometer' => 50200,
                    'destination' => 'Chittagong Port',
                    'purpose' => 'Export Delivery',
                ],
                'client_recorded_at' => now()->toIso8601String(),
            ],
        ],
    ];

    $pushResponse = $this->postJson('/api/v1/mobile/sync/push', $pushPayload);
    $pushResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('processed_count', 1)
        ->assertJsonPath('results.0.status', 'SYNCED');

    // 4. POST /api/v1/mobile/gps/ping
    $pingPayload = [
        'vehicle_id' => $this->vehicle->id,
        'driver_id' => $this->driver->id,
        'latitude' => 24.1036,
        'longitude' => 90.3991,
        'speed_kmh' => 52.0,
        'battery_level' => 92,
        'is_mock_location' => false,
    ];

    $pingResponse = $this->postJson('/api/v1/mobile/gps/ping', $pingPayload);
    $pingResponse->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('nearest_geofence', 'GZP')
        ->assertJsonPath('is_within_geofence', true);
});

test('mobile terminal livewire component renders and executes gate actions', function () {
    Livewire::test(MobileTerminal::class)
        ->assertSuccessful()
        ->assertSee('DHA-METRO-GA-16-0158')
        ->set('selectedVehicleId', $this->vehicle->id)
        ->set('odometerReading', 50300)
        ->set('destination', 'CAKL Bhobanipur')
        ->call('submitGateOut')
        ->assertSet('feedbackType', 'success');

    expect($this->vehicle->fresh()->status)->toBe('ON_TRIP');

    Livewire::test(MobileTerminal::class)
        ->assertSuccessful()
        ->set('selectedVehicleId', $this->vehicle->id)
        ->set('odometerReading', 50350)
        ->call('submitGateIn')
        ->assertSet('feedbackType', 'success');

    expect($this->vehicle->fresh()->status)->toBe('AVAILABLE')
        ->and((int) $this->vehicle->fresh()->current_odometer)->toBe(50350);
});
