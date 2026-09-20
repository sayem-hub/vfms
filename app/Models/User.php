<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property string|null $employee_id
 * @property string|null $phone
 * @property string|null $pin
 * @property bool $is_active
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'role', 'employee_id', 'phone', 'pin', 'is_active', 'password', 'preferred_locale'])]
#[Hidden(['password', 'pin', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['ADMIN', 'TRANSPORT_OFFICER'], true);
    }

    public function isDriver(): bool
    {
        return $this->role === 'DRIVER';
    }

    public function isSecurityGuard(): bool
    {
        return $this->role === 'SECURITY_GUARD';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN';
    }

    public function isTransportOfficer(): bool
    {
        return $this->role === 'TRANSPORT_OFFICER';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'EMPLOYEE';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class, 'requester_id');
    }

    public function transportPreRequisitions(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'transport_requester_id');
    }

    public function adminApprovedMaintenance(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'admin_head_id');
    }

    public function erpTaggedMaintenance(): HasMany
    {
        return $this->hasMany(MaintenanceRecord::class, 'erp_requisition_tagged_by');
    }

    public function securityGateLogs(): HasMany
    {
        return $this->hasMany(VehicleGateLog::class, 'security_guard_id');
    }
}
