<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function factoryUnits(): HasMany
    {
        return $this->hasMany(FactoryUnit::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class);
    }

    public function costAllocations(): HasMany
    {
        return $this->hasMany(CostAllocation::class);
    }
}
