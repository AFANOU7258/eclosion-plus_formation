<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $table = 'ai_messages';

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
    ];

    protected $casts = [
        'tokens_used' => 'integer',
    ];

    // =================================================================
    // CONSTANTES DE RÔLE (OpenAI format)
    // =================================================================

    public const ROLE_USER      = 'user';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SYSTEM    = 'system';

    // =================================================================
    // RELATIONS
    // =================================================================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}
