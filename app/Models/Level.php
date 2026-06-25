<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Level extends Model
{
    protected $fillable = [
        "course_id",
        "title",
        "description",
        "level_image",
        "level_audio",
        "order",
    ];

    protected $casts = [
        "order" => "integer",
    ];

    // =================================================================
    // RELATIONS
    // =================================================================

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** Leçons triées par ordre */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy("order");
    }

    /** Conversations AI liées à ce niveau */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }

    // =================================================================
    // HELPERS
    // =================================================================

    public function lessonsCount(): int
    {
        return $this->lessons()->count();
    }

    /**
     * Récupère la formation parente avec eager-loading intelligent.
     */
    public function getCourseTitleAttribute(): string
    {
        return $this->course?->title ?? "";
    }
}
