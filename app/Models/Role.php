<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'guard_name',
        'slug',
        'description',
        'is_system',
        'is_active',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Role $role) {
            if (empty($role->slug) && !empty($role->name)) {
                $role->slug = \Illuminate\Support\Str::slug($role->name);
            }
        });
    }

    /**
     * Display label for UI representation.
     */
    public function getTitleAttribute(): string
    {
        return $this->display_name ?: ucfirst($this->name);
    }

    /**
     * Users assigned via role_id column directly.
     */
    public function assignedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
