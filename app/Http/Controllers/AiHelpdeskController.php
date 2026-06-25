<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\AiHelpdeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AiHelpdeskController extends Controller
{
    public function __construct(
        protected AiHelpdeskService $aiService
    ) {
        $this->middleware('auth');
    }

    // =================================================================
    // CHAT PRINCIPAL
    // =================================================================

    /**
     * Envoyer un message à l'assistant IA.
     *
     * Route : POST /api/helpdesk/chat
     *
     * Body JSON attendu :
     * {
     *     "message":        "Je ne comprends pas le concept de...",
     *     "lesson_id":      12,                   // optionnel
     *     "conversation_id": 5                    // optionnel (pour continuer une conversation)
     * }
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message'         => ['required', 'string', 'max:2000'],
            'lesson_id'       => ['nullable', 'exists:lessons,id'],
            'conversation_id' => ['nullable', 'exists:ai_conversations,id'],
        ]);

        $user = Auth::user();

        // --- Vérifier l'accès à la leçon si spécifiée --------------------
        $lesson = null;
        if (! empty($validated['lesson_id'])) {
            $lesson = Lesson::with('level.course')->find($validated['lesson_id']);

            // Vérifier que l'étudiant a un accès approuvé à cette formation
            if ($lesson && ! $lesson->level->course->isApprovedFor($user)) {
                return response()->json([
                    'message' => 'Vous devez avoir un accès validé à cette formation '
                               . 'pour utiliser l\'assistant IA.',
                ], 403);
            }
        }

        // --- Récupérer la conversation existante si spécifiée ------------
        $conversation = null;
        if (! empty($validated['conversation_id'])) {
            $conversation = AiConversation::where('user_id', $user->id)
                ->find($validated['conversation_id']);

            if (! $conversation) {
                return response()->json([
                    'message' => 'Conversation introuvable.',
                ], 404);
            }
        }

        // --- Appeler le service IA ---------------------------------------
        $result = $this->aiService->chat(
            user:         $user,
            userMessage:  $validated['message'],
            lesson:       $lesson,
            conversation: $conversation,
        );

        return response()->json([
            'reply'            => $result['reply'],
            'conversation_id'  => $result['conversation']->id,
            'conversation'     => $result['conversation'],
        ]);
    }

    // =================================================================
    // GESTION DES CONVERSATIONS
    // =================================================================

    /**
     * Lister les conversations de l'étudiant connecté.
     *
     * Route : GET /api/helpdesk/conversations
     */
    public function conversations(): JsonResponse
    {
        $conversations = AiConversation::with(['lesson:id,title', 'level:id,title'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return response()->json($conversations);
    }

    /**
     * Détail d'une conversation avec ses messages.
     *
     * Route : GET /api/helpdesk/conversations/{conversation}
     */
    public function show(AiConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        return response()->json(
            $conversation->load(['messages' => fn ($q) => $q->oldest(), 'lesson', 'level'])
        );
    }

    /**
     * Supprimer une conversation.
     *
     * Route : DELETE /api/helpdesk/conversations/{conversation}
     */
    public function destroy(AiConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $conversation->delete(); // cascade sur ai_messages

        return response()->json(['message' => 'Conversation supprimée.']);
    }
}
