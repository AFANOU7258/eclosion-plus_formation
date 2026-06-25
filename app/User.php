<?php

namespace App;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Progress;
use App\Models\Review;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "email",
        "password",
        "role",
        "avatar",
        "bio",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    // -----------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------

    /** Cours créés par ce formateur */
    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, "instructor_id");
    }

    /** Inscriptions aux cours */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** Cours auxquels l'utilisateur est inscrit */
    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, "enrollments")
            ->withPivot("enrolled_at", "completed_at")
            ->withTimestamps();
    }

    /** Progression sur les leçons */
    public function progress(): HasMany
    {
        return $this->hasMany(Progress::class);
    }

    /** Avis laissés */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // -----------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === "admin";
    }

    public function isInstructor(): bool
    {
        return $this->role === "instructor";
    }

    public function isStudent(): bool
    {
        return $this->role === "student";
    }
}
