<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'license_expiry_date' => 'date',
            'salary' => 'decimal:2',
            'is_active' => 'boolean',
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

    public function currentVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'current_vehicle_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    public function expenseSettlements(): HasMany
    {
        return $this->hasMany(TripExpenseSettlement::class);
    }

    public function fixedRoutes(): HasMany
    {
        return $this->hasMany(FixedRoute::class, 'assigned_driver_id');
    }

    public function gateLogs(): HasMany
    {
        return $this->hasMany(VehicleGateLog::class);
    }

    public function gpsPings(): HasMany
    {
        return $this->hasMany(VehicleGpsPing::class);
    }

    public function outboxMessages(): HasMany
    {
        return $this->hasMany(MobileSyncOutbox::class);
    }
}
