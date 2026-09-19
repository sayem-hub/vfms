<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleGateLog extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'gate_out_time' => 'datetime',
            'gate_in_time' => 'datetime',
            'out_odometer' => 'integer',
            'in_odometer' => 'integer',
            'total_km' => 'decimal:2',
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

    public function fixedRoute(): BelongsTo
    {
        return $this->belongsTo(FixedRoute::class);
    }

    public function securityGuard(): BelongsTo
    {
        return $this->belongsTo(User::class, 'security_guard_id');
    }

    /**
     * Compute total kilometers based on odometers
     */
    public function computeTotalKm(): ?float
    {
        if ($this->in_odometer !== null && $this->out_odometer !== null) {
            $diff = max(0, $this->in_odometer - $this->out_odometer);
            $this->total_km = $diff;

            return (float) $diff;
        }

        return null;
    }
}
