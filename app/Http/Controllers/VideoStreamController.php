<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class VideoStreamController extends Controller
{
    /**
     * Stream une vidéo protégée par l'authentification et l'inscription
     */
    public function stream(Lesson $lesson)
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Vérifier l'inscription au cours
        $user = Auth::user();
        $enrollment = $user->enrollments()
            ->where('course_id', $lesson->level->course->id)
            ->where('status', 'approuvé')
            ->first();

        if (!$enrollment && !$user->isAdmin() && !($user->isInstructor() && $lesson->level->course->instructor_id === $user->id)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Vérifier que la vidéo existe
        if (!$lesson->media_path || !Storage::disk('public')->exists($lesson->media_path)) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        $path = Storage::disk('public')->path($lesson->media_path);

        // Gérer les range requests pour le seeking
        return $this->streamWithRangeSupport($path, $lesson);
    }

    /**
     * Supporte les range requests (seeking dans la vidéo)
     */
    private function streamWithRangeSupport($path, Lesson $lesson)
    {
        $fileSize = filesize($path);
        $mimeType = $this->getMimeType($lesson->media_path);

        // Headers par défaut
        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=3600',
            'Content-Length' => $fileSize,
        ];

        // Gérer les range requests
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
                $start = intval($matches[1]);
                $end = $matches[2] !== '' ? intval($matches[2]) : $fileSize - 1;

                if ($start >= 0 && $end < $fileSize && $start <= $end) {
                    $length = $end - $start + 1;
                    
                    header('HTTP/1.1 206 Partial Content');
                    header('Content-Length: ' . $length);
                    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
                    
                    $fp = fopen($path, 'rb');
                    fseek($fp, $start);
                    fpassthru($fp);
                    fclose($fp);
                    exit;
                }
            }
        }

        // Streaming normal
        return response()->file($path, $headers);
    }

    /**
     * Obtenir le type MIME du fichier
     */
    private function getMimeType($filePath)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            'avi' => 'video/avi',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'pdf' => 'application/pdf',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Obtenir les métadonnées vidéo (pour player UI)
     */
    public function metadata(Lesson $lesson)
    {
        // Même vérification que stream()
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $enrollment = $user->enrollments()
            ->where('course_id', $lesson->level->course->id)
            ->where('status', 'approuvé')
            ->first();

        if (!$enrollment && !$user->isAdmin() && !($user->isInstructor() && $lesson->level->course->instructor_id === $user->id)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if (!$lesson->media_path) {
            return response()->json(['error' => 'No video'], 404);
        }

        $path = Storage::disk('public')->path($lesson->media_path);
        $fileSize = filesize($path);
        $duration = $lesson->duration_minutes ?? 0;

        return response()->json([
            'title' => $lesson->title,
            'duration' => $duration,
            'size' => $fileSize,
            'type' => $lesson->media_type,
            'url' => route('video.stream', $lesson),
        ]);
    }
}
