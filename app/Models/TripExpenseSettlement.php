<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripExpenseSettlement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'advance_cash_received' => 'decimal:2',
            'advance_disbursed_at' => 'datetime',
            'total_fuel_expense' => 'decimal:2',
            'total_toll_expense' => 'decimal:2',
            'total_parking_expense' => 'decimal:2',
            'total_driver_food_allowance' => 'decimal:2',
            'total_emergency_repair_expense' => 'decimal:2',
            'total_other_expense' => 'decimal:2',
            'total_actual_expense' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'settled_at' => 'datetime',
        ];
    }

    public function tripRequest(): BelongsTo
    {
        return $this->belongsTo(TripRequest::class, 'trip_request_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function advanceReceivedFrom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advance_received_from_user_id');
    }

    public function cashierSettledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_settled_by');
    }

    /**
     * Recalculate total actual expenses and net balance amount.
     */
    public function recalculate(): void
    {
        $this->total_actual_expense =
            $this->total_fuel_expense +
            $this->total_toll_expense +
            $this->total_parking_expense +
            $this->total_driver_food_allowance +
            $this->total_emergency_repair_expense +
            $this->total_other_expense;

        $this->balance_amount = $this->advance_cash_received - $this->total_actual_expense;
    }
}
