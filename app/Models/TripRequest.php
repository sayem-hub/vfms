<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripRequest extends Model
{
    use HasFactory;

    protected $table = 'trip_requests';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'scheduled_start_time' => 'datetime',
            'scheduled_end_time' => 'datetime',
            'actual_start_time' => 'datetime',
            'actual_end_time' => 'datetime',
            'start_odometer' => 'integer',
            'end_odometer' => 'integer',
            'claimed_distance_km' => 'decimal:2',
            'expected_distance_km' => 'decimal:2',
            'distance_variance_percentage' => 'decimal:2',
            'is_distance_anomaly' => 'boolean',
            'origin_latitude' => 'decimal:7',
            'origin_longitude' => 'decimal:7',
            'destination_latitude' => 'decimal:7',
            'destination_longitude' => 'decimal:7',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function factoryUnit(): BelongsTo
    {
        return $this->belongsTo(FactoryUnit::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function anomalyReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anomaly_reviewed_by');
    }

    public function exportShipmentDetail(): HasOne
    {
        return $this->hasOne(ExportShipmentDetail::class, 'trip_request_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class, 'trip_request_id');
    }

    public function tripExpenseSettlement(): HasOne
    {
        return $this->hasOne(TripExpenseSettlement::class, 'trip_request_id');
    }
}
