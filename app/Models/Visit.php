<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    protected $fillable = [
        'visitor_id', 'visitor_code', 'organizer_id', 'shift_id', 'visitor_name', 'visitor_designation', 'visitor_mobile', 'visitor_company', 'visitor_email',
        'visit_date', 'in_time', 'out_time', 'purpose', 'department',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'in_time' => 'datetime',
        'out_time' => 'datetime',
    ];

    /**
     * Check if visitor has exited the plant.
     */
    public function getIsCheckedOutAttribute(): bool
    {
        return !is_null($this->out_time);
    }

    /**
     * Check if visitor is currently inside the plant.
     */
    public function getIsInsidePlantAttribute(): bool
    {
        return !is_null($this->in_time) && is_null($this->out_time);
    }

    /**
     * Calculate human-readable duration since exit.
     */
    public function getTimeSinceExitAttribute(): ?string
    {
        if (empty($this->out_time)) {
            return null;
        }

        return $this->out_time->diffForHumans(['parts' => 2, 'short' => true]);
    }

    /**
     * Flag in RED if visitor exited more than 2 hours ago and hasn't given feedback.
     */
    public function getIsExceededExitThresholdAttribute(): bool
    {
        if (empty($this->out_time) || !is_null($this->feedback)) {
            return false;
        }

        return $this->out_time->diffInMinutes(now()) >= 120;
    }

    public function getFormattedInTimeAttribute(): ?string
    {
        return $this->in_time ? $this->in_time->format('h:i A') : null;
    }

    public function getFormattedOutTimeAttribute(): ?string
    {
        return $this->out_time ? $this->out_time->format('h:i A') : null;
    }

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
