<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    /**
     * Constructeur : applique les middlewares.
     */
    public function __construct()
    {
        $this->middleware("auth");
    }

    // =================================================================
    // CÔTÉ ÉTUDIANT
    // =================================================================

    /**
     * L'étudiant clique sur "Demander l'accès" pour une formation.
     *
     * Route : POST /enrollments/{course}/request
     */
    public function requestAccess(
        Request $request,
        Course $course,
    ): JsonResponse {
        $user = Auth::user();

        if (!$course->isPublished()) {
            return response()->json(
                ["message" => 'Cette formation n\'est pas encore disponible.'],
                422,
            );
        }

        $existing = Enrollment::where("user_id", $user->id)
            ->where("course_id", $course->id)
            ->whereIn("status", [
                Enrollment::STATUS_PENDING,
                Enrollment::STATUS_APPROVED,
            ])
            ->first();

        if ($existing) {
            $msg = match ($existing->status) {
                Enrollment::STATUS_PENDING
                    => "Vous avez déjà une demande en attente.",
                Enrollment::STATUS_APPROVED
                    => "Vous avez déjà accès à cette formation.",
            };
            return response()->json(["message" => $msg], 409);
        }

        // Si demande refusée, on la réactive
        $rejected = Enrollment::where("user_id", $user->id)
            ->where("course_id", $course->id)
            ->where("status", Enrollment::STATUS_REJECTED)
            ->first();

        if ($rejected) {
            $rejected->update([
                "status" => Enrollment::STATUS_PENDING,
                "payment_method" => $request->input("payment_method"),
                "payment_reference" => $request->input("payment_reference"),
                "approved_by" => null,
                "approved_at" => null,
            ]);
            return response()->json([
                "message" =>
                    "Votre demande a été renvoyée. L'admin va la réexaminer.",
                "enrollment" => $rejected->fresh(),
            ]);
        }

        $enrollment = Enrollment::create([
            "user_id" => $user->id,
            "course_id" => $course->id,
            "status" => Enrollment::STATUS_PENDING,
            "payment_method" => $request->input("payment_method", "wave"),
            "payment_reference" => $request->input("payment_reference"),
        ]);

        // --- 3. TODO: Notification à l'admin (email / in-app) -----------
        // event(new EnrollmentRequested($enrollment));

        return response()->json(
            [
                "message" =>
                    'Votre demande d\'accès a bien été envoyée. ' .
                    'Vous recevrez une notification dès qu\'elle sera validée.',
                "enrollment" => $enrollment->load("course:id,title"),
            ],
            201,
        );
    }

    /**
     * Vérifier le statut d'une demande pour l'étudiant connecté.
     *
     * Route : GET /enrollments/{course}/status
     */
    public function checkStatus(Course $course): JsonResponse
    {
        $enrollment = Enrollment::where("user_id", Auth::id())
            ->where("course_id", $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                "status" => "none",
                "message" => "Aucune demande pour cette formation.",
            ]);
        }

        return response()->json([
            "status" => $enrollment->status,
            "requested_at" => $enrollment->created_at,
            "approved_at" => $enrollment->approved_at,
        ]);
    }

    // =================================================================
    // CÔTÉ ADMINISTRATEUR
    // =================================================================

    /**
     * Liste toutes les demandes en attente (tableau de bord admin).
     *
     * Route : GET /admin/enrollments/pending
     */
    public function pending(): JsonResponse
    {
        $this->authorizeAdmin();

        $enrollments = Enrollment::with([
            "user:id,name,email",
            "course:id,title,price",
        ])
            ->pending()
            ->latest()
            ->paginate(20);

        return response()->json($enrollments);
    }

    /**
     * L'admin approuve une demande.
     *
     * Route : PATCH /admin/enrollments/{enrollment}/approve
     */
    public function approve(Enrollment $enrollment): JsonResponse
    {
        $this->authorizeAdmin();

        if (!$enrollment->isPending()) {
            return response()->json(
                [
                    "message" => "Cette demande n'est plus en attente (statut actuel : {$enrollment->status}).",
                ],
                422,
            );
        }

        $enrollment->approve(Auth::user());

        // TODO: Notifier l'étudiant que l'accès est débloqué
        // event(new EnrollmentApproved($enrollment));

        return response()->json([
            "message" =>
                'Demande approuvée. L\'étudiant a maintenant accès à la formation.',
            "enrollment" => $enrollment->load(
                "user:id,name,email",
                "course:id,title",
            ),
        ]);
    }

    /**
     * L'admin refuse une demande.
     *
     * Route : PATCH /admin/enrollments/{enrollment}/reject
     */
    public function reject(Enrollment $enrollment): JsonResponse
    {
        $this->authorizeAdmin();

        if (!$enrollment->isPending()) {
            return response()->json(
                [
                    "message" => "Cette demande n'est plus en attente (statut actuel : {$enrollment->status}).",
                ],
                422,
            );
        }

        $enrollment->reject(Auth::user());

        // TODO: Notifier l'étudiant du refus
        // event(new EnrollmentRejected($enrollment));

        return response()->json([
            "message" => "Demande refusée.",
            "enrollment" => $enrollment->load(
                "user:id,name,email",
                "course:id,title",
            ),
        ]);
    }

    /**
     * Historique complet des demandes pour l'admin (filtrable par statut).
     *
     * Route : GET /admin/enrollments
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            "status" => [
                "nullable",
                Rule::in(["en_attente", "approuvé", "refusé"]),
            ],
            "course_id" => "nullable|exists:courses,id",
        ]);

        $query = Enrollment::with([
            "user:id,name,email",
            "course:id,title",
        ])->latest();

        if (!empty($validated["status"])) {
            $query->where("status", $validated["status"]);
        }
        if (!empty($validated["course_id"])) {
            $query->where("course_id", $validated["course_id"]);
        }

        return response()->json($query->paginate(30));
    }

    // =================================================================
    // HELPERS
    // =================================================================

    private function authorizeAdmin(): void
    {
        abort_unless(
            Auth::user()?->isAdmin(),
            403,
            "Accès réservé aux administrateurs.",
        );
    }
}
