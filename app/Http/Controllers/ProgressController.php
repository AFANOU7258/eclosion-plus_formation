<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Progress;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Marquer/démarquer une leçon comme terminée */
    public function toggle(Lesson $lesson): JsonResponse
    {
        $user = Auth::user();

        $progress = Progress::firstOrNew([
            'user_id'   => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->completed = !$progress->completed;
        $progress->completed_at = $progress->completed ? now() : null;
        $progress->save();

        return response()->json([
            'completed' => $progress->completed,
            'message'   => $progress->completed ? 'Leçon terminée ✓' : 'Leçon reprise',
        ]);
    }
}
