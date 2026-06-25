<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Page de fin de formation avec quiz */
    public function show(Course $course): View
    {
        $user = Auth::user();
        $progress = $course->progressFor($user);

        if ($progress < 100) {
            return redirect()->route('lessons.show', $course->lessons->first() ?? 1)
                ->with('info', 'Terminez toutes les leçons d\'abord !');
        }

        $quiz = Quiz::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        return view('courses.complete', compact('course', 'quiz', 'progress'));
    }

    /** Générer un quiz via l'IA */
    public function generate(Course $course): JsonResponse
    {
        $user = Auth::user();

        // Vérifier que le cours est terminé
        if ($course->progressFor($user) < 100) {
            return response()->json(['error' => 'Terminez toutes les leçons d\'abord.'], 400);
        }

        // Vérifier si un quiz existe déjà
        $existing = Quiz::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing && $existing->questions) {
            return response()->json([
                'quiz_id' => $existing->id,
                'questions' => $existing->questions,
            ]);
        }

        // Générer les questions avec l'IA
        $questions = $this->generateWithAI($course);

        $quiz = Quiz::create([
            'user_id'   => $user->id,
            'course_id' => $course->id,
            'questions' => $questions,
            'total'     => count($questions),
        ]);

        return response()->json([
            'quiz_id'   => $quiz->id,
            'questions' => $questions,
        ]);
    }

    /** Soumettre les réponses */
    public function submit(Request $request, Quiz $quiz): JsonResponse
    {
        $answers = $request->input('answers', []);
        $questions = $quiz->questions;
        $score = 0;
        $total = count($questions);

        foreach ($questions as $i => $q) {
            if (isset($answers[$i]) && strtolower(trim($answers[$i])) === strtolower(trim($q['answer']))) {
                $score++;
            }
        }

        $quiz->update([
            'score'     => $score,
            'total'     => $total,
            'completed' => true,
        ]);

        return response()->json([
            'score'   => $score,
            'total'   => $total,
            'percent' => round(($score / max($total, 1)) * 100),
        ]);
    }

    /** Générer des questions via DeepSeek */
    private function generateWithAI(Course $course): array
    {
        $apiKey = config('services.openai.api_key');
        if (empty($apiKey)) {
            return $this->fallbackQuestions($course);
        }

        $lessons = $course->lessons()->select('title', 'content')->get();
        $context = "Formation : {$course->title}\n";
        foreach ($lessons as $l) {
            $context .= "- {$l->title} : {$l->content}\n";
        }

        $prompt = "Génère 5 questions à choix multiples (QCM) en français basées sur cette formation. Format JSON strict:\n\n";
        $prompt .= "[\n  {\"question\": \"...\", \"options\": [\"A\", \"B\", \"C\", \"D\"], \"answer\": \"A\"},\n  ...\n]\n\n";
        $prompt .= "Contexte :\n{$context}\n\nRéponds UNIQUEMENT avec le tableau JSON, pas d'autre texte.";

        try {
            $ch = curl_init('https://api.deepseek.com/v1/chat/completions');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey,
                ],
                CURLOPT_POSTFIELDS => json_encode([
                    'model' => 'deepseek-chat',
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                    'max_tokens' => 1000,
                    'temperature' => 0.7,
                ]),
            ]);
            $body = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($body, true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            // Extraire le JSON
            $content = trim(str_replace(['```json', '```'], '', $content));
            $questions = json_decode($content, true);
            if (is_array($questions) && count($questions) > 0) return $questions;
        } catch (\Exception $e) {}

        return $this->fallbackQuestions($course);
    }

    private function fallbackQuestions(Course $course): array
    {
        return [
            ['question' => "Quel est le sujet principal de la formation « {$course->title} » ?", 'options' => ['Le design graphique', 'Le marketing digital', 'La comptabilité', 'La programmation'], 'answer' => 'Le marketing digital'],
            ['question' => "Combien de niveaux comporte cette formation ?", 'options' => ['1', '2', '3', '4'], 'answer' => (string) $course->levels()->count()],
            ['question' => "Quel est l'objectif principal de cette formation ?", 'options' => ['Se divertir', 'Apprendre des compétences pro', 'Jouer', 'Voyager'], 'answer' => 'Apprendre des compétences pro'],
            ['question' => "Comment validez-vous une leçon ?", 'options' => ['En la lisant', 'En cliquant sur Marquer terminée', 'En payant', 'En partageant'], 'answer' => 'En cliquant sur Marquer terminée'],
            ['question' => "Quel moyen de paiement est accepté ?", 'options' => ['Carte bancaire', 'Wave & Orange Money', 'PayPal', 'Bitcoin'], 'answer' => 'Wave & Orange Money'],
        ];
    }
}
