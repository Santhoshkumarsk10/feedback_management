<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = ['visit_id', 'organizer_id', 'overall_rating', 'comments', 'submitted_at'];

    protected $casts = ['submitted_at' => 'datetime'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FeedbackAnswer::class);
    }
}
