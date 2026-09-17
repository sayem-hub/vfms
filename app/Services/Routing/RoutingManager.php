<?php

namespace App\Services\Routing;

use InvalidArgumentException;

class RoutingManager
{
    /**
     * Cache of resolved driver instances.
     */
    protected array $drivers = [];

    /**
     * Get a driver instance by name, or default driver.
     */
    public function driver(?string $driver = null): RoutingServiceInterface
    {
        $driver = $driver ?: config('vfms.routing.default', 'osrm');

        if (! isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Create a new driver instance.
     */
    protected function createDriver(string $driver): RoutingServiceInterface
    {
        return match ($driver) {
            'osrm' => new OsrmRoutingService(config('vfms.routing.drivers.osrm', [])),
            'google_maps' => new GoogleMapsRoutingService(config('vfms.routing.drivers.google_maps', [])),
            default => throw new InvalidArgumentException("Unsupported routing driver: [{$driver}]"),
        };
    }

    /**
     * Dynamically call methods on the default driver.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
