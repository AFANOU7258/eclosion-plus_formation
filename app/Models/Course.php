<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Review;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "instructor_id",
        "category_id",
        "title",
        "slug",
        "thumbnail",
        "description",
        "price",
        "status",
    ];

    protected $casts = [
        "price" => "decimal:2",
        "deleted_at" => "datetime",
    ];

    // =================================================================
    // SCOPES
    // =================================================================

    public function scopePublished($query)
    {
        return $query->where("status", "published");
    }

    public function scopeDraft($query)
    {
        return $query->where("status", "draft");
    }

    // =================================================================
    // RELATIONS
    // =================================================================

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, "instructor_id");
    }

    /** Niveaux triés par ordre */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy("order");
    }

    /**
     * Leçons via les niveaux (relation HasManyThrough).
     * Permet de faire $course->lessons sans boucler sur les niveaux.
     */
    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Level::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    /** Note moyenne */
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg("rating") ?? 0, 1);
    }

    /** Nombre d'avis */
    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    /** Scope recherche */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where("title", "like", "%{$term}%")->orWhere(
                "description",
                "like",
                "%{$term}%",
            );
        });
    }

    /** Élèves approuvés uniquement */
    public function approvedStudents()
    {
        return $this->belongsToMany(User::class, "enrollments")
            ->wherePivot("status", "approuvé")
            ->withPivot("status", "approved_at")
            ->withTimestamps();
    }

    // =================================================================
    // ACCESSEURS
    // =================================================================

    protected function studentsCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->enrollments()
                ->where("status", "approuvé")
                ->count(),
        );
    }

    protected function levelsCount(): Attribute
    {
        return Attribute::make(get: fn() => $this->levels()->count());
    }

    protected function totalLessonsCount(): Attribute
    {
        return Attribute::make(get: fn() => $this->lessons()->count());
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->thumbnail
                ? asset("storage/" . $this->thumbnail)
                : null,
        );
    }

    // =================================================================
    // HELPERS
    // =================================================================

    public function isPublished(): bool
    {
        return $this->status === "published";
    }

    /**
     * Vérifie si un utilisateur a un accès approuvé à cette formation.
     */
    public function isApprovedFor(User $user): bool
    {
        return $this->enrollments()
            ->where("user_id", $user->id)
            ->where("status", "approuvé")
            ->exists();
    }

    /**
     * Progression globale de l'étudiant dans cette formation (en %).
     */
    public function progressFor(User $user): float
    {
        $total = $this->lessons()->count();
        if ($total === 0) {
            return 0.0;
        }

        $completed = Progress::where("user_id", $user->id)
            ->whereIn("lesson_id", $this->lessons()->select("lessons.id"))
            ->where("completed", true)
            ->count();

        return round(($completed / $total) * 100, 1);
    }

    public function getRouteKeyName(): string
    {
        return "slug";
    }
}
