<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostAllocation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total_km_run' => 'decimal:2',
            'total_fuel_cost' => 'decimal:2',
            'total_maintenance_cost' => 'decimal:2',
            'total_driver_cost' => 'decimal:2',
            'total_toll_and_operating_cost' => 'decimal:2',
            'grand_total_allocated' => 'decimal:2',
            'approved_at' => 'datetime',
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CostAllocationItem::class);
    }

    /**
     * Recalculate totals from allocation items
     */
    public function recalculateTotals(): void
    {
        $this->total_trips_count = (int) $this->items()->sum('trips_count');
        $this->total_km_run = (float) $this->items()->sum('km_run');
        $this->total_fuel_cost = (float) $this->items()->sum('fuel_cost');
        $this->total_maintenance_cost = (float) $this->items()->sum('maintenance_cost');
        $this->total_toll_and_operating_cost = (float) $this->items()->sum('operating_cost');
        $this->grand_total_allocated = (float) $this->items()->sum('total_allocated_cost') + (float) $this->total_driver_cost;
    }
}
