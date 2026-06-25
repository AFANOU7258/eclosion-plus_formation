<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\LessonResource;
use App\User;

class LessonResourcePolicy
{
    /**
     * Determine if the user can create a resource for this lesson
     */
    public function createResource(?User $user, Lesson $lesson): bool
    {
        if (!$user) {
            return false;
        }

        // Admin peut toujours ajouter des ressources
        if ($user->isAdmin()) {
            return true;
        }

        // Instructeur peut ajouter des ressources pour ses propres cours
        if ($user->isInstructor()) {
            return $lesson->level->course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can update a resource
     */
    public function updateResource(?User $user, Lesson $lesson, LessonResource $resource): bool
    {
        if (!$user) {
            return false;
        }

        // Admin peut toujours éditer
        if ($user->isAdmin()) {
            return true;
        }

        // Instructeur peut éditer les ressources de ses propres cours
        if ($user->isInstructor()) {
            return $lesson->level->course->instructor_id === $user->id;
        }

        return false;
    }

    /**
     * Determine if the user can delete a resource
     */
    public function deleteResource(?User $user, Lesson $lesson, LessonResource $resource): bool
    {
        return $this->updateResource($user, $lesson, $resource);
    }
}
