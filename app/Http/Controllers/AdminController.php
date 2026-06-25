<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Level;
use App\Models\Lesson;
use App\Models\AiConversation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    /** Dashboard riche */
    public function dashboard(): View
    {
        $stats = [
            "courses_total" => Course::count(),
            "courses_published" => Course::where(
                "status",
                "published",
            )->count(),
            "students_total" => User::where("role", "student")->count(),
            "instructors_total" => User::where("role", "instructor")->count(),
            "enrollments_pending" => Enrollment::pending()->count(),
            "enrollments_approved" => Enrollment::approved()->count(),
            "enrollments_total" => Enrollment::count(),
            "levels_total" => Level::count(),
            "lessons_total" => Lesson::count(),
            "conversations_total" => AiConversation::count(),
            "revenue_total" => Enrollment::where(
                "enrollments.status",
                "approuvé",
            )
                ->join("courses", "enrollments.course_id", "=", "courses.id")
                ->sum("courses.price"),
        ];

        // Dernières inscriptions (5)
        $recentEnrollments = Enrollment::with(["user", "course"])
            ->latest()
            ->take(5)
            ->get();

        // Derniers utilisateurs inscrits
        $recentUsers = User::latest()->take(5)->get();

        // Top formations
        $topCourses = Course::withCount("enrollments")
            ->orderByDesc("enrollments_count")
            ->take(5)
            ->get();

        // Activité récente (conversations IA)
        $recentConversations = AiConversation::with("user")
            ->latest()
            ->take(5)
            ->get();

        return view(
            "admin.dashboard",
            compact(
                "stats",
                "recentEnrollments",
                "recentUsers",
                "topCourses",
                "recentConversations",
            ),
        );
    }

    /** Gestion des demandes enrichie */
    public function enrollments(Request $request): View
    {
        $query = Enrollment::with(["user", "course", "approvedBy"]);

        // Filtre statut
        if ($status = $request->get("status")) {
            $query->where("status", $status);
        }

        // Recherche par étudiant ou formation
        if ($search = $request->get("search")) {
            $query->where(function ($q) use ($search) {
                $q->whereHas(
                    "user",
                    fn($u) => $u->where("name", "like", "%{$search}%"),
                )->orWhereHas(
                    "course",
                    fn($c) => $c->where("title", "like", "%{$search}%"),
                );
            });
        }

        $enrollments = $query
            ->latest()
            ->paginate(25)
            ->appends($request->query());

        // Compteurs pour les onglets
        $counts = [
            "all" => Enrollment::count(),
            "pending" => Enrollment::pending()->count(),
            "approved" => Enrollment::approved()->count(),
            "rejected" => Enrollment::where("status", "refusé")->count(),
        ];

        return view(
            "admin.enrollments.index",
            compact("enrollments", "counts"),
        );
    }

    /** Détail d'une demande */
    public function enrollmentShow(Enrollment $enrollment): View
    {
        $enrollment->load(["user", "course.levels.lessons", "approvedBy"]);
        return view("admin.enrollments.show", compact("enrollment"));
    }

    /** Liste des utilisateurs */
    public function users(Request $request): View
    {
        $query = User::withCount(["enrollments", "createdCourses"]);

        if ($role = $request->get("role")) {
            $query->where("role", $role);
        }
        if ($search = $request->get("search")) {
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")->orWhere(
                    "email",
                    "like",
                    "%{$search}%",
                );
            });
        }

        $users = $query->latest()->paginate(25)->appends($request->query());

        $roles = [
            "student" => User::where("role", "student")->count(),
            "instructor" => User::where("role", "instructor")->count(),
            "admin" => User::where("role", "admin")->count(),
        ];

        return view("admin.users.index", compact("users", "roles"));
    }

    /** Détail d'un utilisateur */
    public function userShow(User $user): View
    {
        $user->load(["enrollments.course", "createdCourses", "reviews"]);
        return view("admin.users.show", compact("user"));
    }

    /** Changer le rôle */
    public function userUpdateRole(Request $request, User $user)
    {
        $request->validate(["role" => "required|in:student,instructor,admin"]);
        $user->update(["role" => $request->role]);
        return back()->with("success", "Rôle mis à jour.");
    }
}
