<?php

namespace App\Services\Financial;

use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequest;
use App\Models\Vehicle;
use App\Models\VehicleGateLog;
use Carbon\Carbon;

class CostPerKmService
{
    /**
     * Calculate Cost Per Kilometer (CPK) for a specific vehicle over a date range
     */
    public function calculateVehicleCpk(Vehicle $vehicle, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        // 1. Total KM in period (from trip requests and gate logs)
        $tripKm = (float) TripRequest::where('vehicle_id', $vehicle->id)
            ->whereBetween('scheduled_start_time', [$startDate, $endDate])
            ->whereIn('status', ['COMPLETED', 'GATE_IN'])
            ->sum('claimed_distance_km');

        $gateLogKm = (float) VehicleGateLog::where('vehicle_id', $vehicle->id)
            ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', 'COMPLETED')
            ->sum('total_km');

        $totalKm = $tripKm + $gateLogKm;

        // Fallback to odometer diff if no trips logged but vehicle has moved
        if ($totalKm <= 0 && $vehicle->current_odometer > 0) {
            $totalKm = max(1, (float) $vehicle->current_odometer);
        }

        // 2. Fuel Expense
        $fuelExpense = (float) FuelLog::where('vehicle_id', $vehicle->id)
            ->whereBetween('refill_date', [$startDate, $endDate])
            ->sum('total_cost');

        // 3. Maintenance Expense
        $maintenanceExpense = (float) MaintenanceRecord::where('vehicle_id', $vehicle->id)
            ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->sum('grand_total_cost');

        // 4. Operating Expenses (Tolls, Parking, DA, Emergency Repair)
        $tripIds = TripRequest::where('vehicle_id', $vehicle->id)
            ->whereBetween('scheduled_start_time', [$startDate, $endDate])
            ->pluck('id');

        $operatingExpense = (float) TripExpenseSettlement::whereIn('trip_request_id', $tripIds)
            ->selectRaw('SUM(total_toll_expense + total_parking_expense + total_driver_food_allowance + total_emergency_repair_expense + total_other_expense) as op_cost')
            ->value('op_cost');

        // 5. Driver Wages (pro-rated monthly salary of primary assigned driver)
        $driverSalary = (float) ($vehicle->drivers()->first()?->salary ?? 0);

        // 6. Fixed Costs (rental or depreciation)
        $fixedCost = (float) ($vehicle->monthly_fixed_cost ?? 0);

        $grandTotalCost = $fuelExpense + $maintenanceExpense + $operatingExpense + $driverSalary + $fixedCost;
        $cpk = round($grandTotalCost / max(1, $totalKm), 2);

        return [
            'vehicle_id' => $vehicle->id,
            'registration_no' => $vehicle->registration_no,
            'vehicle_type' => $vehicle->vehicle_type,
            'usage_category' => $vehicle->usage_category,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_km' => $totalKm,
            'fuel_expense' => $fuelExpense,
            'maintenance_expense' => $maintenanceExpense,
            'operating_expense' => $operatingExpense,
            'driver_salary' => $driverSalary,
            'fixed_cost' => $fixedCost,
            'grand_total_cost' => $grandTotalCost,
            'cpk' => $cpk,
        ];
    }

    /**
     * Calculate fleet-wide Cost Per Kilometer (CPK) metrics
     */
    public function calculateFleetCpk(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $vehicles = Vehicle::where('is_active', true)->get();

        $totalFleetKm = 0.0;
        $totalFuelCost = 0.0;
        $totalMaintenanceCost = 0.0;
        $totalOperatingCost = 0.0;
        $totalDriverCost = 0.0;
        $totalFixedCost = 0.0;

        foreach ($vehicles as $v) {
            $stats = $this->calculateVehicleCpk($v, $startDate, $endDate);
            $totalFleetKm += $stats['total_km'];
            $totalFuelCost += $stats['fuel_expense'];
            $totalMaintenanceCost += $stats['maintenance_expense'];
            $totalOperatingCost += $stats['operating_expense'];
            $totalDriverCost += $stats['driver_salary'];
            $totalFixedCost += $stats['fixed_cost'];
        }

        $grandTotalFleetSpend = $totalFuelCost + $totalMaintenanceCost + $totalOperatingCost + $totalDriverCost + $totalFixedCost;
        $fleetAvgCpk = round($grandTotalFleetSpend / max(1, $totalFleetKm), 2);

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'active_vehicles_count' => $vehicles->count(),
            'total_fleet_km' => $totalFleetKm,
            'total_fuel_cost' => $totalFuelCost,
            'total_maintenance_cost' => $totalMaintenanceCost,
            'total_operating_cost' => $totalOperatingCost,
            'total_driver_cost' => $totalDriverCost,
            'total_fixed_cost' => $totalFixedCost,
            'grand_total_fleet_spend' => $grandTotalFleetSpend,
            'fleet_avg_cpk' => $fleetAvgCpk,
        ];
    }

    /**
     * Benchmarks CPK across different vehicle types
     */
    public function calculateCategoryBenchmark(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $categories = ['SEDAN_CAR', 'MICROBUS', 'STAFF_BUS', 'COVERED_VAN_5T', 'AMBULANCE'];
        $benchmark = [];

        foreach ($categories as $cat) {
            $vehicles = Vehicle::where('is_active', true)
                ->where(function ($q) use ($cat) {
                    $q->where('vehicle_type', $cat)
                        ->orWhere('vehicle_type', 'like', "%{$cat}%");
                })
                ->get();

            $catKm = 0.0;
            $catSpend = 0.0;

            foreach ($vehicles as $v) {
                $vStats = $this->calculateVehicleCpk($v, $startDate, $endDate);
                $catKm += $vStats['total_km'];
                $catSpend += $vStats['grand_total_cost'];
            }

            $catCpk = round($catSpend / max(1, $catKm), 2);

            $benchmark[$cat] = [
                'count' => $vehicles->count(),
                'total_km' => $catKm,
                'total_spend' => $catSpend,
                'cpk' => $catCpk,
            ];
        }

        return $benchmark;
    }
}
