<?php

namespace App\Services\Routing;

class RouteResult
{
    public function __construct(
        public readonly bool $isSuccessful,
        public readonly float $distanceKm,
        public readonly float $durationMinutes,
        public readonly string $driverUsed,
        public readonly ?string $errorMessage = null,
        public readonly array $rawResponse = []
    ) {}

    public static function failure(string $driverUsed, string $errorMessage): self
    {
        return new self(
            isSuccessful: false,
            distanceKm: 0.0,
            durationMinutes: 0.0,
            driverUsed: $driverUsed,
            errorMessage: $errorMessage
        );
    }

    public static function success(
        float $distanceKm,
        float $durationMinutes,
        string $driverUsed,
        array $rawResponse = []
    ): self {
        return new self(
            isSuccessful: true,
            distanceKm: round($distanceKm, 2),
            durationMinutes: round($durationMinutes, 1),
            driverUsed: $driverUsed,
            errorMessage: null,
            rawResponse: $rawResponse
        );
    }
}
