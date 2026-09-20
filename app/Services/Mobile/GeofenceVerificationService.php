<?php

namespace App\Services\Mobile;

use App\Models\VehicleGpsPing;
use Carbon\Carbon;

class GeofenceVerificationService
{
    /**
     * Get official factory and logistics geofences
     */
    public function getOfficialGeofences(): array
    {
        return config('nativephp.official_geofences', [
            'HO' => [
                'name' => 'Corporate Head Office, Baridhara DOHS, Dhaka',
                'lat' => 23.8197,
                'lng' => 90.4143,
                'radius_meters' => 500,
            ],
            'GZP' => [
                'name' => 'BK Bari Factory Plant, Gazipur',
                'lat' => 24.1036,
                'lng' => 90.3991,
                'radius_meters' => 800,
            ],
            'CGZP' => [
                'name' => 'CAKL, Bhobanipur, Gazipur',
                'lat' => 24.1483,
                'lng' => 90.4224,
                'radius_meters' => 800,
            ],
            'AGZP' => [
                'name' => 'BIDC Road, Joydebpur, Gazipur',
                'lat' => 23.9310,
                'lng' => 90.2690,
                'radius_meters' => 600,
            ],
            'CTG' => [
                'name' => 'Chittagong Port Off-Dock Depot',
                'lat' => 22.3167,
                'lng' => 91.8000,
                'radius_meters' => 2000,
            ],
            'AIR' => [
                'name' => 'Dhaka Airport Cargo Village',
                'lat' => 23.8433,
                'lng' => 90.4030,
                'radius_meters' => 1000,
            ],
            'YGZP' => [
                'name' => 'NAZ Yarn Store, Rajabari, Gazipur',
                'lat' => 24.1044,
                'lng' => 90.4961,
                'radius_meters' => 600,
            ],
        ]);
    }

    /**
     * Calculate Great-Circle distance between two coordinates in meters using Haversine formula
     */
    public function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Find the closest official factory or hub geofence for given coordinates
     */
    public function findNearestGeofence(float $latitude, float $longitude): array
    {
        $geofences = $this->getOfficialGeofences();
        $nearest = null;
        $minDistance = INF;
        $nearestCode = null;

        foreach ($geofences as $code => $geo) {
            $dist = $this->calculateDistanceMeters($latitude, $longitude, $geo['lat'], $geo['lng']);
            if ($dist < $minDistance) {
                $minDistance = $dist;
                $nearest = $geo;
                $nearestCode = $code;
            }
        }

        $radius = $nearest['radius_meters'] ?? 500;
        $isWithin = $minDistance <= $radius;

        return [
            'code' => $nearestCode,
            'name' => $nearest['name'] ?? 'Unknown Location',
            'latitude' => $nearest['lat'] ?? 0.0,
            'longitude' => $nearest['lng'] ?? 0.0,
            'distance_meters' => $minDistance,
            'radius_meters' => $radius,
            'is_within' => $isWithin,
        ];
    }

    /**
     * Verify GPS coordinates against geofence and anti-spoof checks
     */
    public function verifyPing(float $latitude, float $longitude, bool $isMockLocation = false): array
    {
        $nearest = $this->findNearestGeofence($latitude, $longitude);
        $isSuspicious = $isMockLocation;
        $warning = null;

        if ($isMockLocation) {
            $warning = '⚠️ Fake/Mock GPS detected on client device!';
        }

        return [
            'is_within_geofence' => $nearest['is_within'],
            'nearest_geofence_code' => $nearest['code'],
            'nearest_geofence_name' => $nearest['name'],
            'distance_meters' => $nearest['distance_meters'],
            'is_mock_location' => $isMockLocation,
            'is_suspicious' => $isSuspicious,
            'warning' => $warning,
        ];
    }

    /**
     * Record a verified vehicle GPS breadcrumb ping
     */
    public function recordPing(
        int $vehicleId,
        float $latitude,
        float $longitude,
        ?int $driverId = null,
        ?int $tripRequestId = null,
        float $speedKmh = 0,
        ?float $heading = null,
        float $accuracyMeters = 0,
        ?int $batteryLevel = null,
        bool $isMockLocation = false,
        ?Carbon $recordedAt = null
    ): VehicleGpsPing {
        $verification = $this->verifyPing($latitude, $longitude, $isMockLocation);

        return VehicleGpsPing::create([
            'trip_request_id' => $tripRequestId,
            'vehicle_id' => $vehicleId,
            'driver_id' => $driverId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'speed_kmh' => $speedKmh,
            'heading' => $heading,
            'accuracy_meters' => $accuracyMeters,
            'battery_level' => $batteryLevel,
            'is_mock_location' => $isMockLocation,
            'nearest_geofence' => $verification['nearest_geofence_code'],
            'is_within_geofence' => $verification['is_within_geofence'],
            'recorded_at' => $recordedAt ?? now(),
        ]);
    }
}
