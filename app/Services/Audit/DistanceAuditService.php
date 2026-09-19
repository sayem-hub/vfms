<?php

namespace App\Services\Audit;

use App\Models\TripRequest;
use App\Services\Routing\RoutingManager;

class DistanceAuditService
{
    public function __construct(
        protected RoutingManager $routingManager
    ) {}

    /**
     * Audit distance for a trip request.
     * Compares claimed odometer difference against routed map distance.
     */
    public function auditTrip(TripRequest $trip): TripRequest
    {
        // 1. Compute claimed distance
        if ($trip->start_odometer !== null && $trip->end_odometer !== null) {
            $trip->claimed_distance_km = max(0, $trip->end_odometer - $trip->start_odometer);
        }

        // 2. Compute expected distance if coordinates exist
        if ($trip->origin_latitude && $trip->origin_longitude && $trip->destination_latitude && $trip->destination_longitude) {
            $routeResult = $this->routingManager->calculateDistanceAndDuration(
                originLat: (float) $trip->origin_latitude,
                originLng: (float) $trip->origin_longitude,
                destLat: (float) $trip->destination_latitude,
                destLng: (float) $trip->destination_longitude
            );

            if ($routeResult->isSuccessful) {
                $trip->expected_distance_km = $routeResult->distanceKm;
            }
        }

        // 3. Compute variance and check anomaly threshold
        if ($trip->claimed_distance_km && $trip->expected_distance_km && $trip->expected_distance_km > 0) {
            $variance = (($trip->claimed_distance_km - $trip->expected_distance_km) / $trip->expected_distance_km) * 100;
            $trip->distance_variance_percentage = round($variance, 2);

            $threshold = config('vfms.routing.anomaly_threshold_percentage', 15.0);
            $trip->is_distance_anomaly = ($trip->distance_variance_percentage > $threshold);
        }

        $trip->save();

        return $trip;
    }
}
