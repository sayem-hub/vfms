<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExportShipmentDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'carton_quantity' => 'integer',
            'cbm_volume' => 'decimal:2',
            'port_arrival_time' => 'datetime',
            'port_release_time' => 'datetime',
            'waiting_hours' => 'decimal:2',
            'free_waiting_hours_allowed' => 'decimal:2',
            'demurrage_charge_per_hour' => 'decimal:2',
            'total_demurrage_payable' => 'decimal:2',
        ];
    }

    public function tripRequisition(): BelongsTo
    {
        return $this->belongsTo(TripRequisition::class);
    }
}
