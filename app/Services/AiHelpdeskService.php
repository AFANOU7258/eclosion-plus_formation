<?php

namespace App\Services;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Lesson;
use App\User;
use Illuminate\Support\Facades\Log;

/**
 * Service responsable de la communication avec l'API OpenAI
 * pour l'assistant virtuel (Helpdesk IA).
 *
 * Usage typique depuis un contrôleur :
 *
 *   $service = app(AiHelpdeskService::class);
 *   $reply   = $service->chat(
 *       user:         Auth::user(),
 *       userMessage:  "Je ne comprends pas cette partie...",
 *       lesson:       $currentLesson,
 *       conversation: $conversation,   // nullable (crée une nouvelle conv si null)
 *   );
 */
class AiHelpdeskService
{
    /**
     * URL de l'API OpenAI Chat Completions.
     */
    protected string $apiUrl = "https://api.openai.com/v1/chat/completions";

    /**
     * Modèle utilisé pour les réponses.
     * Peut être surchargé dans le .env (AI_MODEL=gpt-4o).
     */
    protected string $model;

    /**
     * Température (créativité) des réponses.
     */
    protected float $temperature = 0.7;

    /**
     * Nombre maximum de tokens pour la réponse.
     */
    protected int $maxTokens = 1000;

    /**
     * Messages maximum de l'historique à envoyer
     * (pour ne pas exploser le contexte).
     */
    protected int $historyLimit = 20;

    public function __construct()
    {
        $this->model = config("services.openai.model", "gpt-4o-mini");
        $this->apiUrl = config("services.openai.base_url", $this->apiUrl);
        $this->temperature = config("services.openai.temperature", 0.7);
        $this->maxTokens = config("services.openai.max_tokens", 1000);
    }

    // =================================================================
    // API PRINCIPALE
    // =================================================================

    /**
     * Envoyer un message utilisateur à l'IA et obtenir une réponse.
     *
     * @param User               $user          L'étudiant qui pose la question
     * @param string             $userMessage   La question saisie
     * @param Lesson|null        $lesson        La leçon sur laquelle l'étudiant est bloqué
     * @param AiConversation|null $conversation Conversation existante (ou null pour en créer une)
     * @return array{reply: string, conversation: AiConversation}
     */
    public function chat(
        User $user,
        string $userMessage,
        ?Lesson $lesson = null,
        ?AiConversation $conversation = null,
    ): array {
        // --- 1. Créer ou récupérer la conversation ------------------------
        if (!$conversation) {
            $conversation = $this->createConversation($user, $lesson);
        }

        // --- 2. Construire le système de messages pour OpenAI -------------
        $messages = $this->buildMessages($conversation, $lesson, $userMessage);

        // --- 3. Sauvegarder le message utilisateur ------------------------
        AiMessage::create([
            "conversation_id" => $conversation->id,
            "role" => AiMessage::ROLE_USER,
            "content" => $userMessage,
        ]);

        // --- 4. Appeler l'API OpenAI --------------------------------------
        try {
            $response = $this->callOpenAi($messages);
        } catch (\Exception $e) {
            Log::error("AI Helpdesk error", [
                "user_id" => $user->id,
                "conversation_id" => $conversation->id,
                "error" => $e->getMessage(),
            ]);

            // Message de fallback pour l'étudiant
            $fallback =
                "Désolé, l'assistant IA est momentanément indisponible. " .
                "Veuillez réessayer dans quelques instants.";

            AiMessage::create([
                "conversation_id" => $conversation->id,
                "role" => AiMessage::ROLE_ASSISTANT,
                "content" => $fallback,
            ]);

            return [
                "reply" => $fallback,
                "conversation" => $conversation,
            ];
        }

        // --- 5. Sauvegarder la réponse de l'assistant ---------------------
        $reply = $response["choices"][0]["message"]["content"];
        $tokensUsed = $response["usage"]["total_tokens"] ?? null;

        AiMessage::create([
            "conversation_id" => $conversation->id,
            "role" => AiMessage::ROLE_ASSISTANT,
            "content" => $reply,
            "tokens_used" => $tokensUsed,
        ]);

        // --- 6. Auto-titrer la conversation au premier échange ------------
        if (
            empty($conversation->title) &&
            $conversation->messages()->count() <= 3
        ) {
            $conversation->update([
                "title" => mb_strimwidth($userMessage, 0, 80, "…"),
            ]);
        }

        return [
            "reply" => $reply,
            "conversation" => $conversation->fresh("messages"),
        ];
    }

