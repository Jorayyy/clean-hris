<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'employee_id', 'accessible_sites', 'can_access_all_sites'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
            'accessible_sites' => 'array',
            'can_access_all_sites' => 'boolean',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function normalizedRole(): ?string
    {
        return $this->role ? strtolower(trim((string) $this->role)) : null;
    }

    public function hasPortalRole(array $roles): bool
    {
        $currentRole = $this->normalizedRole();
        $normalizedRoles = array_map(static fn ($role) => strtolower(trim((string) $role)), $roles);

        if ($currentRole && in_array($currentRole, $normalizedRoles, true)) {
            return true;
        }

        return $this->getRoleNames()
            ->map(static fn ($role) => strtolower(trim((string) $role)))
            ->intersect($normalizedRoles)
            ->isNotEmpty();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasPortalRole(['superadmin', 'super-admin', 'super admin'])
            || $this->hasAnyRole(['Super Admin', 'super-admin']);
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->isSuperAdmin();
    }

    public function isAdmin()
    {
        return $this->isSuperAdmin()
            || $this->hasPortalRole(['admin'])
            || $this->hasAnyRole(['HR Admin', 'Accounting Admin', 'Admin', 'admin']);
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->isAdmin();
    }

    public function isEmployee(): bool
    {
        return $this->hasPortalRole(['employee'])
            || $this->hasAnyRole(['Employee', 'employee']);
    }

    public function getIsEmployeeAttribute(): bool
    {
        return $this->isEmployee();
    }

    /**
     * Get the user's display name (email for clarity).
     */
    public function getDisplayNameAttribute()
    {
        return $this->email;
    }
}
