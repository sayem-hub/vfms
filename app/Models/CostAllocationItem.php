<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostAllocationItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'km_run' => 'decimal:2',
            'fuel_cost' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
            'operating_cost' => 'decimal:2',
            'total_allocated_cost' => 'decimal:2',
        ];
    }

    public function costAllocation(): BelongsTo
    {
        return $this->belongsTo(CostAllocation::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
