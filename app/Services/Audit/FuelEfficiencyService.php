<?php

namespace App\Services\Audit;

use App\Models\FuelLog;
use App\Models\Vehicle;

class FuelEfficiencyService
{
    /**
     * Process and audit a fuel log record.
     */
    public function auditFuelLog(FuelLog $fuelLog): FuelLog
    {
        $vehicle = $fuelLog->vehicle ?: Vehicle::find($fuelLog->vehicle_id);

        if (! $vehicle) {
            $fuelLog->save();

            return $fuelLog;
        }

        // 1. Find previous fuel log for this vehicle before current refill date
        $previousLog = FuelLog::where('vehicle_id', $fuelLog->vehicle_id)
            ->where('id', '!=', $fuelLog->id ?? 0)
            ->where('odometer_reading', '<', $fuelLog->odometer_reading)
            ->orderByDesc('odometer_reading')
            ->first();

        if ($previousLog && $fuelLog->fuel_quantity > 0) {
            $kmDelta = $fuelLog->odometer_reading - $previousLog->odometer_reading;
            $fuelLog->km_since_last_refill = $kmDelta;

            $calculatedKmPerLiter = $kmDelta / (float) $fuelLog->fuel_quantity;
            $fuelLog->calculated_km_per_liter = round($calculatedKmPerLiter, 2);

            // 2. Anomaly Check against vehicle expected benchmark
            $benchmark = (float) $vehicle->expected_km_per_liter;
            if ($benchmark > 0) {
                $dropThreshold = config('vfms.fuel.drop_alert_percentage', 20.0);
                $dropPercentage = (($benchmark - $calculatedKmPerLiter) / $benchmark) * 100;

                // If efficiency is dropped by more than threshold (e.g. >20% worse than expected)
                $fuelLog->is_efficiency_anomaly = ($dropPercentage >= $dropThreshold);
            }
        }

        $fuelLog->save();

        // 3. Update vehicle current odometer if higher
        if ($fuelLog->odometer_reading > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $fuelLog->odometer_reading]);
        }

        return $fuelLog;
    }
}