    // =================================================================
    // MÉTHODES INTERNES
    // =================================================================

    /**
     * Créer une nouvelle conversation liée au contexte de la leçon.
     */
    protected function createConversation(
        User $user,
        ?Lesson $lesson,
    ): AiConversation {
        return AiConversation::create([
            "user_id" => $user->id,
            "level_id" => $lesson?->level_id,
            "lesson_id" => $lesson?->id,
        ]);
    }

    /**
     * Construire le tableau de messages au format OpenAI.
     *
     * Structure :
     *   [0] => system message  (contexte de la formation)
     *   [1..N] => historique des messages précédents
     *   [N+1] => message utilisateur actuel
     */
    protected function buildMessages(
        AiConversation $conversation,
        ?Lesson $lesson,
        string $userMessage,
    ): array {
        $messages = [];

        // --- System prompt (nourri dynamiquement) -------------------------
        $systemPrompt = $this->buildSystemPrompt($lesson);
        $messages[] = [
            "role" => "system",
            "content" => $systemPrompt,
        ];

        // --- Historique des messages précédents ---------------------------
        $history = $conversation
            ->messages()
            ->latest()
            ->take($this->historyLimit)
            ->get()
            ->reverse(); // ordre chronologique

        foreach ($history as $msg) {
            $messages[] = [
                "role" => $msg->role,
                "content" => $msg->content,
            ];
        }

        // --- Message utilisateur actuel ----------------------------------
        $messages[] = [
            "role" => "user",
            "content" => $userMessage,
        ];

        return $messages;
    }

    /**
     * Construire le prompt système qui donne le contexte à l'IA.
     *
     * L'IA reçoit dynamiquement :
     *   - Le titre de la formation
     *   - Le titre et la description du niveau
     *   - Le titre et le contenu de la leçon
     *
     * Cela permet à l'IA de répondre de manière ultra-pertinente.
     */
    protected function buildSystemPrompt(?Lesson $lesson): string
    {
        $prompt =
            "Tu es l'assistant virtuel de la plateforme de formation Eclosion+.\n\n";

        $prompt .= "TON RÔLE :\n";
        $prompt .=
            "- Aider les étudiants à comprendre le contenu des formations.\n";
        $prompt .=
            "- Expliquer les concepts difficiles avec des exemples concrets.\n";
        $prompt .=
            "- Répondre de manière pédagogique, patiente et encourageante.\n";
        $prompt .=
            "- Si une question est hors-sujet, recentrer poliment sur la formation.\n";
        $prompt .=
            "- Ne jamais donner de conseils médicaux, financiers ou juridiques.\n\n";

        // Contexte dynamique de la formation
        if ($lesson) {
            $prompt .= "CONTEXTE ACTUEL DE L'ÉTUDIANT :\n";
            $prompt .= $lesson->ai_context . "\n";

            // Adaptation selon le type de média
            $prompt .= match ($lesson->media_type) {
                "video" => "L'étudiant est en train de regarder une vidéo. " .
                    "Sois concis et visuel dans tes explications.\n",
                "audio" => "L'étudiant écoute un fichier audio. " .
                    "Privilégie des explications claires et auditives.\n",
                "pdf" => "L'étudiant lit un document PDF. " .
                    "Tu peux l'aider à décortiquer le texte ou les concepts.\n",
                default => "",
            };
        } else {
            $prompt .=
                "L'étudiant n'est pas encore positionné sur une leçon spécifique. " .
                "Aide-le de manière générale.\n";
        }

        $prompt .=
            "\nRÉPONDS EN FRANÇAIS, sauf si l'étudiant s'exprime dans une autre langue.";

        return $prompt;
    }

    /**
     * Appeler l'API DeepSeek/OpenAI Chat Completions via PHP curl natif.
     */
    protected function callOpenAi(array $messages): array
    {
        $apiKey = config("services.openai.api_key");

        if (empty($apiKey)) {
            throw new \RuntimeException(
                "Clé API non configurée. Ajoutez OPENAI_API_KEY dans votre .env.",
            );
        }

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer " . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "model" => $this->model,
                "messages" => $messages,
                "temperature" => $this->temperature,
                "max_tokens" => $this->maxTokens,
            ]),
        ]);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException("API request failed: {$error}");
        }

        $data = json_decode($body, true);

        if ($httpCode !== 200 || isset($data["error"])) {
            $errorMsg = $data["error"]["message"] ?? $body;
            throw new \RuntimeException(
                "API error (HTTP {$httpCode}): {$errorMsg}",
            );
        }

        return $data;
    }
}
