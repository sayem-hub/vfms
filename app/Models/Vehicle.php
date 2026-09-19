<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class);
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

    public function fixedRoutes(): HasMany
    {
        return $this->hasMany(FixedRoute::class, 'assigned_vehicle_id');
    }

    public function gateLogs(): HasMany
    {
        return $this->hasMany(VehicleGateLog::class);
    }

    public function isDedicated(): bool
    {
        return $this->usage_category === 'DEDICATED_MANAGEMENT' || ! empty($this->dedicated_to_official);
    }

    public function isStaffBus(): bool
    {
        return $this->usage_category === 'STAFF_COMMUTE_BUS' || $this->vehicle_type === 'STAFF_BUS';
    }

    /**
     * Get active gate log if vehicle is currently punched OUT
     */
    public function activeGateLog(): ?VehicleGateLog
    {
        return $this->gateLogs()
            ->where('status', 'OUT')
            ->latest('id')
            ->first();
    }

    /**
     * Calculate monthly fuel consumed liters for given year and month (default current)
     */
    public function monthlyFuelConsumedLiters(?int $year = null, ?int $month = null): float
    {
        $year = $year ?? Carbon::now()->year;
        $month = $month ?? Carbon::now()->month;

        return (float) $this->fuelLogs()
            ->whereYear('refill_date', $year)
            ->whereMonth('refill_date', $month)
            ->sum('fuel_quantity');
    }

    /**
     * Calculate remaining fuel quota for the month
     */
    public function monthlyFuelQuotaRemaining(?int $year = null, ?int $month = null): ?float
    {
        if ($this->monthly_fuel_quota_liters === null || (float) $this->monthly_fuel_quota_liters <= 0) {
            return null;
        }

        $consumed = $this->monthlyFuelConsumedLiters($year, $month);

        return round((float) $this->monthly_fuel_quota_liters - $consumed, 2);
    }

    /**
     * Calculate fuel quota usage percentage
     */
    public function monthlyFuelQuotaUsagePercent(?int $year = null, ?int $month = null): ?float
    {
        if ($this->monthly_fuel_quota_liters === null || (float) $this->monthly_fuel_quota_liters <= 0) {
            return null;
        }

        $consumed = $this->monthlyFuelConsumedLiters($year, $month);
        $percent = ($consumed / (float) $this->monthly_fuel_quota_liters) * 100;

        return round($percent, 1);
    }
}
