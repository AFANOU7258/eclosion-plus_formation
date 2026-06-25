<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    protected $fillable = [
        "level_id",
        "title",
        "content",
        "media_type",
        "media_path",
        "duration_minutes",
        "order",
        "illustrations",
    ];

    protected $casts = [
        "duration_minutes" => "integer",
        "order" => "integer",
        "illustrations" => "array",
    ];

    // =================================================================
    // RELATIONS
    // =================================================================

    /** Niveau parent */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Accès rapide au cours parent (via le niveau).
     */
    public function course(): BelongsTo
    {
        return $this->level->course();
    }

    /** Progression des étudiants sur cette leçon */
    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /** Conversations AI liées à cette leçon précise */
    public function aiConversations(): HasMany
    {
        return $this->hasMany(AiConversation::class);
    }

    /** Commentaires des étudiants */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->whereNull("parent_id")
            ->orderBy("created_at");
    }

    /** Ressources pédagogiques (PDF, images, documents) */
    public function resources(): HasMany
    {
        return $this->hasMany(LessonResource::class)->orderBy("order");
    }

    // =================================================================
    // HELPERS
    // =================================================================

    public function isVideo(): bool
    {
        return $this->media_type === "video";
    }

    public function isAudio(): bool
    {
        return $this->media_type === "audio";
    }

    public function isPdf(): bool
    {
        return $this->media_type === "pdf";
    }

    /** URL publique du média */
    public function getMediaUrlAttribute(): ?string
    {
        return $this->media_path ? asset("storage/" . $this->media_path) : null;
    }

    /** Vérifier si un utilisateur a terminé cette leçon */
    public function isCompletedBy(User $user): bool
    {
        return $this->progress()
            ->where("user_id", $user->id)
            ->where("completed", true)
            ->exists();
    }

    /**
     * Contexte textuel pour le prompt système de l'IA.
     * Agrège : titre du cours > titre du niveau > titre et contenu de la leçon.
     */
    public function getAiContextAttribute(): string
    {
        $courseTitle = $this->level?->course?->title ?? "";
        $levelTitle = $this->level?->title ?? "";
        $lessonTitle = $this->title;

        $context = "Formation : {$courseTitle}\n";
        $context .= "Niveau : {$levelTitle}\n";
        $context .= "Leçon : {$lessonTitle}\n";
        if ($this->content) {
            $context .= "Contenu de la leçon : {$this->content}\n";
        }

        return $context;
    }
}
