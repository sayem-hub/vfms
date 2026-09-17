<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceRecord extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'odometer_at_service' => 'integer',
            'service_date' => 'date',
            'parts_total_cost' => 'decimal:2',
            'labor_total_cost' => 'decimal:2',
            'grand_total_cost' => 'decimal:2',
            'requires_old_parts_surrender' => 'boolean',
            'is_old_parts_surrendered' => 'boolean',
            'store_acknowledged_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function storeAcknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_acknowledged_by');
    }

    public function scrapPartsSurrenders(): HasMany
    {
        return $this->hasMany(ScrapPartsSurrender::class);
    }
}
