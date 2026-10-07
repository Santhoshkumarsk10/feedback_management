<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    protected $fillable = [
        'visitor_id', 'visitor_code', 'organizer_id', 'shift_id', 'visitor_name', 'visitor_designation', 'visitor_mobile', 'visitor_company', 'visitor_email',
        'visit_date', 'purpose',
    ];

    protected $casts = ['visit_date' => 'date'];

    protected static function booted()
    {
        static::creating(function (Visit $visit) {
            // Auto link or create Visitor record if not assigned
            if (empty($visit->visitor_id)) {
                $visitor = Visitor::upsertFromExternal([
                    'visitor_id' => $visit->visitor_code,
                    'name' => $visit->visitor_name,
                    'company' => $visit->visitor_company,
                    'designation' => $visit->visitor_designation,
                    'mobile' => $visit->visitor_mobile,
                    'email' => $visit->visitor_email,
                ], 'visit_entry');

                $visit->visitor_id = $visitor->id;
                $visit->visitor_code = $visitor->visitor_id;
            } elseif (empty($visit->visitor_code)) {
                $visitor = Visitor::find($visit->visitor_id);
                $visit->visitor_code = $visitor?->visitor_id ?: Visitor::generateUniqueVisitorId();
            }
        });
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'visitor_id');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
}
