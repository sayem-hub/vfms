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
            'estimated_cost' => 'decimal:2',
            'parts_total_cost' => 'decimal:2',
            'labor_total_cost' => 'decimal:2',
            'grand_total_cost' => 'decimal:2',
            'admin_approved_at' => 'datetime',
            'erp_requisition_date' => 'date',
            'erp_requisition_tagged_at' => 'datetime',
            'needs_vendor_repair_gatepass' => 'boolean',
            'parts_sent_to_vendor_at' => 'datetime',
            'parts_returned_from_vendor_at' => 'datetime',
            'requires_old_parts_surrender' => 'boolean',
            'is_old_parts_surrendered' => 'boolean',
            'store_acknowledged_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function transportRequester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transport_requester_id');
    }

    public function adminHead(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_head_id');
    }

    public function erpTaggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'erp_requisition_tagged_by');
    }

    public function storeAcknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_acknowledged_by');
    }

    public function scrapPartsSurrenders(): HasMany
    {
        return $this->hasMany(ScrapPartsSurrender::class);
    }

    public function isApprovedByAdmin(): bool
    {
        return $this->admin_approval_status === 'APPROVED_BY_ADMIN';
    }

    public function isErpTagged(): bool
    {
        return ! empty($this->erp_requisition_no);
    }
}
