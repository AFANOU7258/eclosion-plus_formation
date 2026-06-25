<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LessonVideoUploadController extends Controller
{
    /**
     * Afficher le formulaire d'upload vidéo
     */
    public function create(Lesson $lesson)
    {
        $this->authorize('updateResource', [LessonResourcePolicy::class, $lesson->level->course]);

        return view('lessons.video.upload', compact('lesson'));
    }

    /**
     * Uploader une vidéo
     */
    public function store(Request $request, Lesson $lesson)
    {
        $this->authorize('updateResource', [LessonResourcePolicy::class, $lesson->level->course]);

        $validated = $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogg,avi,mov,mkv|max:5120000', // 5GB max
            'duration_minutes' => 'nullable|integer|min:0',
        ]);

        try {
            // Supprimer l'ancien vidéo s'il existe
            if ($lesson->media_path && Storage::disk('public')->exists($lesson->media_path)) {
                Storage::disk('public')->delete($lesson->media_path);
            }

            // Upload le nouveau fichier
            $video = $request->file('video');
            $path = $video->store('courses/lessons', 'public');

            // Mettre à jour la leçon
            $lesson->update([
                'media_path' => $path,
                'media_type' => 'video',
                'duration_minutes' => $validated['duration_minutes'] ?? null,
            ]);

            // Retourner pour AJAX
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vidéo uploadée avec succès',
                    'video' => [
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'stream_url' => route('video.stream', $lesson),
                    ],
                ]);
            }

            return redirect()->route('lessons.show', $lesson)
                ->with('success', 'Vidéo uploadée avec succès');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Erreur lors de l\'upload: ' . $e->getMessage(),
                ], 400);
            }

            return back()->withErrors(['video' => 'Erreur lors de l\'upload']);
        }
    }

    /**
     * Uploader via formulaire (AJAX)
     */
    public function upload(Request $request, Lesson $lesson)
    {
        $this->authorize('updateResource', [LessonResourcePolicy::class, $lesson->level->course]);

        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogg,avi,mov,mkv|max:5120000',
        ]);

        try {
            // Supprimer l'ancien
            if ($lesson->media_path && Storage::disk('public')->exists($lesson->media_path)) {
                Storage::disk('public')->delete($lesson->media_path);
            }

            // Upload
            $video = $request->file('video');
            $path = $video->store('courses/lessons', 'public');

            $lesson->update([
                'media_path' => $path,
                'media_type' => 'video',
            ]);

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Supprimer une vidéo
     */
    public function destroy(Lesson $lesson)
    {
        $this->authorize('updateResource', [LessonResourcePolicy::class, $lesson->level->course]);

        if ($lesson->media_path && Storage::disk('public')->exists($lesson->media_path)) {
            Storage::disk('public')->delete($lesson->media_path);
        }

        $lesson->update([
            'media_path' => null,
            'media_type' => null,
            'duration_minutes' => null,
        ]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Vidéo supprimée');
    }
}
