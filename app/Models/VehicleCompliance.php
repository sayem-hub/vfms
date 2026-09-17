<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleCompliance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'renewal_cost' => 'decimal:2',
            'alert_60d_sent_at' => 'datetime',
            'alert_30d_sent_at' => 'datetime',
            'alert_15d_sent_at' => 'datetime',
            'alert_7d_sent_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function daysRemaining(): int
    {
        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }
}
