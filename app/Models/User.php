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

    public function getIsSuperAdminAttribute()
    {
        return $this->role === 'super-admin' || 
               $this->hasAnyRole(['Super Admin', 'super-admin']);
    }

    public function isAdmin()
    {
        return $this->is_super_admin || 
               $this->role === 'admin' || 
               $this->hasAnyRole(['HR Admin', 'Accounting Admin', 'Admin', 'admin']);
    }

    /**
     * Get the user's display name (email for clarity).
     */
    public function getDisplayNameAttribute()
    {
        return $this->email;
    }
}
