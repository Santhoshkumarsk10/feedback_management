<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    protected $fillable = [
        'visitor_id',
        'visitor_code',
        'plant_id',
        'organizer_id',
        'visit_date',
        'in_time',
        'out_time',
        'purpose',
        'department',
        // Virtual/transient fields delegated directly to the master Visitor record
        'visitor_name',
        'visitor_designation',
        'visitor_mobile',
        'visitor_company',
        'visitor_email',
    ];

    /**
     * Always eager-load the master Visitor relation to prevent N+1 queries.
     */
    protected $with = [
        'visitor',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'in_time' => 'datetime',
        'out_time' => 'datetime',
    ];

    protected $appends = [
        'is_feedback_expired',
        'visitor_name',
        'visitor_company',
        'visitor_designation',
        'visitor_mobile',
        'visitor_email',
    ];

    /**
     * Staging store for incoming visitor attributes before persistence.
     */
    protected array $pendingVisitorData = [];

    // --- Accessors for Visitor Details from Master Table ---

    public function getVisitorNameAttribute(): ?string
    {
        return $this->visitor?->name ?? ($this->pendingVisitorData['name'] ?? null);
    }

    public function getVisitorCompanyAttribute(): ?string
    {
        return $this->visitor?->company ?? ($this->pendingVisitorData['company'] ?? null);
    }

    public function getVisitorDesignationAttribute(): ?string
    {
        return $this->visitor?->designation ?? ($this->pendingVisitorData['designation'] ?? null);
    }

    public function getVisitorMobileAttribute(): ?string
    {
        return $this->visitor?->mobile ?? ($this->pendingVisitorData['mobile'] ?? null);
    }

    public function getVisitorEmailAttribute(): ?string
    {
        return $this->visitor?->email ?? ($this->pendingVisitorData['email'] ?? null);
    }

    // --- Mutators for Visitor Details ---

    public function setVisitorNameAttribute($value): void
    {
        $this->pendingVisitorData['name'] = $value;
    }

    public function setVisitorCompanyAttribute($value): void
    {
        $this->pendingVisitorData['company'] = $value;
    }

    public function setVisitorDesignationAttribute($value): void
    {
        $this->pendingVisitorData['designation'] = $value;
    }

    public function setVisitorMobileAttribute($value): void
    {
        $this->pendingVisitorData['mobile'] = $value;
    }

    public function setVisitorEmailAttribute($value): void
    {
        $this->pendingVisitorData['email'] = $value;
    }

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
        static::saving(function (Visit $visit) {
            // Strip any raw visitor columns from attributes so SQL INSERT/UPDATE doesn't fail
            unset(
                $visit->attributes['visitor_name'],
                $visit->attributes['visitor_company'],
                $visit->attributes['visitor_designation'],
                $visit->attributes['visitor_mobile'],
                $visit->attributes['visitor_email']
            );

            // Auto link or create Visitor record if not assigned
            if (empty($visit->visitor_id)) {
                $visitor = Visitor::upsertFromExternal([
                    'visitor_id' => $visit->visitor_code,
                    'name' => $visit->pendingVisitorData['name'] ?? 'Guest Visitor',
                    'company' => $visit->pendingVisitorData['company'] ?? null,
                    'designation' => $visit->pendingVisitorData['designation'] ?? null,
                    'mobile' => $visit->pendingVisitorData['mobile'] ?? null,
                    'email' => $visit->pendingVisitorData['email'] ?? null,
                ], 'visit_entry');

                $visit->visitor_id = $visitor->id;
                $visit->visitor_code = $visitor->visitor_id;
            } else {
                $visitor = $visit->visitor ?: Visitor::find($visit->visitor_id);
                if ($visitor && !empty(array_filter($visit->pendingVisitorData))) {
                    $visitor->update(array_filter($visit->pendingVisitorData));
                }
                if (empty($visit->visitor_code) && $visitor) {
                    $visit->visitor_code = $visitor->visitor_id;
                }
            }
        });
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'visitor_id');
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
}
