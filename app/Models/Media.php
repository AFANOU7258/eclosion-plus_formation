<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'user_id',
        'name',
        'path',
        'mime_type',
        'size',
        'collection',
        'meta',
    ];

    protected $casts = [
        'size' => 'integer',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** URL S3 complète */
    public function getUrlAttribute(): string
    {
        return app('filesystem')->disk('s3')->url($this->path);
    }

    /** Le fichier est-il une image ? */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /** Le fichier est-il une vidéo ? */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /** Le fichier est-il un PDF ? */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
