<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Progress extends Model
{
    protected $table = 'progress';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'completed',
        'completed_at',
        'watched_seconds',
    ];

    protected $casts = [
        'completed'       => 'boolean',
        'completed_at'    => 'datetime',
        'watched_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /** Marquer la leçon comme terminée */
    public function markCompleted(): void
    {
        $this->update([
            'completed'    => true,
            'completed_at' => now(),
        ]);
    }

    /** Mettre à jour le temps visionné */
    public function updateWatchedSeconds(int $seconds): void
    {
        $this->update(['watched_seconds' => $seconds]);
    }
}
