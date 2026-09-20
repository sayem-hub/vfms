<?php

namespace App\Services\Financial;

use App\Models\Company;
use App\Models\CostAllocation;
use App\Models\CostAllocationItem;
use App\Models\FuelLog;
use App\Models\MaintenanceRecord;
use App\Models\TripExpenseSettlement;
use App\Models\TripRequest;
use App\Models\Vehicle;
use App\Models\VehicleGateLog;
use Carbon\Carbon;

class CostAllocationService
{
    /**
     * Calculate inter-company cost allocation matrix for a given month (format: YYYY-MM)
     */
    public function calculateMonthlyAllocationMatrix(string $period): array
    {
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        $companies = Company::where('is_active', true)->get();
        $matrix = [];

        foreach ($companies as $company) {
            $companyVehicles = Vehicle::where('company_id', $company->id)->get();
            $vehicleIds = $companyVehicles->pluck('id')->toArray();

            // Trips initiated by this company or using this company's vehicles
            $trips = TripRequest::where(function ($q) use ($company, $vehicleIds) {
                $q->where('company_id', $company->id)
                    ->orWhereIn('vehicle_id', $vehicleIds);
            })
                ->whereBetween('scheduled_start_time', [$startDate, $endDate])
                ->get();

            $tripsCount = $trips->count();
            $tripKm = (float) $trips->sum('claimed_distance_km');

            // Gate logs for this company's vehicles
            $gateLogs = VehicleGateLog::whereIn('vehicle_id', $vehicleIds)
                ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'COMPLETED')
                ->get();

            $gateKm = (float) $gateLogs->sum('total_km');
            $totalKm = $tripKm + $gateKm;

            // Fuel costs in period
            $fuelCost = (float) FuelLog::whereIn('vehicle_id', $vehicleIds)
                ->whereBetween('refill_date', [$startDate, $endDate])
                ->sum('total_cost');

            // Maintenance costs in period
            $maintenanceCost = (float) MaintenanceRecord::whereIn('vehicle_id', $vehicleIds)
                ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('grand_total_cost');

            // Operating expenses (Tolls, Parking, DA, Emergency Repairs)
            $tripIds = $trips->pluck('id')->toArray();
            $operatingCost = (float) TripExpenseSettlement::whereIn('trip_request_id', $tripIds)
                ->selectRaw('SUM(total_toll_expense + total_parking_expense + total_driver_food_allowance + total_emergency_repair_expense + total_other_expense) as op_cost')
                ->value('op_cost');

            // Driver payroll cost for this company
            $driverCost = (float) $company->drivers()->sum('salary');

            $grandTotal = $fuelCost + $maintenanceCost + $operatingCost + $driverCost;

            $matrix[] = [
                'company_id' => $company->id,
                'company_name' => $company->name,
                'company_code' => $company->code,
                'period' => $period,
                'vehicles_count' => $companyVehicles->count(),
                'trips_count' => $tripsCount,
                'total_km' => $totalKm,
                'fuel_cost' => $fuelCost,
                'maintenance_cost' => $maintenanceCost,
                'operating_cost' => $operatingCost,
                'driver_cost' => $driverCost,
                'grand_total' => $grandTotal,
            ];
        }

        return $matrix;
    }

    /**
     * Generate and save a formal Inter-Company Cost Allocation Journal
     */
    public function generateAndSaveJournal(int $companyId, string $period, ?int $factoryUnitId = null, ?int $approvedBy = null, ?string $notes = null): CostAllocation
    {
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        $company = Company::findOrFail($companyId);
        $companyVehicles = Vehicle::where('company_id', $companyId)->get();
        $vehicleIds = $companyVehicles->pluck('id')->toArray();

        // Generate unique journal reference e.g. JRN-202609-NAZ-001
        $cleanPeriod = str_replace('-', '', $period);
        $journalRef = "JRN-{$cleanPeriod}-{$company->code}-".strtoupper(substr(uniqid(), -4));

        $allocation = CostAllocation::create([
            'company_id' => $companyId,
            'factory_unit_id' => $factoryUnitId,
            'allocation_period' => $period,
            'journal_reference_no' => $journalRef,
            'total_driver_cost' => (float) $company->drivers()->sum('salary'),
            'status' => 'FINALIZED',
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now(),
            'notes' => $notes ?? "Monthly transport cost allocation for {$company->name} ({$period})",
        ]);

        // Create itemized records per vehicle
        foreach ($companyVehicles as $v) {
            $trips = TripRequest::where('vehicle_id', $v->id)
                ->whereBetween('scheduled_start_time', [$startDate, $endDate])
                ->get();

            $tripKm = (float) $trips->sum('claimed_distance_km');
            $gateKm = (float) VehicleGateLog::where('vehicle_id', $v->id)
                ->whereBetween('log_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->where('status', 'COMPLETED')
                ->sum('total_km');

            $vKm = $tripKm + $gateKm;

            $vFuel = (float) FuelLog::where('vehicle_id', $v->id)
                ->whereBetween('refill_date', [$startDate, $endDate])
                ->sum('total_cost');

            $vMaint = (float) MaintenanceRecord::where('vehicle_id', $v->id)
                ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
                ->sum('grand_total_cost');

            $tripIds = $trips->pluck('id')->toArray();
            $vOperating = (float) TripExpenseSettlement::whereIn('trip_request_id', $tripIds)
                ->selectRaw('SUM(total_toll_expense + total_parking_expense + total_driver_food_allowance + total_emergency_repair_expense + total_other_expense) as op_cost')
                ->value('op_cost');

            $vTotal = $vFuel + $vMaint + $vOperating;

            CostAllocationItem::create([
                'cost_allocation_id' => $allocation->id,
                'vehicle_id' => $v->id,
                'trips_count' => $trips->count(),
                'km_run' => $vKm,
                'fuel_cost' => $vFuel,
                'maintenance_cost' => $vMaint,
                'operating_cost' => $vOperating,
                'total_allocated_cost' => $vTotal,
            ]);
        }

        $allocation->recalculateTotals();
        $allocation->save();

        return $allocation;
    }
}
