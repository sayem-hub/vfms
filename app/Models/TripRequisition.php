<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripRequisition extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'origin_latitude' => 'float',
            'origin_longitude' => 'float',
            'destination_latitude' => 'float',
            'destination_longitude' => 'float',
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
            'erp_synced_at' => 'datetime',
            'erp_sync_payload' => 'array',
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
        return $this->hasOne(ExportShipmentDetail::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    public function expenseSettlement(): HasOne
    {
        return $this->hasOne(TripExpenseSettlement::class);
    }
}
