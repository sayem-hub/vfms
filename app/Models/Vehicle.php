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
            'purchase_price' => 'decimal:2',
            'purchase_date' => 'date',
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

    public function costAllocationItems(): HasMany
    {
        return $this->hasMany(CostAllocationItem::class);
    }

    public function gpsPings(): HasMany
    {
        return $this->hasMany(VehicleGpsPing::class);
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

    /**
     * Calculate Lifetime Total Cost of Ownership (TCO) Ledger
     */
    public function lifetimeTco(): array
    {
        $purchasePrice = (float) ($this->purchase_price ?? 0);
        $totalFuelCost = (float) $this->fuelLogs()->sum('total_cost');
        $totalMaintenanceCost = (float) $this->maintenanceRecords()->sum('grand_total_cost');
        $totalComplianceCost = (float) $this->compliances()->sum('renewal_cost');

        // Trip settlements non-fuel expenses
        $tripIds = $this->tripRequests()->pluck('id');
        $totalOperatingExpense = (float) TripExpenseSettlement::whereIn('trip_request_id', $tripIds)
            ->selectRaw('SUM(total_toll_expense + total_parking_expense + total_driver_food_allowance + total_emergency_repair_expense + total_other_expense) as op_cost')
            ->value('op_cost');

        $grandTotalTco = $purchasePrice + $totalFuelCost + $totalMaintenanceCost + $totalComplianceCost + $totalOperatingExpense;
        $totalKm = max(1, (int) $this->current_odometer);
        $lifetimeCpk = round($grandTotalTco / $totalKm, 2);

        return [
            'purchase_price' => $purchasePrice,
            'total_fuel_cost' => $totalFuelCost,
            'total_maintenance_cost' => $totalMaintenanceCost,
            'total_compliance_cost' => $totalComplianceCost,
            'total_operating_expense' => $totalOperatingExpense,
            'grand_total_tco' => $grandTotalTco,
            'total_km' => $totalKm,
            'lifetime_cpk' => $lifetimeCpk,
        ];
    }

    /**
     * Repair vs Replace (মেরামত বনাম নতুন গাড়ি ক্রয়) Advisory Engine
     */
    public function repairVsReplaceStatus(): array
    {
        $oneYearAgo = Carbon::now()->subYear();

        $trailing12MonthMaintenance = (float) $this->maintenanceRecords()
            ->where('service_date', '>=', $oneYearAgo)
            ->sum('grand_total_cost');

        $capitalBenchmark = (float) ($this->purchase_price ?? 2500000); // Default benchmark BDT 25 Lac if unstated
        $maintenanceRatio = ($trailing12MonthMaintenance / max(1, $capitalBenchmark)) * 100;

        // Determine advisory status
        if ($maintenanceRatio >= 35.0 || $trailing12MonthMaintenance >= 500000 || (int) $this->current_odometer >= 300000) {
            $status = 'RECOMMEND_REPLACE';
            $labelBn = '🔴 নতুন গাড়ি ক্রয় সুপারিশকৃত (Replace Recommended)';
            $labelEn = '🔴 Replacement Recommended';
            $color = 'danger';
            if ((int) $this->current_odometer >= 300000) {
                $reason = 'গাড়ির মোট মাইলেজ '.number_format((int) $this->current_odometer).' কিমি অতিক্রান্ত হয়েছে (সীমা: ৩০০,০০০ কিমি)। উচ্চ পরিচালন ব্যয় এড়াতে প্রতিস্থাপন সুপারিশকৃত।';
            } else {
                $reason = 'বিগত ১২ মাসে রক্ষণাবেক্ষণ ব্যয় হয়েছে ৳'.number_format($trailing12MonthMaintenance, 0).' (মূল্যের '.round($maintenanceRatio, 1).'%), যা অর্থনৈতিক সীমার চেয়ে বেশি।';
            }
        } elseif ($maintenanceRatio >= 20.0 || $trailing12MonthMaintenance >= 250000) {
            $status = 'WATCHLIST';
            $labelBn = '🟡 নজরদারিতে রাখুন (High Maintenance Watchlist)';
            $labelEn = '🟡 Watchlist / High Operating Cost';
            $color = 'warning';
            $reason = 'রক্ষণাবেক্ষণ ব্যয় ঊর্ধ্বমুখী (বিগত ১২ মাসে ৳'.number_format($trailing12MonthMaintenance, 0).')। ঘন ঘন পার্টস রিপ্লেসমেন্ট পর্যবেক্ষণ করুন।';
        } else {
            $status = 'ECONOMICAL';
            $labelBn = '🟢 ব্যবহারযোগ্য ও লাভজনক (Economical to Retain)';
            $labelEn = '🟢 Economical to Retain';
            $color = 'success';
            $reason = 'গাড়ির পারফরম্যান্স ও পরিচালন ব্যয় স্বাভাবিক সীমার মধ্যে রয়েছে।';
        }

        return [
            'status' => $status,
            'label_bn' => $labelBn,
            'label_en' => $labelEn,
            'color' => $color,
            'trailing_maintenance' => $trailing12MonthMaintenance,
            'ratio_percentage' => round($maintenanceRatio, 1),
            'reason' => $reason,
        ];
    }
}
