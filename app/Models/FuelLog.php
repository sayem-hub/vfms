<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'refill_date' => 'datetime',
            'odometer_reading' => 'integer',
            'fuel_quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'km_since_last_refill' => 'integer',
            'calculated_km_per_liter' => 'decimal:2',
            'is_efficiency_anomaly' => 'boolean',
        ];
    }

    public function tripRequisition(): BelongsTo
    {
        return $this->belongsTo(TripRequisition::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
