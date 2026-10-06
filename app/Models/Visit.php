<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    protected $fillable = [
        'organizer_id', 'visitor_name', 'visitor_designation', 'visitor_mobile', 'visitor_company', 'visitor_email',
        'visit_date', 'purpose',
    ];

    protected $casts = ['visit_date' => 'date'];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
}
