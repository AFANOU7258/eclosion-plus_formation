<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LessonResource extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lesson_id',
        'title',
        'type',
        'file_path',
        'url',
        'description',
        'order',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the lesson that owns this resource
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get the download URL for the resource
     */
    public function getDownloadUrl()
    {
        if ($this->type === 'link') {
            return $this->url;
        }
        
        return route('resources.download', $this->id);
    }

    /**
     * Get display icon based on type
     */
    public function getIcon()
    {
        $icons = [
            'pdf' => '📄',
            'image' => '🖼️',
            'document' => '📋',
            'link' => '🔗',
            'video' => '🎥',
        ];
        
        return $icons[$this->type] ?? '📎';
    }
}
