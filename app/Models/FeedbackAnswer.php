<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackAnswer extends Model
{
    protected $fillable = ['feedback_id', 'question_id', 'answer'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }
}
