<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;

    public const ROLES = ['superadmin', 'admin', 'coordinator', 'supervisor', 'staff', 'organizer'];

    protected $fillable = [
        'name', 'email', 'mobile', 'password', 'role', 'role_id', 'department', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (User $user) {
            if (!empty($user->role)) {
                $spatieRole = $user->role === 'organizer' ? 'staff' : $user->role;
                try {
                    if (Role::where('name', $spatieRole)->exists() && !$user->hasRole($spatieRole)) {
                        $user->syncRoles([$spatieRole]);
                    } elseif (Role::where('name', $user->role)->exists() && !$user->hasRole($user->role)) {
                        $user->syncRoles([$user->role]);
                    }
                } catch (\Throwable $e) {
                    // Ignore during migration or uninitialized tables
                }
            }
        });
    }

    // ---- JWT ----
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return ['role' => $this->role];
    }

    // ---- Relations ----

    public function roleModel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function visitsAsOrganizer(): HasMany
    {
        return $this->hasMany(Visit::class, 'organizer_id');
    }

    public function feedbacksReceived(): HasMany
    {
        return $this->hasMany(Feedback::class, 'organizer_id');
    }

    /**
     * Check if the user is currently on duty.
     * All active users are considered on duty.
     */
    public function isOnDuty(): bool
    {
        return (bool) $this->is_active;
    }
}
