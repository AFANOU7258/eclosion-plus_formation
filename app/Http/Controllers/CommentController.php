<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Lesson;
use App\Services\AiHelpdeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(
        protected AiHelpdeskService $ai
    ) {
        $this->middleware('auth');
    }

    /** Lister les commentaires d'une leçon */
    public function index(Lesson $lesson): JsonResponse
    {
        $comments = $lesson->comments()
            ->with(['user:id,name', 'replies.user:id,name'])
            ->get();

        return response()->json($comments);
    }

    /** Poster un commentaire → l'IA répond automatiquement */
    public function store(Request $request, Lesson $lesson): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $user = Auth::user();

        // Créer le commentaire de l'étudiant
        $comment = Comment::create([
            'user_id'   => $user->id,
            'lesson_id' => $lesson->id,
            'content'   => $validated['content'],
            'is_ai'     => false,
        ]);

        $comment->load('user:id,name');

        // L'IA répond automatiquement
        $aiReply = null;
        try {
            $result = $this->ai->chat(
                user: $user,
                userMessage: "Question d'un étudiant sur cette leçon : " . $validated['content'],
                lesson: $lesson,
            );

            // Créer la réponse de l'IA
            $aiComment = Comment::create([
                'user_id'   => $user->id, // lié au même user mais marqué IA
                'lesson_id' => $lesson->id,
                'parent_id' => $comment->id,
                'content'   => $result['reply'],
                'is_ai'     => true,
            ]);

            $aiReply = $aiComment->load('user:id,name');
        } catch (\Exception $e) {
            $aiReply = null; // L'IA est down, on ignore
        }

        return response()->json([
            'comment' => $comment,
            'ai_reply' => $aiReply,
        ], 201);
    }
}
