<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ShareLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ShareLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth")->except(["access"]);
    }

    /** Générer un lien d'accès */
    public function store(Request $request, Course $course): JsonResponse
    {
        $link = ShareLink::create([
            "course_id" => $course->id,
            "token" => Str::random(32),
            "created_by" => auth()->id(),
            "expires_at" => $request->expires_at
                ? now()->addDays((int) $request->expires_at)
                : null,
        ]);

        return response()->json([
            "url" => url("/acces/" . $link->token),
            "token" => $link->token,
            "message" => "Lien créé avec succès !",
        ]);
    }

    /** Supprimer un lien */
    public function destroy(ShareLink $link): JsonResponse
    {
        $link->delete();
        return response()->json(["message" => "Lien supprimé."]);
    }

    /** Page d'accès public via token */
    public function access(string $token): View
    {
        $link = ShareLink::where("token", $token)->firstOrFail();

        if (!$link->isValid()) {
            abort(410, "Ce lien a expiré.");
        }

        $course = $link
            ->course()
            ->with(["levels.lessons"])
            ->first();

        return view("courses.guest-access", compact("course", "link"));
    }
}
