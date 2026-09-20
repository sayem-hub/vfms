<?php

use App\Services\Mobile\GeofenceVerificationService;
use App\Services\Mobile\ImageWatermarkService;
use App\Services\Mobile\OfflineSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile Client & Field Hardware API Routes (NZ Group VFMS Mobile)
|--------------------------------------------------------------------------
*/

Route::prefix('v1/mobile')->group(function () {
    // 1. Ingest Offline Outbox Queue Batch
    Route::post('/sync/push', function (Request $request, OfflineSyncService $syncService) {
        $validated = $request->validate([
            'device_id' => 'required|string|max:100',
            'driver_id' => 'nullable|integer|exists:drivers,id',
            'items' => 'required|array|min:1',
            'items.*.idempotency_key' => 'required|string|max:120',
            'items.*.action_type' => 'required|string',
            'items.*.payload' => 'required|array',
            'items.*.client_recorded_at' => 'nullable|string',
        ]);

        $results = $syncService->processBatchOutbox(
            $validated['items'],
            $validated['device_id'],
            $validated['driver_id'] ?? null
        );

        return response()->json([
            'success' => true,
            'processed_count' => count($results),
            'results' => $results,
            'server_time' => now()->toIso8601String(),
        ]);
    })->name('api.mobile.sync.push');

    // 2. Pull Device Offline Bootstrap Cache
    Route::get('/sync/pull', function (Request $request, OfflineSyncService $syncService) {
        $deviceId = (string) $request->query('device_id', 'DEV-UNKNOWN');
        $driverId = $request->query('driver_id') ? (int) $request->query('driver_id') : null;

        $payload = $syncService->generateDevicePullPayload($deviceId, $driverId);

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    })->name('api.mobile.sync.pull');

    // 3. Ingest Live GPS Location Ping
    Route::post('/gps/ping', function (Request $request, GeofenceVerificationService $geofenceService) {
        $validated = $request->validate([
            'vehicle_id' => 'required|integer|exists:vehicles,id',
            'driver_id' => 'nullable|integer|exists:drivers,id',
            'trip_request_id' => 'nullable|integer|exists:trip_requests,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed_kmh' => 'nullable|numeric|min:0',
            'heading' => 'nullable|numeric|between:0,360',
            'accuracy_meters' => 'nullable|numeric|min:0',
            'battery_level' => 'nullable|integer|between:0,100',
            'is_mock_location' => 'nullable|boolean',
        ]);

        $ping = $geofenceService->recordPing(
            vehicleId: (int) $validated['vehicle_id'],
            latitude: (float) $validated['latitude'],
            longitude: (float) $validated['longitude'],
            driverId: isset($validated['driver_id']) ? (int) $validated['driver_id'] : null,
            tripRequestId: isset($validated['trip_request_id']) ? (int) $validated['trip_request_id'] : null,
            speedKmh: (float) ($validated['speed_kmh'] ?? 0),
            heading: isset($validated['heading']) ? (float) $validated['heading'] : null,
            accuracyMeters: (float) ($validated['accuracy_meters'] ?? 0),
            batteryLevel: isset($validated['battery_level']) ? (int) $validated['battery_level'] : null,
            isMockLocation: (bool) ($validated['is_mock_location'] ?? false),
        );

        return response()->json([
            'success' => true,
            'ping_id' => $ping->id,
            'nearest_geofence' => $ping->nearest_geofence,
            'is_within_geofence' => $ping->is_within_geofence,
            'is_mock_location' => $ping->is_mock_location,
        ]);
    })->name('api.mobile.gps.ping');

    // 4. Native Camera Photo Watermarking Endpoint
    Route::post('/photo/watermark', function (Request $request, ImageWatermarkService $watermarkService) {
        $validated = $request->validate([
            'photo' => 'required|image|max:10240', // 10MB max
            'vehicle_reg' => 'required|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:150',
        ]);

        $storedPath = $watermarkService->applyWatermark(
            imageFile: $request->file('photo'),
            vehicleReg: $validated['vehicle_reg'],
            driverName: $validated['driver_name'] ?? null,
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            locationName: $validated['location_name'] ?? null
        );

        $metadata = $watermarkService->generateProofMetadata(
            vehicleReg: $validated['vehicle_reg'],
            driverName: $validated['driver_name'] ?? null,
            latitude: isset($validated['latitude']) ? (float) $validated['latitude'] : null,
            longitude: isset($validated['longitude']) ? (float) $validated['longitude'] : null,
            locationName: $validated['location_name'] ?? null
        );

        return response()->json([
            'success' => true,
            'file_path' => $storedPath,
            'metadata' => $metadata,
        ]);
    })->name('api.mobile.photo.watermark');

    // 5. Official Geofences Reference
    Route::get('/geofences', function (GeofenceVerificationService $geofenceService) {
        return response()->json([
            'success' => true,
            'geofences' => $geofenceService->getOfficialGeofences(),
        ]);
    })->name('api.mobile.geofences');
});
