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
        'name', 'email', 'mobile', 'password', 'role', 'role_id', 'plant_id', 'department', 'shift_id', 'is_active',
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

    public function assignedShift(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function shiftRosters(): HasMany
    {
        return $this->hasMany(ShiftRoster::class);
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

    /**
     * Get the staff member's rostered shift for a given date (defaults to today).
     */
    public function getCurrentRosteredShift(?\Carbon\Carbon $date = null): ?Shift
    {
        $targetDate = $date ? $date->toDateString() : today()->toDateString();
        $roster = $this->shiftRosters()->whereDate('roster_date', $targetDate)->with('shift')->first();
        if ($roster && $roster->shift) {
            return $roster->shift;
        }

        return $this->assignedShift;
    }

    /**
     * Check if the user is currently on duty per Section 6.6.
     * Admin and Supervisor/Manager are exempt from shift restrictions.
     */
    public function isOnDuty(): bool
    {
        // Admin and Supervisor/Manager exempt per BRS Section 6.6 requirement 4
        if (in_array($this->role, ['superadmin', 'admin', 'supervisor'])) {
            return true;
        }

        $activeShift = Shift::current();
        if (!$activeShift) {
            return true; // No active shift defined or off-hours
        }

        $myShift = $this->getCurrentRosteredShift();
        if (!$myShift) {
            return true; // Unassigned staff can cover any active shift
        }

        return (int) $myShift->id === (int) $activeShift->id;
    }
}
