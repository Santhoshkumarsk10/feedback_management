<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'display_name',
        'module',
        'description',
    ];

    /**
     * Readable display title.
     */
    public function getTitleAttribute(): string
    {
        return $this->display_name ?: ucwords(str_replace('-', ' ', $this->name));
    }
}
