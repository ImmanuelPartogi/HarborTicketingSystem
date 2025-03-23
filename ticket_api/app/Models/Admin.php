<?php
// Admin.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Admin role constants
     */
    const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';
    const ROLE_ADMIN = 'ADMIN';
    const ROLE_OPERATOR = 'OPERATOR';

    /**
     * The guard used for authentication
     *
     * @var string
     */
    protected $guard = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    /**
     * Get the refunds processed by this admin.
     *
     * @return HasMany
     */
    public function refundsProcessed(): HasMany
    {
        return $this->hasMany(Refund::class, 'refunded_by');
    }

    /**
     * Check if admin has super admin role
     *
     * @return bool
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Check if admin has admin role
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if admin has operator role
     *
     * @return bool
     */
    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR;
    }
}
