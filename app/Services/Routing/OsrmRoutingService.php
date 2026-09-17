<?php

namespace App\Services\Routing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class OsrmRoutingService implements RoutingServiceInterface
{
    protected string $baseUrl;

    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://router.project-osrm.org', '/');
        $this->timeout = (int) ($config['timeout'] ?? 10);
    }

    public function calculateDistanceAndDuration(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng
    ): RouteResult {
        // OSRM requires: {longitude},{latitude};{longitude},{latitude}
        $coordinates = sprintf('%.6f,%.6f;%.6f,%.6f', $originLng, $originLat, $destLng, $destLat);
        $url = "{$this->baseUrl}/route/v1/driving/{$coordinates}?overview=false";

        try {
            $response = Http::timeout($this->timeout)->get($url);

            if ($response->successful() && isset($response['code']) && $response['code'] === 'Ok') {
                $route = $response['routes'][0] ?? null;
                if ($route) {
                    $distanceMeters = (float) $route['distance'];
                    $durationSeconds = (float) $route['duration'];

                    return RouteResult::success(
                        distanceKm: $distanceMeters / 1000,
                        durationMinutes: $durationSeconds / 60,
                        driverUsed: 'osrm',
                        rawResponse: $response->json()
                    );
                }
            }

            Log::warning('OSRM returned non-OK response', ['url' => $url, 'body' => $response->body()]);
        } catch (Throwable $e) {
            Log::warning('OSRM routing request failed, falling back to Haversine', [
                'error' => $e->getMessage(),
                'origin' => [$originLat, $originLng],
                'destination' => [$destLat, $destLng],
            ]);
        }

        // Fallback: Haversine distance with Bangladesh road tortuosity factor (approx 1.3x straight line)
        $straightKm = $this->haversineDistance($originLat, $originLng, $destLat, $destLng);
        $estimatedRoadKm = $straightKm * 1.30;
        $estimatedMinutes = ($estimatedRoadKm / 35.0) * 60; // Estimated 35 km/h average speed in BD traffic

        return RouteResult::success(
            distanceKm: $estimatedRoadKm,
            durationMinutes: $estimatedMinutes,
            driverUsed: 'osrm_haversine_fallback',
            rawResponse: ['note' => 'Estimated via Haversine + road factor due to OSRM unavailability']
        );
    }

    /**
     * Calculate straight-line distance via Haversine formula in kilometers.
     */
    protected function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }
}
