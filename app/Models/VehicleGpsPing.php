<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleGpsPing extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_request_id',
        'vehicle_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'heading',
        'accuracy_meters',
        'battery_level',
        'is_mock_location',
        'nearest_geofence',
        'is_within_geofence',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'speed_kmh' => 'float',
            'heading' => 'float',
            'accuracy_meters' => 'float',
            'battery_level' => 'integer',
            'is_mock_location' => 'boolean',
            'is_within_geofence' => 'boolean',
            'recorded_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function tripRequest(): BelongsTo
    {
        return $this->belongsTo(TripRequest::class);
    }
}
