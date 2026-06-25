<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HelpdeskController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\AiHelpdeskController;
use App\Http\Controllers\StudentEnrollmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Eclosion+ LMS
|--------------------------------------------------------------------------
*/

// Accueil
Route::get("/", function () {
    return view("welcome");
});

// Accès invité via lien partagé
Route::get("acces/{token}", [
    App\Http\Controllers\ShareLinkController::class,
    "access",
]);

// Catalogue
Route::get("formations", [CourseController::class, "index"])->name(
    "courses.index",
);
Route::get("formations/{course}", [CourseController::class, "show"])->name(
    "courses.show",
);

// Leçon
Route::get("lecons/{lesson}", [LessonController::class, "show"])->name(
    "lessons.show",
);

// Authentification
Route::get("login", function () {
    return view("auth.login");
})->name("login");
Route::post("login", [
    App\Http\Controllers\Auth\AuthController::class,
    "login",
])->name("login.post");
Route::get("register", function () {
    return view("auth.register");
})->name("register");
Route::post("register", [
    App\Http\Controllers\Auth\AuthController::class,
    "register",
])->name("register.post");
Route::post("logout", [
    App\Http\Controllers\Auth\AuthController::class,
    "logout",
])->name("logout");

// Routes protégées
Route::middleware("auth")->group(function () {
    Route::get("mes-cours", [
        StudentEnrollmentController::class,
        "index",
    ])->name("enrollments.index");
    Route::get("support", [HelpdeskController::class, "index"])->name(
        "helpdesk.index",
    );
    Route::get("support/{conversation}", [
        HelpdeskController::class,
        "show",
    ])->name("helpdesk.show");

    // Demandes d'accès
    Route::post("enrollments/{course}/request", [
        EnrollmentController::class,
        "requestAccess",
    ])->name("api.enrollments.request");
    Route::get("enrollments/{course}/status", [
        EnrollmentController::class,
        "checkStatus",
    ]);

    // Chat IA
    Route::post("helpdesk/chat", [AiHelpdeskController::class, "chat"]);
    Route::get("helpdesk/conversations", [
        AiHelpdeskController::class,
        "conversations",
    ]);
    Route::delete("helpdesk/conversations/{conversation}", [
        AiHelpdeskController::class,
        "destroy",
    ]);

    // Commentaires avec réponse IA
    Route::get("lecons/{lesson}/comments", [
        App\Http\Controllers\CommentController::class,
        "index",
    ]);
    Route::post("lecons/{lesson}/comments", [
        App\Http\Controllers\CommentController::class,
        "store",
    ]);

    // Avis / Étoiles
    Route::post("formations/{course}/review", [
        App\Http\Controllers\ReviewController::class,
        "store",
    ]);

    // Quiz de fin de formation
    Route::get("formations/{course}/complete", [
        App\Http\Controllers\QuizController::class,
        "show",
    ])->name("courses.complete");
    Route::get("quiz/{course}/generate", [
        App\Http\Controllers\QuizController::class,
        "generate",
    ]);
    Route::post("quiz/{quiz}/submit", [
        App\Http\Controllers\QuizController::class,
        "submit",
    ]);

    // Progression
    Route::post("progress/{lesson}/toggle", [
        App\Http\Controllers\ProgressController::class,
        "toggle",
    ]);

    // Admin : approuver/refuser
    Route::patch("admin/enrollments/{enrollment}/approve", [
        EnrollmentController::class,
        "approve",
    ]);
    Route::patch("admin/enrollments/{enrollment}/reject", [
        EnrollmentController::class,
        "reject",
    ]);
});

// Admin
Route::middleware("auth")
    ->prefix("admin")
    ->group(function () {
        Route::get("/", [AdminController::class, "dashboard"])->name(
            "admin.dashboard",
        );
        Route::get("enrollments", [
            AdminController::class,
            "enrollments",
        ])->name("admin.enrollments.index");
        Route::get("enrollments/{enrollment}", [
            AdminController::class,
            "enrollmentShow",
        ])->name("admin.enrollments.show");
        Route::get("users", [AdminController::class, "users"])->name(
            "admin.users.index",
        );
        Route::get("users/{user}", [AdminController::class, "userShow"])->name(
            "admin.users.show",
        );
        Route::patch("users/{user}/role", [
            AdminController::class,
            "userUpdateRole",
        ])->name("admin.users.updateRole");

        // CRUD Formations
        Route::get("courses", [AdminCourseController::class, "index"])->name(
            "admin.courses.index",
        );
        Route::get("courses/create", [
            AdminCourseController::class,
            "create",
        ])->name("admin.courses.create");
        Route::post("courses", [AdminCourseController::class, "store"])->name(
            "admin.courses.store",
        );
        Route::get("courses/{course}/edit", [
            AdminCourseController::class,
            "edit",
        ])->name("admin.courses.edit");
        Route::put("courses/{course}", [
            AdminCourseController::class,
            "update",
        ])->name("admin.courses.update");
        Route::delete("courses/{course}", [
            AdminCourseController::class,
            "destroy",
        ])->name("admin.courses.destroy");

        Route::post("courses/{course}/share", [
            App\Http\Controllers\ShareLinkController::class,
            "store",
        ])->name("admin.courses.share");
        Route::delete("share-links/{link}", [
            App\Http\Controllers\ShareLinkController::class,
            "destroy",
        ])->name("admin.share.destroy");
    });
