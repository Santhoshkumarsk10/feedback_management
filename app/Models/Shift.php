<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope to filter active shifts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Plant visits conducted during this shift.
     */
    public function visits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Visit::class);
    }

    /**
     * Feedback records received for visits in this shift.
     */
    public function feedbacks(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Feedback::class, Visit::class);
    }

    /**
     * Formatted short start time (e.g. 06:00).
     */
    public function getStartTimeShortAttribute(): string
    {
        return substr($this->start_time, 0, 5);
    }

    /**
     * Formatted short end time (e.g. 14:00).
     */
    public function getEndTimeShortAttribute(): string
    {
        return substr($this->end_time, 0, 5);
    }

    /**
     * Formatted 12-hour start time (e.g. 06:00 AM).
     */
    public function getStartTime12hAttribute(): string
    {
        try {
            return Carbon::createFromTimeString($this->start_time)->format('h:i A');
        } catch (\Exception $e) {
            return $this->start_time;
        }
    }

    /**
     * Formatted 12-hour end time (e.g. 02:00 PM).
     */
    public function getEndTime12hAttribute(): string
    {
        try {
            return Carbon::createFromTimeString($this->end_time)->format('h:i A');
        } catch (\Exception $e) {
            return $this->end_time;
        }
    }

    /**
     * Formatted 24h range (e.g. 06:00 – 14:00).
     */
    public function getFormatted24hRangeAttribute(): string
    {
        return $this->start_time_short . ' – ' . $this->end_time_short;
    }

    /**
     * Formatted 12h range (e.g. 06:00 AM – 02:00 PM).
     */
    public function getFormatted12hRangeAttribute(): string
    {
        return $this->start_time_12h . ' – ' . $this->end_time_12h;
    }

    /**
     * Check if the shift crosses midnight (overnight shift).
     */
    public function getIsOvernightAttribute(): bool
    {
        return $this->end_time_short < $this->start_time_short;
    }

    /**
     * Calculate human-readable duration (e.g. 8 hrs).
     */
    public function getDurationAttribute(): string
    {
        try {
            $start = Carbon::createFromTimeString($this->start_time);
            $end = Carbon::createFromTimeString($this->end_time);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            $diffMins = $start->diffInMinutes($end);
            $hours = floor($diffMins / 60);
            $mins = $diffMins % 60;

            if ($mins > 0) {
                return "{$hours}h {$mins}m";
            }

            return "{$hours} hrs";
        } catch (\Exception $e) {
            return '—';
        }
    }

    /**
     * Get the current active operational shift.
     */
    public static function current(): ?self
    {
        return self::active()->get()->first(fn ($s) => $s->is_currently_active);
    }

    /**
     * Check if the current time falls inside this shift.
     */
    public function getIsCurrentlyActiveAttribute(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $tz = config('app.plant_timezone', 'Asia/Kolkata');
        $now = now($tz)->format('H:i');
        $start = $this->start_time_short;
        $end = $this->end_time_short;

        if ($start <= $end) {
            return $now >= $start && $now < $end;
        }

        // Overnight shift (e.g. 22:00 to 06:00)
        return $now >= $start || $now < $end;
    }

    /**
     * Live shift progress metrics (percentage, elapsed, remaining).
     */
    public function getShiftProgressAttribute(): array
    {
        try {
            $tz = config('app.plant_timezone', 'Asia/Kolkata');
            $now = Carbon::now($tz);
            $start = Carbon::createFromTimeString($this->start_time, $tz);
            $end = Carbon::createFromTimeString($this->end_time, $tz);

            if ($end->lessThanOrEqualTo($start)) {
                if ($now->format('H:i') < $this->end_time_short) {
                    $start->subDay();
                } else {
                    $end->addDay();
                }
            }

            $totalMins = (int) max(1, $start->diffInMinutes($end));
            $elapsedMins = (int) max(0, min($totalMins, $start->diffInMinutes($now)));
            $remainingMins = (int) max(0, $totalMins - $elapsedMins);

            $percent = min(100, round(($elapsedMins / $totalMins) * 100));
            $remHours = floor($remainingMins / 60);
            $remM = $remainingMins % 60;

            return [
                'percent' => $percent,
                'elapsed_minutes' => $elapsedMins,
                'remaining_minutes' => $remainingMins,
                'remaining_formatted' => "{$remHours}h {$remM}m remaining",
                'current_plant_time' => $now->format('h:i:s A'),
            ];
        } catch (\Exception $e) {
            return [
                'percent' => 50,
                'elapsed_minutes' => 0,
                'remaining_minutes' => 0,
                'remaining_formatted' => '—',
                'current_plant_time' => now()->format('h:i A'),
            ];
        }
    }
}
