<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'contact_email',
        'contact_phone',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
