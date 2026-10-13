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

    protected $appends = [
        'is_feedback_expired',
    ];

    /**
     * Check if feedback collection has expired because the visit day has ended.
     * Feedback can only be submitted on the day of the visit.
     */
    public function getIsFeedbackExpiredAttribute(): bool
    {
        if (empty($this->visit_date)) {
            return false;
        }

        $tz = config('app.plant_timezone', 'Asia/Kolkata');
        $todayStr = now($tz)->format('Y-m-d');
        $visitDateStr = $this->visit_date->timezone($tz)->format('Y-m-d');

        return $visitDateStr < $todayStr;
    }

    /**
     * Check if visitor can still submit feedback (no existing feedback and visit day has not ended).
     */
    public function getCanSubmitFeedbackAttribute(): bool
    {
        return ! $this->feedback && ! $this->is_feedback_expired;
    }

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

    /**
     * Check if visitor has pending feedback and their assigned shift is closing within 30 minutes or has ended today.
     */
    public function getIsShiftClosingSoonAlertAttribute(): bool
    {
        if (!is_null($this->feedback) || $this->feedback()->exists()) {
            return false;
        }

        if (! $this->shift) {
            return false;
        }

        $tz = config('app.plant_timezone', 'Asia/Kolkata');
        $visitDate = $this->visit_date ? $this->visit_date->timezone($tz)->format('Y-m-d') : null;
        $today = now($tz)->format('Y-m-d');

        if ($visitDate !== $today) {
            return false;
        }

        return $this->shift->isClosingSoon(30) || $this->shift->hasEndedToday();
    }

    /**
     * Human-friendly alert badge text for this visitor's shift feedback deadline.
     */
    public function getShiftCloseAlertTextAttribute(): ?string
    {
        if (! $this->is_shift_closing_soon_alert) {
            return null;
        }

        $shift = $this->shift;
        if ($shift && $shift->isClosingSoon(30)) {
            $rem = $shift->shift_progress['remaining_minutes'] ?? 0;
            return $rem <= 1 ? 'Shift ends <1m' : "Shift ends in {$rem}m";
        }

        return 'Shift closed — Feedback overdue';
    }
}
