<?php

namespace App\Services\Routing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GoogleMapsRoutingService implements RoutingServiceInterface
{
    protected string $apiKey;

    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->apiKey = (string) ($config['api_key'] ?? '');
        $this->timeout = (int) ($config['timeout'] ?? 10);
    }

    public function calculateDistanceAndDuration(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng
    ): RouteResult {
        if (empty($this->apiKey)) {
            return RouteResult::failure('google_maps', 'Google Maps API key is not configured.');
        }

        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json';
        $params = [
            'origins' => "{$originLat},{$originLng}",
            'destinations' => "{$destLat},{$destLng}",
            'mode' => 'driving',
            'key' => $this->apiKey,
        ];

        try {
            $response = Http::timeout($this->timeout)->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'OK' && isset($data['rows'][0]['elements'][0])) {
                    $element = $data['rows'][0]['elements'][0];
                    if (($element['status'] ?? '') === 'OK') {
                        $distanceMeters = (float) $element['distance']['value'];
                        $durationSeconds = (float) $element['duration']['value'];

                        return RouteResult::success(
                            distanceKm: $distanceMeters / 1000,
                            durationMinutes: $durationSeconds / 60,
                            driverUsed: 'google_maps',
                            rawResponse: $data
                        );
                    }
                }
            }

            Log::error('Google Maps Distance Matrix failed', ['response' => $response->body()]);

            return RouteResult::failure('google_maps', 'Google Maps returned no route: '.($response->json('error_message') ?? 'Unknown error'));
        } catch (Throwable $e) {
            Log::error('Google Maps API Exception', ['message' => $e->getMessage()]);

            return RouteResult::failure('google_maps', $e->getMessage());
        }
    }
}
