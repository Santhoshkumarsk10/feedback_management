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

    public const ROLES = ['superadmin', 'admin', 'supervisor', 'staff', 'organizer'];

    protected $fillable = [
        'name', 'email', 'mobile', 'password', 'role', 'role_id', 'plant_id', 'department', 'is_active',
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
    public function plant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

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
}
