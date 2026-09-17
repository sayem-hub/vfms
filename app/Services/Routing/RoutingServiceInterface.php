<?php

namespace App\Services\Routing;

interface RoutingServiceInterface
{
    /**
     * Calculate route distance (KM) and expected travel duration (Minutes).
     */
    public function calculateDistanceAndDuration(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng
    ): RouteResult;
}
