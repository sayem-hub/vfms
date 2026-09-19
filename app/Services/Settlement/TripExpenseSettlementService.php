<?php

namespace App\Services\Settlement;

use App\Models\FuelLog;
use App\Models\TripExpenseSettlement;

class TripExpenseSettlementService
{
    /**
     * Create or update trip settlement and sync fuel expenses from trip's fuel logs.
     */
    public function syncAndCalculate(TripExpenseSettlement $settlement): TripExpenseSettlement
    {
        // 1. Auto-sum fuel expenses logged under this trip request if applicable
        if ($settlement->trip_request_id) {
            $totalFuel = FuelLog::where('trip_request_id', $settlement->trip_request_id)
                ->sum('total_cost');

            if ($totalFuel > 0) {
                $settlement->total_fuel_expense = $totalFuel;
            }
        }

        // 2. Recalculate totals
        $settlement->recalculate();
        $settlement->save();

        return $settlement;
    }

    /**
     * Mark settlement as audited by transport supervisor.
     */
    public function markAsAudited(TripExpenseSettlement $settlement): TripExpenseSettlement
    {
        $settlement->status = 'AUDITED_BY_TRANSPORT';
        $settlement->save();

        return $settlement;
    }

    /**
     * Settle cash by cashier (driver refund or payout).
     */
    public function settleByCashier(TripExpenseSettlement $settlement, int $cashierUserId): TripExpenseSettlement
    {
        $settlement->cashier_settled_by = $cashierUserId;
        $settlement->settled_at = now();
        $settlement->status = 'SETTLED_BY_CASHIER';
        $settlement->save();

        return $settlement;
    }
}
