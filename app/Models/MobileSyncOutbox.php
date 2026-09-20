<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileSyncOutbox extends Model
{
    use HasFactory;

    protected $table = 'mobile_sync_outbox';

    protected $fillable = [
        'device_id',
        'driver_id',
        'idempotency_key',
        'action_type',
        'payload',
        'client_recorded_at',
        'synced_at',
        'status',
        'retry_count',
        'error_message',
        'server_entity_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'client_recorded_at' => 'datetime',
            'synced_at' => 'datetime',
            'retry_count' => 'integer',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'PENDING');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'FAILED');
    }

    public function scopeByDevice(Builder $query, string $deviceId): Builder
    {
        return $query->where('device_id', $deviceId);
    }

    public function markSynced(?int $serverEntityId = null): void
    {
        $this->update([
            'status' => 'SYNCED',
            'synced_at' => now(),
            'server_entity_id' => $serverEntityId,
            'error_message' => null,
        ]);
    }

    public function markFailed(string $error): void
    {
        $this->increment('retry_count');
        $this->update([
            'status' => 'FAILED',
            'error_message' => $error,
        ]);
    }

    public function markConflict(string $reason): void
    {
        $this->update([
            'status' => 'CONFLICT',
            'error_message' => $reason,
        ]);
    }
}
