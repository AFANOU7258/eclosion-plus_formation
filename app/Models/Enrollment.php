<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Enrollment extends Model
{
    protected $fillable = [
        "user_id",
        "course_id",
        "status",
        "payment_method",
        "payment_reference",
        "approved_by",
        "approved_at",
    ];

    protected $casts = [
        "approved_at" => "datetime",
    ];

    // =================================================================
    // CONSTANTES DE STATUT
    // =================================================================

    public const STATUS_PENDING = "en_attente";
    public const STATUS_APPROVED = "approuvé";
    public const STATUS_REJECTED = "refusé";

    // =================================================================
    // RELATIONS
    // =================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** L'admin qui a validé/refusé la demande */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, "approved_by");
    }

    // =================================================================
    // SCOPES
    // =================================================================

    public function scopePending(Builder $query): void
    {
        $query->where("status", self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where("status", self::STATUS_APPROVED);
    }

    public function scopeRejected(Builder $query): void
    {
        $query->where("status", self::STATUS_REJECTED);
    }

    // =================================================================
    // HELPERS
    // =================================================================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Approuver la demande.
     */
    public function approve(User $admin): void
    {
        $this->update([
            "status" => self::STATUS_APPROVED,
            "approved_by" => $admin->id,
            "approved_at" => now(),
        ]);
    }

    /**
     * Refuser la demande.
     */
    public function reject(User $admin): void
    {
        $this->update([
            "status" => self::STATUS_REJECTED,
            "approved_by" => $admin->id,
            "approved_at" => now(),
        ]);
    }
}
