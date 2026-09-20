<?php

namespace App\Services\Financial;

use App\Models\Vehicle;

class TotalCostOfOwnershipService
{
    /**
     * Get TCO ledger details for a specific vehicle
     */
    public function getVehicleTco(Vehicle $vehicle): array
    {
        $tco = $vehicle->lifetimeTco();
        $advisory = $vehicle->repairVsReplaceStatus();

        return array_merge($tco, [
            'vehicle' => $vehicle,
            'advisory' => $advisory,
        ]);
    }

    /**
     * Get overall fleet TCO metrics
     */
    public function getFleetTcoSummary(): array
    {
        $vehicles = Vehicle::where('is_active', true)->get();

        $totalCapitalValue = 0.0;
        $totalLifetimeFuel = 0.0;
        $totalLifetimeMaintenance = 0.0;
        $totalLifetimeCompliance = 0.0;
        $totalLifetimeOperating = 0.0;
        $totalFleetOdometerKm = 0;

        foreach ($vehicles as $v) {
            $tco = $v->lifetimeTco();
            $totalCapitalValue += $tco['purchase_price'];
            $totalLifetimeFuel += $tco['total_fuel_cost'];
            $totalLifetimeMaintenance += $tco['total_maintenance_cost'];
            $totalLifetimeCompliance += $tco['total_compliance_cost'];
            $totalLifetimeOperating += $tco['total_operating_expense'];
            $totalFleetOdometerKm += (int) $v->current_odometer;
        }

        $grandTotalFleetTco = $totalCapitalValue + $totalLifetimeFuel + $totalLifetimeMaintenance + $totalLifetimeCompliance + $totalLifetimeOperating;
        $lifetimeFleetCpk = round($grandTotalFleetTco / max(1, $totalFleetOdometerKm), 2);

        return [
            'total_vehicles' => $vehicles->count(),
            'total_capital_value' => $totalCapitalValue,
            'total_lifetime_fuel' => $totalLifetimeFuel,
            'total_lifetime_maintenance' => $totalLifetimeMaintenance,
            'total_lifetime_compliance' => $totalLifetimeCompliance,
            'total_lifetime_operating' => $totalLifetimeOperating,
            'grand_total_fleet_tco' => $grandTotalFleetTco,
            'total_fleet_odometer_km' => $totalFleetOdometerKm,
            'lifetime_fleet_cpk' => $lifetimeFleetCpk,
        ];
    }

    /**
     * Generate Repair vs Replace Recommendations across all active fleet vehicles
     */
    public function getRepairVsReplaceRecommendations(): array
    {
        $vehicles = Vehicle::where('is_active', true)->get();

        $economical = [];
        $watchlist = [];
        $recommendReplace = [];

        foreach ($vehicles as $v) {
            $status = $v->repairVsReplaceStatus();
            $data = [
                'vehicle' => $v,
                'registration_no' => $v->registration_no,
                'vehicle_type' => $v->vehicle_type,
                'official' => $v->dedicated_to_official ?? 'Pool / General',
                'current_odometer' => $v->current_odometer,
                'trailing_maintenance' => $status['trailing_maintenance'],
                'ratio_percentage' => $status['ratio_percentage'],
                'status' => $status['status'],
                'label_bn' => $status['label_bn'],
                'label_en' => $status['label_en'],
                'color' => $status['color'],
                'reason' => $status['reason'],
            ];

            if ($status['status'] === 'RECOMMEND_REPLACE') {
                $recommendReplace[] = $data;
            } elseif ($status['status'] === 'WATCHLIST') {
                $watchlist[] = $data;
            } else {
                $economical[] = $data;
            }
        }

        return [
            'recommend_replace' => $recommendReplace,
            'watchlist' => $watchlist,
            'economical' => $economical,
            'total_analyzed' => $vehicles->count(),
            'needs_attention_count' => count($recommendReplace) + count($watchlist),
        ];
    }
}
