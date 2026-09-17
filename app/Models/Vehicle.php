<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'monthly_fuel_quota_liters' => 'decimal:2',
            'monthly_fixed_cost' => 'decimal:2',
            'rate_per_km' => 'decimal:2',
            'current_odometer' => 'integer',
            'fuel_capacity_liters' => 'decimal:2',
            'expected_km_per_liter' => 'decimal:2',
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

    public function compliances(): HasMany
    {
        return $this->hasMany(VehicleCompliance::class);
    }

    public function tripRequisitions(): HasMany
    {
        return $this->hasMany(TripRequisition::class);
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class);
    }

    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class, 'current_vehicle_id');
    }
}
